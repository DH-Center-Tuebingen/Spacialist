@extends('layouts.app')

@section('content')
    <data-analysis :concepts="{{$concepts}}">
    </data-analysis>
@endsection
