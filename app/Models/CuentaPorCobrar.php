<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\Cliente;
use App\Models\Venta;
use App\Models\AbonoFactura;

class CuentaPorCobrar extends Model
{
    protected $table = 'cuentas_por_cobrar';

    protected $fillable = [
        'cliente_id',
        'factura_id',
        'total_factura',
        'saldo_restante',
        'fecha_emision',
        'fecha_vencimiento',
        'estado',
    ];

    public function cliente()
    {
        return $this->belongsTo(Cliente::class);
    }

    public function factura()
    {
        return $this->belongsTo(Venta::class, 'factura_id');
    }
    public function abonos()
    {
        return $this->hasMany(AbonoFactura::class, 'cuenta_id');
    }
}
