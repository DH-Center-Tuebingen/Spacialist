@extends('layouts.app')

@section('content')
    <gis
        :entity-types="{{ $entityTypes }}"
        :layers="{{ $entityLayers }}"
    >
    </gis>
@endsection
