<?php
namespace App\Http\Controllers;
use Illuminate\Support\Facades\Auth;
use App\Models\CuentaPorCobrar;
use App\Models\AbonoFactura;
use Illuminate\Http\Request;
use Barryvdh\DomPDF\Facade\Pdf;
use App\Models\Empresa;

class CuentaPorCobrarController extends Controller
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
        $cuentas = CuentaPorCobrar::with('cliente')->get();
        return view('admin.cuentas.index', compact('cuentas'));
    }
    public function show($id)
{
    $cuenta = CuentaPorCobrar::with(['cliente', 'factura'])->findOrFail($id);
    $abonos = $cuenta->abonos()->with('usuario')->get();

    return view('admin.cuentas.show', compact('cuenta', 'abonos'));
}
public function imprimir($id)
{
    $cuenta = CuentaPorCobrar::with(['cliente', 'factura'])->findOrFail($id);
    $abonos = $cuenta->abonos()->with('usuario')->get();

    $pdf = Pdf::loadView('admin.cuentas.pdf', compact('cuenta', 'abonos'));
    return $pdf->stream('CuentaPorCobrar_'.$cuenta->id.'.pdf');
}
public function destroy($id)
{
    $cuenta = CuentaPorCobrar::with('abonos')->findOrFail($id);

    // Primero eliminamos los abonos asociados
    $cuenta->abonos()->delete();

    // Luego eliminamos la cuenta por cobrar
    $cuenta->delete();

    return redirect()->route('admin.cuentas.index')
        ->with('mensaje', 'Cuenta por cobrar y sus abonos fueron eliminados correctamente.')
        ->with('icono', 'success');
}
}
