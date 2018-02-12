@extends('layouts.app')

@section('content')
    <table class="table table-striped table-hover">
        <thead class="thead-light">
            <tr>
                <th>Name</th>
                <th>Display Name</th>
                <th>Description</th>
                <th>Permissions</th>
                <th>Created</th>
                <th>Updated</th>
                <th>Options</th>
            </tr>
        </thead>
        <tbody>
            <tr>
                <td>
                    admin
                </td>
                <td>
                    The "Admin"
                </td>
                <td>
                    I'm the BOSS!
                </td>
                <td>
                    TODO
                </td>
                <td>
                    today
                </td>
                <td>
                    yesterday
                </td>
                <td>
                    <div class="dropdown">
                        <span id="dropdownMenuButton" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                            <i class="fas fa-fw fa-ellipsis-h"></i>
                        </span>
                        <div class="dropdown-menu" aria-labelledby="dropdownMenuButton">
                            <a class="dropdown-item" href="#">
                                <i class="fas fa-fw fa-trash text-danger"></i> Delete
                            </a>
                            <a class="dropdown-item" href="#">
                                <i class="fas fa-fw fa-edit text-info"></i> Edit
                            </a>
                        </div>
                    </div>
                </td>
            </tr>
        </tbody>
    </table>
    <button type="button" class="btn btn-success">
        <i class="fas fa-fw fa-plus"></i> Add New Role
    </button>
@endsection
