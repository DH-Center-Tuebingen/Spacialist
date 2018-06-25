@extends('layouts.app')

@section('content')
    <data-model
        :attributes="{{ $attributes }}"
        :context-types="{{ $contextTypes }}"
        :preferences="{{ json_encode($p) }}"
        :values="{}">
    </data-model>
@endsection
