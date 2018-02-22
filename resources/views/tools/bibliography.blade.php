@extends('layouts.app')

@section('content')
    <bibliography :entries="{{ $entries }}"></bibliography>
@endsection
