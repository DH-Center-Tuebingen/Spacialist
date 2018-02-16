@extends('layouts.app')

@section('content')
    <div class="row">
        <div class="col-md-3">
            <h5>Baselayer</h5>
            <layer :data="{{ $baselayers }}"></layer>
            <h5>Overlays</h5>
            <layer :data="{{ $overlays }}"></layer>
        </div>
        <div class="col-md-9">
            <h5>Properties</h5>
        </div>
    </div>
@endsection
