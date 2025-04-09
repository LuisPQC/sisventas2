<?php

namespace App\Models;
use Illuminate\Database\Eloquent\Factories\HasFactory;

use Illuminate\Database\Eloquent\Model;

class TmpCotizacion extends Model
{
    use HasFactory;
    protected $table = 'tmp_cotizaciones';
    protected $fillable = [
        'producto_id',
        'user_id',
        'cantidad',
        'precio_unitario',
        'subtotal',
    ];

    public function producto()
    {
        return $this->belongsTo(Producto::class);
    }

    public function usuario()
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}
