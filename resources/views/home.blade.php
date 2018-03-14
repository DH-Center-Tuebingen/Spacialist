@extends('layouts.app')

@section('content')
    <main-view
        :concepts="{{$concepts}}"
        :context-types="{{$contextTypes}}"
        :preferences="{{json_encode($p)}}"
        :roots="{{$roots}}">
    </main-view>
@endsection
