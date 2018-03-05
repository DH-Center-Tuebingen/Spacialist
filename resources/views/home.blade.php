@extends('layouts.app')

@section('content')
    <main-view
        :concepts="{{$concepts}}"
        :context-types="{{$contextTypes}}"
        :tree="{{$roots}}">
    </main-view>
@endsection
