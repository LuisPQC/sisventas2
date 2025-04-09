@extends('adminlte::page')

@section('title', 'Nueva Cotización')

@section('content_header')
    <h1>Nueva Cotización</h1>
@stop

@section('content')

@if ($errors->any())
    <div class="alert alert-danger">
        <ul class="mb-0">
            @foreach ($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
@endif

{{-- FORMULARIO PARA AGREGAR PRODUCTOS --}}
<div class="card mb-3">
    <div class="card-header">Agregar Productos</div>
    <div class="card-body">
    <form action="{{url('/admin/cotizaciones/agregar-producto')}}" method="post">
            @csrf
            <div class="row align-items-end">
                <div class="col-md-6">
                    <label for="producto_id">Producto</label>
                    <select name="producto_id" class="form-control" required>
                        <option value="">-- Selecciona un producto --</option>
                        @foreach ($productos as $producto)
                            <option value="{{ $producto->id }}">
                                {{ $producto->nombre }} (Stock: {{ $producto->stock }})
                            </option>
                        @endforeach
                    </select>
                </div>

                <div class="col-md-2">
                    <label for="cantidad">Cantidad</label>
                    <input type="number" name="cantidad" class="form-control" min="1" required>
                </div>

                <div class="col-md-2">
                    <button type="submit" class="btn btn-success mt-3">Agregar</button>
                </div>
            </div>
        </form>
    </div>
</div>

{{-- TABLA DE PRODUCTOS EN CARRITO --}}
<div class="card mb-3">
    <div class="card-header">Productos en la Cotización</div>
    <div class="card-body p-0">
        <table class="table table-bordered">
            <thead>
                <tr>
                    <th>Producto</th>
                    <th>Cantidad</th>
                    <th>Precio Unitario</th>
                    <th>Subtotal</th>
                    <th></th>
                </tr>
            </thead>
            <tbody>
                @php $total = 0; @endphp
                @forelse ($carrito as $item)
                    <tr>
                        <td>{{ $item->producto->nombre }}</td>
                        <td>{{ $item->cantidad }}</td>
                        <td>${{ number_format($item->precio_unitario, 2) }}</td>
                        <td>${{ number_format($item->subtotal, 2) }}</td>
                        <td>
                            <form action="{{ route('admin.cotizaciones.eliminarProducto', $item->id) }}" method="POST" onsubmit="return confirm('¿Eliminar producto del carrito?')">
                                @csrf
                                @method('DELETE')
                                <button class="btn btn-danger btn-sm">Eliminar</button>
                            </form>
                        </td>
                    </tr>
                    @php $total += $item->subtotal; @endphp
                @empty
                    <tr>
                        <td colspan="5" class="text-center">No hay productos en el carrito.</td>
                    </tr>
                @endforelse
            </tbody>
            <tfoot>
                <tr>
                    <td colspan="3" class="text-end"><strong>Total</strong></td>
                    <td colspan="2"><strong>${{ number_format($total, 2) }}</strong></td>
                </tr>
            </tfoot>
        </table>
    </div>
</div>

{{-- FORMULARIO PARA GUARDAR COTIZACIÓN --}}
<form action="{{ route('admin.cotizaciones.store') }}" method="POST">
    @csrf
    <div class="card mb-3">
        <div class="card-body">
            <div class="row mb-3">
                <div class="col-md-6">
                    <label for="cliente_id">Cliente</label>
                    <select name="cliente_id" class="form-control" required>
                        <option value="">-- Selecciona un cliente --</option>
                        @foreach ($clientes as $cliente)
                            <option value="{{ $cliente->id }}">{{ $cliente->nombre_cliente }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-3">
                    <label for="fecha">Fecha</label>
                    <input type="date" name="fecha" class="form-control" value="{{ now()->format('Y-m-d') }}" required>
                </div>
            </div>

            <input type="hidden" name="total" value="{{ $total }}">

            <div class="text-end">
                <button class="btn btn-primary">Guardar Cotización</button>
            </div>
        </div>
    </div>
</form>

@endsection
