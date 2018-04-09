@extends('layouts.app')

@section('content')
    <gis
        :concepts="{{ $concepts }}"
        :context-types="{{ $contextTypes }}"
        :layers="{{ $contextLayers }}"
    >
    </gis>
@endsection
