<?php
namespace App\Http\Controllers;
use App\Models\CuentaPorCobrar;
use App\Models\AbonoFactura;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\Empresa;

class AbonoFacturaController extends Controller
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
    public function create($cuenta_id)
    {
        $cuenta = CuentaPorCobrar::with('cliente')->findOrFail($cuenta_id);
        return view('admin.abonos.create', compact('cuenta'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'cuenta_id' => 'required|exists:cuentas_por_cobrar,id',
            'monto' => 'required|numeric|min:0.01',
        ]);

        $cuenta = CuentaPorCobrar::findOrFail($request->cuenta_id);

        // Registrar abono
        AbonoFactura::create([
            'cuenta_id' => $cuenta->id,
            'monto' => $request->monto,
            'usuario_id' => Auth::id(),
            'observaciones' => $request->observaciones,
        ]);

        // Actualizar saldo restante
        $cuenta->saldo_restante -= $request->monto;

        if ($cuenta->saldo_restante <= 0) {
            $cuenta->estado = 'pagada';
            $cuenta->saldo_restante = 0;
        }

        $cuenta->save();

        return redirect()->route('admin.cuentas.index')->with('success', 'Abono registrado correctamente');
    }
}
