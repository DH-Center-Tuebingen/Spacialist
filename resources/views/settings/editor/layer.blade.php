@extends('layouts.app')

@section('content')
    <div class="row">
        <div class="col-md-3">
            <h5>Baselayer</h5>
            <button type="button" class="btn btn-success">
                <i class="fas fa-fw fa-plus"></i> Add New Baselayer
            </button>
            <h5>Overlays</h5>
            <button type="button" class="btn btn-success">
                <i class="fas fa-fw fa-plus"></i> Add New Overlay
            </button>
        </div>
        <div class="col-md-9">
            <h5>Properties</h5>
        </div>
    </div>
@endsection
