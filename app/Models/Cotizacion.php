<?php

namespace App\Models;
use Illuminate\Database\Eloquent\Factories\HasFactory;

use Illuminate\Database\Eloquent\Model;

class Cotizacion extends Model
{
    
    use HasFactory;
    protected $table = 'cotizaciones';
    protected $fillable = [
        'cliente_id',
        'user_id',
        'total',
        'estado',
        'fecha',
    ];

    public function cliente()
    {
        return $this->belongsTo(Cliente::class);
    }

    public function usuario()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function detalles()
    {
        return $this->hasMany(DetalleCotizacion::class);
    }
}
