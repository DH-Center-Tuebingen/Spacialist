@extends('layouts.app')

@section('content')
    <div class="row">
        <div class="col-md-2">
            <button type="button" class="btn btn-default">
                <i class="fas fa-fw fa-download"></i> Import Geodata
            </button>
            <p class="alert alert-info">
                List of all context-type connected layers
            </p>
        </div>
        <div class="col-md-10">
            <ol-map></ol-map>
        </div>
    </div>
@endsection
