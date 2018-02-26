@extends('layouts.app')

@section('content')
    <data-model
        :attributes="{{ $attributes }}"
        :concepts="{{ $concepts }}"
        :context-types="{{ $contextTypes }}"
        :values="{}">
    </data-model>
@endsection
