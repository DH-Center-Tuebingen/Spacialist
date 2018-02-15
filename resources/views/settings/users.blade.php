@extends('layouts.app')

@section('content')
    <users :users="{{ $users }}" :roles="{{ $roles }}"></users>
@endsection
