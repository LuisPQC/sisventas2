@extends('adminlte::page')

@section('content_header')
<h1><b>Ventas/Listado de ventas</b></h1>
<hr>
@stop

@section('content')
<div class="row">
    <div class="col-md-12">
        <div class="card card-outline card-primary">
            <div class="card-header">
                <h3 class="card-title">Ventas registradas</h3>
                <div class="card-tools">
                    <a href="{{url('/admin/ventas/reporte')}}" target="_blank" class="btn btn-danger"><i class="fa fa-file-pdf"></i> Reporte total</a>
                    <!-- Botón para abrir modal -->
                    <button class="btn btn-warning" data-toggle="modal" data-target="#modalReporteFechas">
                        <i class="fas fa-file-pdf"></i> Reporte por fechas
                    </button>

                    @if($arqueoAbierto)
                    <a href="{{url('/admin/ventas/create')}}" class="btn btn-primary"><i class="fa fa-plus"></i> Crear nuevo</a>
                    @else
                    <a href="{{url('/admin/arqueos/create')}}" class="btn btn-danger"><i class="fa fa-plus"></i> Abrir caja</a>
                    @endif
                </div>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table id="mitabla" class="table table-striped table-bordered table-hover table-sm">
                    <thead class="thead-light">
                        <tr>
                            <th scope="col" style="text-align: center">Nro</th>
                            <th scope="col">Fecha</th>
                            <th scope="col">Precio total</th>
                            <th scope="col">Productos</th>
                            <th scope="col" style="text-align: center">Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php $contador = 1; ?>
                        @foreach($ventas as $venta)
                        <tr>
                            <td style="text-align: center;vertical-align: middle">{{$contador++}}</td>
                            <td style="vertical-align: middle">{{$venta->fecha}}</td>
                            <td style="vertical-align: middle">{{$venta->precio_total}}</td>
                            <td style="vertical-align: middle">
                                <ul>
                                    @foreach($venta->detallesVenta as $detalle)
                                    <li>{{$detalle->producto->nombre.' - '.$detalle->cantidad.' Unidades'}}</li>
                                    @endforeach
                                </ul>
                            </td>
                            <td style="text-align: center;vertical-align: middle">
                                <div class="btn-group" role="group" aria-label="Basic example">
                                    <a href="{{url('/admin/ventas',$venta->id)}}" class="btn btn-info btn-sm"><i class="fas fa-eye"></i></a>
                                    <a href="{{url('/admin/ventas/'.$venta->id.'/edit')}}" class="btn btn-success btn-sm"><i class="fas fa-pencil"></i></a>
                                    <a href="{{url('/admin/ventas/pdf/'.$venta->id)}}" target="_blank" class="btn btn-warning btn-sm"><i class="fas fa-print"></i></a>
                                    <form action="{{url('/admin/ventas',$venta->id)}}" method="post"
                                        onclick="preguntar{{$venta->id}}(event)" id="miFormulario{{$venta->id}}">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-danger btn-sm" style="border-radius: 0px 4px 4px 0px"><i class="fas fa-trash"></i></button>
                                    </form>
                                    <script>
                                        function preguntar {
                                            {
                                                $venta - > id
                                            }
                                        }(event) {
                                            event.preventDefault();
                                            Swal.fire({
                                                title: '¿Desea eliminar esta registro?',
                                                text: '',
                                                icon: 'question',
                                                showDenyButton: true,
                                                confirmButtonText: 'Eliminar',
                                                confirmButtonColor: '#a5161d',
                                                denyButtonColor: '#270a0a',
                                                denyButtonText: 'Cancelar',
                                            }).then((result) => {
                                                if (result.isConfirmed) {
                                                    var form = $('#miFormulario{{$venta->id}}');
                                                    form.submit();
                                                }
                                            });
                                        }
                                    </script>
                                </div>
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

<!-- Modal -->
<div class="modal fade" id="modalReporteFechas" tabindex="-1" role="dialog" aria-labelledby="modalReporteLabel" aria-hidden="true">
  <div class="modal-dialog" role="document">
    <form action="{{ route('admin.ventas.reporte.pdf') }}" method="GET" target="_blank">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="modalReporteLabel">Generar reporte por fechas</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Cerrar">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <div class="form-group">
                    <label for="desde">Desde</label>
                    <input type="date" name="desde" class="form-control" required>
                </div>
                <div class="form-group">
                    <label for="hasta">Hasta</label>
                    <input type="date" name="hasta" class="form-control" required>
                </div>
            </div>
            <div class="modal-footer">
                <button type="submit" class="btn btn-success">
                    <i class="fas fa-file-pdf"></i> Generar PDF
                </button>
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancelar</button>
            </div>
        </div>
    </form>
  </div>
</div>

@stop

@section('css')
@stop

@section('js')
<script>
    $('#mitabla').DataTable({
        "pageLength": 5,
        "language": {
            "emptyTable": "No hay información",
            "info": "Mostrando _START_ a _END_ de _TOTAL_ Ventas",
            "infoEmpty": "Mostrando 0 a 0 de 0 Ventas",
            "infoFiltered": "(Filtrado de _MAX_ total Ventas)",
            "infoPostFix": "",
            "thousands": ",",
            "lengthMenu": "Mostrar _MENU_ Ventas",
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