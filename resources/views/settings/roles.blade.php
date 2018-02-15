@extends('layouts.app')

@section('content')
    <roles :roles="{{ $roles }}" :permissions="{{ $permissions }}"></roles>
@endsection
