@extends('layouts.app')

@section('content')
    <main-view
        :concepts="{{$concepts}}"
        :context-types="{{$contextTypes}}"
        :preferences="{{json_encode($p)}}"
        :tree="{{$roots}}">
    </main-view>
@endsection
