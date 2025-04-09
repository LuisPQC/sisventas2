@extends('adminlte::page')
@section('title','cotizaciones')
@section('content_header')
<h1><b>Listado de Cotizaciones</b></h1>
<hr>
@stop
@section('content')
<div class="row">
    <div class="col-md-12">
        <div class="card card-outline card-primary">
            <div class="card-header">
                <h3 class="card-title">Cotizaciones registrado</h3>
                <div class="card-tools">
                    <a href="{{url('/admin/cotizaciones/create')}}" class="btn btn-primary"><i class="fa fa-plus"></i> Crear nuevo</a>
                </div>
            </div>
            <div class="card-body table-responsive">
                <table id="mitabla" class="table table-bordered table-striped">
                    <thead class="">
                        <tr>
                            <th>ID</th>
                            <th>Cliente</th>
                            <th>Total</th>
                            <th>Fecha</th>
                            <th>Productos</th>
                            <th>Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($cotizaciones as $cotizacion)
                        <tr>
                            <td>{{ $cotizacion->id }}</td>
                            <td>{{ $cotizacion->cliente->nombre_cliente ?? 'N/A' }}</td>
                            <td>${{ number_format($cotizacion->total, 2) }}</td>
                            <td>{{ $cotizacion->fecha }}</td>
                            <td class="text-center">
                                <button class="btn btn-sm btn-info" data-toggle="modal" data-target="#modalProductos{{ $cotizacion->id }}">
                                    Ver productos
                                </button>

                                {{-- MODAL --}}
                                <div class="modal fade" id="modalProductos{{ $cotizacion->id }}" tabindex="-1" role="dialog" aria-labelledby="modalLabel{{ $cotizacion->id }}" aria-hidden="true">
                                    <div class="modal-dialog modal-lg" role="document">
                                        <div class="modal-content">

                                            <div class="modal-header">
                                                <h5 class="modal-title" id="modalLabel{{ $cotizacion->id }}">
                                                    Productos de la cotización #{{ $cotizacion->id }}
                                                </h5>
                                                <button type="button" class="close" data-dismiss="modal" aria-label="Cerrar">
                                                    <span aria-hidden="true">&times;</span>
                                                </button>
                                            </div>

                                            <div class="modal-body p-0">
                                                <table class="table table-bordered mb-0">
                                                    <thead class="thead-dark">
                                                        <tr>
                                                            <th>Producto</th>
                                                            <th class="text-center">Cantidad</th>
                                                            <th class="text-center">Precio Unitario</th>
                                                            <th class="text-center">Subtotal</th>
                                                        </tr>
                                                    </thead>
                                                    <tbody>
                                                        @php $total = 0; @endphp
                                                        @foreach ($cotizacion->detalles as $detalle)
                                                        <tr>
                                                            <td>{{ $detalle->producto->nombre }}</td>
                                                            <td class="text-center">{{ $detalle->cantidad }}</td>
                                                            <td class="text-center">${{ number_format($detalle->precio_unitario, 2) }}</td>
                                                            <td class="text-center">${{ number_format($detalle->subtotal, 2) }}</td>
                                                        </tr>
                                                        @php $total += $detalle->subtotal; @endphp
                                                        @endforeach
                                                    </tbody>
                                                    <tfoot>
                                                        <tr>
                                                            <td colspan="3" class="text-end"><strong>Total</strong></td>
                                                            <td class="text-center"><strong>${{ number_format($total, 2) }}</strong></td>
                                                        </tr>
                                                    </tfoot>
                                                </table>
                                            </div>

                                        </div>
                                    </div>
                                </div>
                            </td>
                            <td>
                                @if ($cotizacion->estado === 'pendiente')
                                <!-- <a href="{{ route('admin.cotizaciones.convertir', $cotizacion->id) }}" class="btn btn-sm btn-success">
                                    Convertir a Venta
                                </a>-->
                                @endif<a href="{{ route('admin.cotizaciones.reporte', ['id' => $cotizacion->id]) }}" target="_blank" class="btn btn-sm btn-primary">
                                    PDF con nombre
                                </a>

                                <a href="{{ route('admin.cotizaciones.reporte', ['id' => $cotizacion->id, 'mostrar_cliente' => 'no']) }}" target="_blank" class="btn btn-sm btn-outline-secondary">
                                    PDF sin nombre
                                </a>

                                <form action="{{ route('admin.cotizaciones.destroy', $cotizacion->id) }}" method="POST" class="d-inline" onsubmit="return confirm('¿Eliminar esta cotización?')">
                                    @csrf
                                    @method('DELETE')
                                    <button class="btn btn-sm btn-danger">Eliminar</button>
                                </form>
                            </td>
                        </tr>
                        @endforeach

                        @if ($cotizaciones->isEmpty())
                        <tr>
                            <td colspan="6" class="text-center">No hay cotizaciones registradas.</td>
                        </tr>
                        @endif
                    </tbody>
                </table>
            </div>

        </div>
    </div>
</div>
</div>
@endsection
@section('js')
<script>
    $('#mitabla').DataTable({
        "pageLength": 5,
        "language": {
            "emptyTable": "No hay información",
            "info": "Mostrando _START_ a _END_ de _TOTAL_ Cotizaciones",
            "infoEmpty": "Mostrando 0 a 0 de 0 Cotizaciones",
            "infoFiltered": "(Filtrado de _MAX_ total Cotizaciones)",
            "infoPostFix": "",
            "thousands": ",",
            "lengthMenu": "Mostrar _MENU_ Cotizaciones",
            "loadingRecords": "Cargando...",
            "processing": "Procesando...",
            "search": "Buscador:",
            "zeroRecords": "Sin resultados encontrados",
            "paginate": {
                "first": "Primero",
                "last": "Ultimo",
                "next": "Siguiente",
                "previous": "Anterior"
            }
        },
    });
</script>
@stop