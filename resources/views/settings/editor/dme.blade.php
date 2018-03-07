@extends('layouts.app')

@section('content')
    <data-model
        :attributes="{{ $attributes }}"
        :concepts="{{ $concepts }}"
        :context-types="{{ $contextTypes }}"
        :preferences="{{ json_encode($p) }}"
        :values="{}">
    </data-model>
@endsection
