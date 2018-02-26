@extends('layouts.app')

@section('content')
    <user-preferences :preferences="{{ $preferences }}" :user-id="{{ $user_id }}"></user-preferences>
@endsection
