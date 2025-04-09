<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\CuentaPorCobrar;
use App\Models\User;

class AbonoFactura extends Model
{
    protected $table = 'abonos_factura';

    protected $fillable = [
        'cuenta_id',
        'monto',
        'usuario_id',
        'fecha_abono',
        'observaciones'
    ];

    public function cuenta()
    {
        return $this->belongsTo(CuentaPorCobrar::class, 'cuenta_id');
    }

    public function usuario()
    {
        return $this->belongsTo(User::class, 'usuario_id');
    }
}
