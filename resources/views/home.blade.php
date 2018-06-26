@extends('layouts.app')

@section('content')
    <main-view
        :bibliography="{{$bibliography}}"
        :context-types="{{$contextTypes}}"
        :preferences="{{json_encode($p)}}"
        :roots="{{$roots}}">
    </main-view>
@endsection
