@extends('layouts.app')

@section('content')
    <data-model
        :attributes="{{ $attributes }}"
        :values="{}">
    </data-model>
@endsection
