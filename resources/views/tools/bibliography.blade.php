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
    <div id="literature-container" class="table-responsive">
        <table class="table table-striped table-hover" id="literature-table">
            <thead class="thead-light">
                <tr>
                    <td>
                        <a href="">
                            literature.bibtex.type
                        </a>
                    </td>
                    <td>
                        <a href="">
                            literature.bibtex.citekey
                        </a>
                    </td>
                    <td>
                        <a href="">
                            literature.bibtex.author
                        </a>
                    </td>
                    <td>
                        <a href="">
                            literature.bibtex.editor
                        </a>
                    </td>
                    <td>
                        <a href="">
                            literature.bibtex.title
                        </a>
                    </td>
                    <td>
                        <a href="">
                            literature.bibtex.journal
                        </a>
                    </td>
                    <td>
                        <a href="">
                            literature.bibtex.year
                        </a>
                    </td>
                    <td>
                    	literature.bibtex.month
                    </td>
                    <td>
                    	literature.bibtex.pages
                    </td>
                    <td>
                        literature.bibtex.volume
                    </td>
                    <td>
                        literature.bibtex.number
                    </td>
                    <td>
                    	literature.bibtex.chapter
                    </td>
                    <td>
                    	literature.bibtex.edition
                    </td>
                    <td>
                    	literature.bibtex.series
                    </td>
                    <td>
                        <a href="">
                            literature.bibtex.booktitle
                        </a>
                    </td>
                    <td>
                        <a href="">
                            literature.bibtex.publisher
                        </a>
                    </td>
                    <td>
                        <a href="">
                            literature.bibtex.address
                        </a>
                    </td>
                    <td>
                    	literature.bibtex.note
                    </td>
                    <td>
                    	literature.created-at
                    </td>
                    <td>
                    	literature.updated-at
                    </td>
                    <td>
                        <a href="">
                            literature.bibtex.misc
                        </a>
                    </td>
                    <td>
                        <a href="">
                            literature.bibtex.howpublished
                        </a>
                    </td>
                    <td>
                    	literature.bibtex.institution
                    </td>
                    <td>
                    	literature.bibtex.organization
                    </td>
                    <td>
                    	literature.bibtex.school
                    </td>
                    <td>
                        literature.delete
                    </td>
                    <td>
                        literature.edit
                    </td>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td>
                        type
                    </td>
                    <td>
                        citekey
                    </td>
                    <td>
                    	author
                    </td>
                    <td>
                    	editor
                    </td>
                    <td>
                    	title
                    </td>
                    <td>
                    	journal
                    </td>
                    <td>
                    	year
                    </td>
                    <td>
                    	month
                    </td>
                    <td>
                    	pages
                    </td>
                    <td>
                    	volume
                    </td>
                    <td>
                    	number
                    </td>
                    <td>
                    	chapter
                    </td>
                    <td>
                    	edition
                    </td>
                    <td>
                    	series
                    </td>
                    <td>
                    	booktitle
                    </td>
                    <td>
                    	publisher
                    </td>
                    <td>
                    	address
                    </td>
                    <td>
                    	note
                    </td>
                    <td>
                    	created_at
                    </td>
                    <td>
                    	updated_at
                    </td>
                    <td>
                    	misc
                    </td>
                    <td>
                    	howpublished
                    </td>
                    <td>
                    	institution
                    </td>
                    <td>
                    	organization
                    </td>
                    <td>
                    	school
                    </td>
                    <td>
                        <button type="button" class="btn btn-outline-danger">
                            <i class="fas fa-fw fa-trash"></i>
                        </button>
                    </td>
                    <td>
                        <button type="button" class="btn btn-outline-info">
                            <i class="fas fa-fw fa-edit"></i>
                        </button>
                    </td>
                </tr>
            </tbody>
        </table>
    </div>
@endsection
