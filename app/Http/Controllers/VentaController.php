<?php

namespace App\Http\Controllers;

use App\Models\Arqueo;
use App\Models\Cliente;
use App\Models\DetalleVenta;
use App\Models\Empresa;
use App\Models\MovimientoCaja;
use App\Models\Producto;
use App\Models\TmpVenta;
use App\Models\Venta;
use App\Models\CuentaPorCobrar;
use App\Models\AbonoFactura;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Barryvdh\DomPDF\Facade\Pdf;
use Nnjeim\World\Models\Currency;
use NumberToWords\NumberToWords;
use NumberFormatter;

class VentaController extends Controller
{
    /**
     * Display a listing of the resource.
     */

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
        $arqueoAbierto = Arqueo::whereNull('fecha_cierre')
            ->where('empresa_id', Auth::user()->empresa_id)
            ->first();
        $ventas = Venta::with('detallesVenta', 'cliente')
            ->where('empresa_id', Auth::user()->empresa_id)
            ->get();
        return view('admin.ventas.index', compact('ventas', 'arqueoAbierto'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $productos = Producto::where('empresa_id', Auth::user()->empresa_id)->get();
        $clientes = Cliente::where('empresa_id', Auth::user()->empresa_id)->get();

        $session_id = session()->getId();
        $tmp_ventas = TmpVenta::where('session_id', $session_id)->get();

        return view('admin.ventas.create', compact('productos', 'clientes', 'tmp_ventas'));
    }

    public function cliente_store(Request $request)
    {
        $validate = $request->validate([
            'nombre_cliente' => 'required',
            'nit_codigo' => 'required',
            'telefono' => 'required',
            'email' => 'required',
        ]);

        $cliente = new Cliente();
        $cliente->nombre_cliente = $request->nombre_cliente;
        $cliente->nit_codigo = $request->nit_codigo;
        $cliente->telefono = $request->telefono;
        $cliente->email = $request->email;
        $cliente->empresa_id = Auth::user()->empresa_id;
        $cliente->save();

        return response()->json(['success' => 'Cliente registrado']);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'fecha' => 'required',
            'precio_total' => 'required',
        ]);

        $session_id = session()->getId();

        $venta = new Venta();
        $venta->fecha = $request->fecha;
        $venta->precio_total = $request->precio_total;
        $venta->empresa_id = Auth::user()->empresa_id;
        $venta->cliente_id = $request->cliente_id;
        $venta->forma_pago = $request->forma_pago ?? 'contado'; // Asegúrate de tener este campo en la DB
        $venta->save();

        // REGISTRAR EN ARQUEO (solo si es contado o abono)
        $arqueo_id = Arqueo::whereNull('fecha_cierre')
            ->where('empresa_id', Auth::user()->empresa_id)
            ->first();

        if ($venta->forma_pago === 'contado') {
            $monto_ingreso = $venta->precio_total;
        } elseif ($venta->forma_pago === 'credito') {
            $monto_ingreso = floatval($request->monto_abonado ?? 0);
        }

        if ($monto_ingreso > 0) {
            $movimiento = new MovimientoCaja();
            $movimiento->tipo = "INGRESO";
            $movimiento->monto = $monto_ingreso;
            $movimiento->descripcion = "Venta de productos";
            $movimiento->arqueo_id = $arqueo_id->id;
            $movimiento->save();
        }

        // DETALLE VENTA
        $tmp_ventas = TmpVenta::where('session_id', $session_id)->get();

        foreach ($tmp_ventas as $tmp_venta) {
            $producto = Producto::find($tmp_venta->producto_id);

            DetalleVenta::create([
                'cantidad' => $tmp_venta->cantidad,
                'venta_id' => $venta->id,
                'producto_id' => $tmp_venta->producto_id,
            ]);

            $producto->stock -= $tmp_venta->cantidad;
            $producto->save();
        }

        // BORRAR CARRITO TEMPORAL
        TmpVenta::where('session_id', $session_id)->delete();

        // CREAR CUENTA POR COBRAR si es a crédito
        if ($venta->forma_pago === 'credito') {
            $abono = floatval($request->monto_abonado ?? 0);
            $saldo = $venta->precio_total - $abono;

            $cuenta = CuentaPorCobrar::create([
                'cliente_id' => $venta->cliente_id,
                'factura_id' => $venta->id,
                'total_factura' => $venta->precio_total,
                'saldo_restante' => $saldo,
                'fecha_emision' => now(),
                'fecha_vencimiento' => $request->fecha_vencimiento ?? now()->addDays(30),
                'estado' => $saldo <= 0 ? 'pagada' : 'pendiente',
            ]);

            if ($abono > 0) {
                AbonoFactura::create([
                    'cuenta_id' => $cuenta->id,
                    'monto' => $abono,
                    'usuario_id' => Auth::id(),
                    'fecha_abono' => now(),
                    'observaciones' => 'Abono inicial al momento de la venta',
                ]);
            }
        }

        return redirect()->route('admin.ventas.index')
            ->with('mensaje', 'Se registró la venta correctamente')
            ->with('icono', 'success');
    }

    public function pdf($id)
    {

        function numeroALetrasConDecimales($numero)
        {
            $formatter = new NumberFormatter("es", NumberFormatter::SPELLOUT);

            // Dividir el número en parte entera y decimal
            $partes = explode('.', number_format($numero, 2, '.', ''));

            $entero = $formatter->format($partes[0]);
            $decimal = $formatter->format($partes[1]);

            return ucfirst("$entero con $decimal/100");
        }

        $id_empresa = Auth::user()->empresa_id;
        $empresa = Empresa::where('id', $id_empresa)->first();
        $moneda = Currency::find($empresa->moneda);
        $venta = Venta::with('detallesVenta', 'cliente')->findOrFail($id);

        $numero = $venta->precio_total;
        $literal = numeroALetrasConDecimales($numero);

        $pdf = PDF::loadView('admin.ventas.pdf', compact('empresa', 'venta', 'moneda', 'literal'));
        return $pdf->stream();
        //return view('admin.ventas.pdf');
    }
    /**
     * Display the specified resource.
     */
    public function show($id)
    {
        $venta = Venta::with('detallesVenta', 'cliente')->findOrFail($id);
        return view('admin.ventas.show', compact('venta'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit($id)
    {
        $productos = Producto::where('empresa_id', Auth::user()->empresa_id)->get();
        $clientes = Cliente::where('empresa_id', Auth::user()->empresa_id)->get();
        $venta = Venta::with('detallesVenta', 'cliente')->findOrFail($id);
        return view('admin.ventas.edit', compact('venta', 'productos', 'clientes'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, $id)
    {
        //$datos = request()->all();
        //return response()->json($datos);
        $request->validate([
            'fecha' => 'required',
            'precio_total' => 'required',
        ]);

        $venta = Venta::find($id);
        $venta->fecha = $request->fecha;
        $venta->precio_total = $request->precio_total;
        $venta->cliente_id = $request->cliente_id;
        $venta->empresa_id = Auth::user()->empresa_id;
        $venta->save();

        return redirect()->route('admin.ventas.index')
            ->with('mensaje', 'Se actualizo la ventas de la manera correcta')
            ->with('icono', 'success');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy($id)
    {
        $venta = Venta::find($id);

        foreach ($venta->detallesVenta as $detalle) {
            $producto = Producto::find($detalle->producto_id);
            $producto->stock += $detalle->cantidad;
            $producto->save();
        }

        $venta->detallesVenta()->delete();

        Venta::destroy($id);

        return redirect()->route('admin.ventas.index')
            ->with('mensaje', 'Se elimino la venta de la manera correcta')
            ->with('icono', 'success');
    }

    public function reporte()
    {

        $empresa = Empresa::where('id', Auth::user()->empresa_id)->first();

        $ventas = Venta::with('cliente')
            ->where('empresa_id', Auth::user()->empresa_id)
            ->get();
        $pdf = PDF::loadView('admin.ventas.reporte', compact('ventas', 'empresa'));
        return $pdf->stream();
    }

    public function formularioReportePDF()
    {
        return view('admin.ventas.reporte_form');
    }
    public function generarReportePDF(Request $request)
    {
        $request->validate([
            'desde' => 'required|date',
            'hasta' => 'required|date|after_or_equal:desde',
        ]);

        $ventas = Venta::with('cliente')
            ->whereBetween('fecha', [$request->desde, $request->hasta])
            ->orderBy('fecha', 'asc')
            ->get();

        $empresa = Empresa::first(); // Ajusta según cómo guardás la info de empresa

        $pdf = Pdf::loadView('admin.ventas.reporte', compact('ventas', 'empresa'))
            ->setPaper('A4', 'portrait');

        return $pdf->stream('Reporte_Ventas_' . now()->format('Ymd_His') . '.pdf');
    }
}
