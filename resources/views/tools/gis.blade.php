@extends('layouts.app')

@section('content')
    <gis
        :context-types="{{ $contextTypes }}"
        :layers="{{ $contextLayers }}"
    >
    </gis>
@endsection
