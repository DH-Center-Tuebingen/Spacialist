@extends('layouts.app')

@section('content')
    <table class="table table-striped table-hover">
        <thead class="thead-light">
            <tr>
                <th>Name</th>
                <th>E-Mail-Address</th>
                <th>Roles</th>
                <th>Added</th>
                <th>Updated</th>
                <th>Options</th>
            </tr>
        </thead>
        <tbody>
            <tr>
                <td>
                    Admin
                </td>
                <td>
                    admin@admin.com
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
                                <i class="fas fa-fw fa-paper-plane text-info"></i> Send Reset-Mail
                            </a>
                            <a class="dropdown-item" href="#">
                                <i class="fas fa-fw fa-trash text-danger"></i> Delete
                            </a>
                        </div>
                    </div>
                </td>
            </tr>
        </tbody>
    </table>
@endsection
