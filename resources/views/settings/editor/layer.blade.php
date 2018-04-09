@extends('layouts.app')

@section('content')
    <layer-editor :baselayer="{{ $baselayers }}" :overlays="{{ $overlays }}" :concepts="{{ $concepts }}"></layer-editor>
@endsection
