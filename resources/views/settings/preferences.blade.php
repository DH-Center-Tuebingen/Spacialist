@extends('layouts.app')

@section('content')
    <table class="table table-striped table-hover">
        <thead class="thead-light">
            <tr>
                <th>Preference</th>
                <th>Value</th>
                <th>Allow Override?</th>
                <th>Save</th>
            </tr>
        </thead>
        <tbody>
            <tr>
                <td>
                    <strong>Language</strong>
                </td>
                <td>
                    <input type="text" value="en" />
                </td>
                <td>
                    <input type="checkbox" value="true" checked />
                </td>
                <td>
                    <button type="button" class="btn btn-success">
                        <i class="fas fa-fw fa-check"></i>
                    </button>
                </td>
            </tr>
            <tr>
                <td>
                    <strong>Columns in Main View</strong>
                </td>
                <td>
                    <span></span>Left-Hand Column: <input type="number" value="2" />
                    <br />
                    <span></span>Center Column: <input type="number" value="5" />
                    <br />
                    <span></span>Right-Hand Column: <input type="number" value="5" />
                </td>
                <td>
                    <input type="checkbox" value="true" checked />
                </td>
                <td>
                    <button type="button" class="btn btn-success">
                        <i class="fas fa-fw fa-check"></i>
                    </button>
                </td>
            </tr>
            <tr>
                <td>
                    <strong>Show Tooltips</strong>
                </td>
                <td>
                    <input type="checkbox" checked />
                </td>
                <td>
                    <input type="checkbox" checked />
                </td>
                <td>
                    <button type="button" class="btn btn-success">
                        <i class="fas fa-fw fa-check"></i>
                    </button>
                </td>
            </tr>
            <tr>
                <td>
                    <strong>Thesaurus-Element for Tags</strong>
                </td>
                <td>
                    <input type="text" value="http://..." />
                </td>
                <td>
                    <input type="checkbox" checked="false" />
                </td>
                <td>
                    <button type="button" class="btn btn-success">
                        <i class="fas fa-fw fa-check"></i>
                    </button>
                </td>
            </tr>
            <tr>
                <td>
                    <strong>Loaded Extensions</strong>
                </td>
                <td>
                    TODO
                </td>
                <td>
                    <input type="checkbox" checked="false" />
                </td>
                <td>
                    <button type="button" class="btn btn-success">
                        <i class="fas fa-fw fa-check"></i>
                    </button>
                </td>
            </tr>
            <tr>
                <td>
                    <strong>Show Link To ThesauRex</strong>
                </td>
                <td>
                    <input type="text" value="http://..." />
                </td>
                <td>
                    <input type="checkbox" checked="false" />
                </td>
                <td>
                    <button type="button" class="btn btn-success">
                        <i class="fas fa-fw fa-check"></i>
                    </button>
                </td>
            </tr>
            <tr>
                <td>
                    <strong>Project Name</strong>
                </td>
                <td>
                    <input type="text" value="Spacialist with Vue!" />
                </td>
                <td>
                    <input type="checkbox" checked="false" />
                </td>
                <td>
                    <button type="button" class="btn btn-success">
                        <i class="fas fa-fw fa-check"></i>
                    </button>
                </td>
            </tr>
        </tbody>
    </table>
@endsection
