@extends('layouts.app')

@section('content')
    <ul class="list-inline">
        <li class="list-inline-item">
            <form class="form-inline" id="literature-search-form">
                <div class="form-group">
                    <div class="input-group">
                        <div class="input-group-prepend">
                            <span class="input-group-text">
                                <i class="fas fa-fw fa-search"></i>
                            </span>
                        </div>
                        <input type="text" class="form-control" placehoder="Search&ellipsis;">
                    </div>
                </div>
            </form>
        </li>
        <li class="list-inline-item">
            <button type="button" class="btn btn-success" id="literature-add-button">
                <i class="fas fa-fw fa-plus"></i> New Bibliography Item
            </button>
        </li>
        <li class="list-inline-item">
            <button type="file" id="literature-import-button" class="btn btn-primary">
                <i class="fas fa-fw fa-download"></i> Import BibTex File
            </button>
        </li>
    </ul>
    <bibliography :entries="{{ $entries }}"></bibliography>
@endsection
