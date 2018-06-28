@extends('layouts.app')

@section('content')
    <main-view
        :bibliography="{{$bibliography}}"
        :roots="{{$roots}}">
    </main-view>
@endsection
