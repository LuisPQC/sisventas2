@extends('adminlte::page')

@section('title', 'Generar Reporte de Ventas')

@section('content_header')
    <h1>Generar reporte de ventas (PDF)</h1>
@stop

@section('content')
    <form action="{{ route('ventas.reporte.pdf') }}" method="GET" target="_blank">
        <div class="row mb-4">
            <div class="col-md-4">
                <label>Desde</label>
                <input type="date" name="desde" class="form-control" required>
            </div>
            <div class="col-md-4">
                <label>Hasta</label>
                <input type="date" name="hasta" class="form-control" required>
            </div>
            <div class="col-md-4 d-flex align-items-end">
                <button type="submit" class="btn btn-success">
                    <i class="fas fa-file-pdf"></i> Generar reporte PDF
                </button>
                <a href="{{ route('admin.ventas.index') }}" class="btn btn-secondary ml-2">Volver</a>
            </div>
        </div>
    </form>
@stop
