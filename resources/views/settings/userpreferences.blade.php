@extends('layouts.app')

@section('content')
    <user-preferences :preferences="{{ $preferences }}"></user-preferences>
@endsection
