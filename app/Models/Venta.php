<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Venta extends Model
{
    use HasFactory;

    public function detallesVenta(){
        return $this->hasMany(detalleVenta::class);
    }

    public function cliente(){
        return $this->belongsTo(Cliente::class);
    }
}
