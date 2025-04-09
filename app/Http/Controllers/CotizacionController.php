<?php

namespace App\Http\Controllers;

use App\Models\Cotizacion;
use App\Models\MovimientoCaja;
use App\Models\Arqueo;
use App\Models\CuentaPorCobrar;
use App\Models\AbonoFactura;
use App\Models\Empresa;
use App\Models\Venta;
use App\Models\DetalleVenta;
use App\Models\TmpCotizacion;
use App\Models\DetalleCotizacion;
use App\Models\Cliente;
use App\Models\Producto;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Barryvdh\DomPDF\Facade\Pdf;


class CotizacionController extends Controller
{
    public function __construct()
    {
        // Si el usuario está autenticado, obtener la empresa y compartirla en la vista
        $this->middleware(function ($request, $next) {
            if (Auth::check()) {
                // Obtener la empresa según el id de la empresa del usuario autenticado
                $empresa = Empresa::find(Auth::user()->empresa_id);
                // Compartir la variable 'empresa' con todas las vistas
                view()->share('empresa', $empresa);
            }
            return $next($request);
        });
    }
    public function index()
{
    $cotizaciones = Cotizacion::with(['cliente', 'usuario', 'detalles.producto'])->latest()->get();
    return view('admin.cotizaciones.index', compact('cotizaciones'));
}


    public function create()
    {
        $clientes = Cliente::all();
        $productos = Producto::all();
        $carrito = TmpCotizacion::where('user_id', Auth::id())->with('producto')->get();
        return view('admin.cotizaciones.create', compact('clientes', 'productos', 'carrito'));
    }

    public function store(Request $request)
    {
        DB::beginTransaction();

        try {
            $carrito = TmpCotizacion::where('user_id', Auth::id())->get();
            if ($carrito->isEmpty()) {
                return redirect()->back()->with('error', 'El carrito está vacío.');
            }

            $cotizacion = Cotizacion::create([
                'cliente_id' => $request->cliente_id,
                'user_id' => Auth::id(),
                'total' => $carrito->sum('subtotal'),
                'estado' => 'pendiente',
                'fecha' => now()->format('Y-m-d'),
            ]);

            foreach ($carrito as $item) {
                DetalleCotizacion::create([
                    'cotizacion_id' => $cotizacion->id,
                    'producto_id' => $item->producto_id,
                    'cantidad' => $item->cantidad,
                    'precio_unitario' => $item->precio_unitario,
                    'subtotal' => $item->subtotal,
                ]);
            }

            TmpCotizacion::where('user_id', Auth::id())->delete();

            DB::commit();
            return redirect()->route('admin.cotizaciones.index')->with('success', 'Cotización registrada correctamente.');
        } catch (\Throwable $e) {
            DB::rollback();
            return redirect()->back()->with('error', 'Ocurrió un error: ' . $e->getMessage());
        }
    }

    public function show(Cotizacion $cotizacion)
    {
        $cotizacion->load('detalles.producto', 'cliente', 'usuario');
        return view('admin.cotizaciones.show', compact('cotizacion'));
    }
    public function destroy(Cotizacion $cotizacion)
{
    try {
        $cotizacion->delete();
        return redirect()->route('admin.cotizaciones.index')->with('success', 'Cotización eliminada correctamente.');
    } catch (\Throwable $e) {
        return redirect()->back()->with('error', 'Ocurrió un error al eliminar: ' . $e->getMessage());
    }
}
public function generarReportePDF($id)
{
    $cotizacion = Cotizacion::with(['cliente', 'detalles.producto'])->findOrFail($id);
    $mostrarCliente = request('mostrar_cliente') !== 'no';

    $pdf = Pdf::loadView('admin.cotizaciones.reporte_pdf', compact('cotizacion', 'mostrarCliente'));

    return $pdf->stream("cotizacion_{$cotizacion->id}.pdf");
}


public function convertirEnVenta($id)
{
    DB::beginTransaction();

    try {
        $cotizacion = Cotizacion::with(['detalles.producto', 'cliente'])->findOrFail($id);

        if ($cotizacion->estado === 'aprobada') {
            return redirect()->back()->with('info', 'Esta cotización ya fue convertida en venta.');
        }

        // Crear la venta
        $venta = Venta::create([
            'fecha' => now()->format('Y-m-d'),
            'precio_total' => $cotizacion->total,
            'empresa_id' => Auth::user()->empresa_id,
            'cliente_id' => $cotizacion->cliente_id,
            'forma_pago' => 'contado', // o "credito" si lo manejás desde cotización
        ]);

        // Movimiento en caja (solo si es contado o hay abono)
        $arqueo = Arqueo::whereNull('fecha_cierre')
            ->where('empresa_id', Auth::user()->empresa_id)
            ->first();

        $monto_ingreso = $venta->forma_pago === 'contado' ? $venta->precio_total : 0;

        if ($monto_ingreso > 0 && $arqueo) {
            MovimientoCaja::create([
                'tipo' => 'INGRESO',
                'monto' => $monto_ingreso,
                'descripcion' => 'Venta desde cotización',
                'arqueo_id' => $arqueo->id,
            ]);
        }

        // Detalle de venta y descuento de stock
        foreach ($cotizacion->detalles as $item) {
            DetalleVenta::create([
                'cantidad' => $item->cantidad,
                'venta_id' => $venta->id,
                'producto_id' => $item->producto_id,
            ]);

            $producto = $item->producto;
            $producto->stock -= $item->cantidad;
            $producto->save();
        }

        // Cuenta por cobrar (solo si es a crédito)
        if ($venta->forma_pago === 'credito') {
            $abono = floatval(request()->monto_abonado ?? 0);
            $saldo = $venta->precio_total - $abono;

            $cuenta = CuentaPorCobrar::create([
                'cliente_id' => $venta->cliente_id,
                'factura_id' => $venta->id,
                'total_factura' => $venta->precio_total,
                'saldo_restante' => $saldo,
                'fecha_emision' => now(),
                'fecha_vencimiento' => now()->addDays(30),
                'estado' => $saldo <= 0 ? 'pagada' : 'pendiente',
            ]);

            if ($abono > 0) {
                AbonoFactura::create([
                    'cuenta_id' => $cuenta->id,
                    'monto' => $abono,
                    'usuario_id' => Auth::id(),
                    'fecha_abono' => now(),
                    'observaciones' => 'Abono inicial (desde cotización)',
                ]);
            }
        }

        // Actualizar estado de la cotización
        $cotizacion->estado = 'aprobada';
        $cotizacion->save();

        DB::commit();

        return redirect()->route('admin.ventas.index')
            ->with('mensaje', 'Cotización convertida en venta correctamente')
            ->with('icono', 'success');

    } catch (\Throwable $e) {
        DB::rollBack();
        return redirect()->back()
            ->with('mensaje', 'Error al convertir la cotización: ' . $e->getMessage())
            ->with('icono', 'error');
    }
}
public function agregarProducto(Request $request)
{
    $request->validate([
        'producto_id' => 'required|exists:productos,id',
        'cantidad' => 'required|integer|min:1',
    ]);

    $producto = Producto::find($request->producto_id);
    $user_id = Auth::id();
    $precio = $producto->precio_venta ?? 0; // o lo que uses

    $existente = TmpCotizacion::where('producto_id', $producto->id)
        ->where('user_id', $user_id)
        ->first();

    if ($existente) {
        $existente->cantidad += $request->cantidad;
        $existente->subtotal = $existente->cantidad * $precio;
        $existente->save();
    } else {
        TmpCotizacion::create([
            'producto_id' => $producto->id,
            'user_id' => $user_id,
            'cantidad' => $request->cantidad,
            'precio_unitario' => $precio,
            'subtotal' => $precio * $request->cantidad,
        ]);
    }
    
    return redirect()->back()->with('success', 'Producto agregado al carrito de cotización.');
}
public function eliminarProducto($id)
{
    $item = TmpCotizacion::findOrFail($id);
    if ($item->user_id == Auth::id()) {
        $item->delete();
        return redirect()->back()->with('success', 'Producto eliminado del carrito.');
    }
    return redirect()->back()->with('error', 'No tienes permiso para eliminar este producto.');
}


}
