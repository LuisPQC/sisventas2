@extends('adminlte::page')

@section('content_header')
<h1><b>Cuentas por Cobrar</b></h1>
<hr>
@stop

@section('content')
<div class="row">
    <div class="col-md-12">
        <div class="card card-outline card-primary">
            <div class="card-header">
                <h3>Cuentas por Cobrar registradas</h3>
            </div>
            <div class="card-body">

                <div class="table-responsive">
                    <table id="mitabla" class="table table-bordered table-hover table-striped table-sm">
                        <thead>
                            <tr>
                                <th>Nro</th>
                                <th>Cliente</th>
                                <th>Total Factura</th>
                                <th>Saldo Restante</th>
                                <th>Fecha Emisión</th>
                                <th>Fecha Vencimiento</th>
                                <th>Estado</th>
                                <th>Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php $contador = 1; ?>
                            @foreach($cuentas as $cuenta)
                            <tr>
                                <td style="text-align: center;vertical-align: middle">{{$contador++}}</td>
                                <td>{{ $cuenta->cliente->nombre_cliente ?? 'Sin cliente' }}</td>
                                <td>{{ number_format($cuenta->total_factura, 2) }}</td>
                                <td>{{ number_format($cuenta->saldo_restante, 2) }}</td>
                                <td>{{ $cuenta->fecha_emision }}</td>
                                <td>{{ $cuenta->fecha_vencimiento }}</td>
                                <td>
                                    @if($cuenta->estado == 'pagada')
                                        <span class="badge bg-success">Pagada</span>
                                    @elseif($cuenta->estado == 'vencida')
                                        <span class="badge bg-danger">Vencida</span>
                                    @else
                                        <span class="badge bg-warning text-dark">Pendiente</span>
                                    @endif
                                </td>
                                <td>
                                @if($cuenta->estado !== 'pagada')
                                    <a href="{{ route('admin.abonos.create', $cuenta->id) }}" class="btn btn-primary btn-sm">Abonar</a>
                                @endif
                                    <a href="{{ route('admin.cuentas.show', $cuenta->id) }}" class="btn btn-info btn-sm"><i class="fas fa-eye"></i> Ver</a>
                                    <form action="{{ route('admin.cuentas.destroy', $cuenta->id) }}" method="POST" style="display:inline-block;" onsubmit="return confirm('¿Estás seguro de eliminar esta cuenta?');">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-danger btn-sm"><i class="fas fa-trash"></i></button>
                                    </form>
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>
@stop

@section('js')
<script>
    $('#mitabla').DataTable({
        "pageLength": 5,
        "language": {
            "emptyTable": "No hay información",
            "info": "Mostrando _START_ a _END_ de _TOTAL_ Cuentas por cobrar",
            "infoEmpty": "Mostrando 0 a 0 de 0 Cuentas por cobrar",
            "infoFiltered": "(Filtrado de _MAX_ total Cuentas por cobrar)",
            "infoPostFix": "",
            "thousands": ",",
            "lengthMenu": "Mostrar _MENU_ Cuentas por cobrar",
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