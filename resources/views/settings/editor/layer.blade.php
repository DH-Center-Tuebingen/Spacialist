@extends('layouts.app')

@section('content')
    <layer-editor :baselayer="{{ $baselayers }}" :overlays="{{ $overlays }}"></layer-editor>
@endsection
