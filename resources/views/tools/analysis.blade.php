@extends('layouts.app')

@section('content')
    <div class="row">
        <div class="col-md-3">
            <h4>Query Options</h4>
            <h5>Active Filters</h5>
            <p>
                No filters active.
            </p>
            <h5>Further Query Options</h5>
            <form>
                <div class="form-check">
                    <input type="checkbox" class="form-check-input" id="apply-changes-toggle" checked />
                    <label for="apply-changes-toggle" class="form-check-label">Apply Changes immediately</label>
                </div>
                <div class="form-group row">
                    <label for="table" class="col-md-3 col-form-label">Table</label>
                    <div class="col-md-9">
                        <input type="text" class="form-control" id="table" value="TODO" />
                    </div>
                </div>
                <div class="form-check">
                    <input type="checkbox" class="form-check-input" id="metadata-columns-toggle" checked />
                    <label for="metadata-columns-toggle" class="form-check-label">Show Metadata Columns</label>
                </div>
                <div class="form-check">
                    <input type="checkbox" class="form-check-input" id="expert-mode-toggle" checked />
                    <label for="expert-mode-toggle" class="form-check-label">Expert Mode</label>
                </div>
                <button type="submit" class="btn btn-default mt-2">
                    <i class="fas fa-fw fa-filter"></i> Filter
                </button>
            </form>
        </div>
        <div class="col-md-9">
            <h4>Results <span class="badge badge-primary">30</span></h4>
            <p class="text-secondary">
                1-30 / 54
            </p>
            <ul class="pagination">
                <li class="page-item disabled">
                    <a href="#" class="page-link">
                        <i class="fas fa-fw fa-arrow-left"></i> Previous 0 results
                    </a>
                </li>
                <li class="page-item">
                    <a href="#" class="page-link">
                        <i class="fas fa-fw fa-arrow-right"></i> Next 24 results
                    </a>
                </li>
            </ul>
            <ul class="nav nav-tabs">
                <li class="nav-item">
                    <a class="nav-link active" href="#">
                        <i class="fas fa-fw fa-puzzle-piece"></i> Query Builder
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="#">
                        <i class="fas fa-fw fa-chart-line"></i> Visualization
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="#">
                        <i class="fas fa-fw fa-upload"></i> Export
                    </a>
                </li>
            </ul>
            <div id="literature-container" class="table-responsive mt-1">
                <table class="table table-striped table-hover table-bordered" id="literature-table">
                    <thead class="thead-light">
                        <tr>
                            <th>
                                Name
                                <a href="#">
                                    <i class="fas fa-fw fa-search"></i>
                                </a>
                            </th>
                            <th>
                                Context-Type
                                <a href="#">
                                    <i class="fas fa-fw fa-search"></i>
                                </a>
                            </th>
                            <th>
                                Attributes
                                <a href="#">
                                    <i class="fas fa-fw fa-search"></i>
                                </a>
                            </th>
                            <th>
                                Geodata
                                <a href="#">
                                    <i class="fas fa-fw fa-search"></i>
                                </a>
                            </th>
                            <th>
                                Literature
                                <a href="#">
                                    <i class="fas fa-fw fa-search"></i>
                                </a>
                            </th>
                            <th>
                                Files
                                <a href="#">
                                    <i class="fas fa-fw fa-search"></i>
                                </a>
                            </th>
                            <th>
                                Child Contexts
                                <a href="#">
                                    <i class="fas fa-fw fa-search"></i>
                                </a>
                            </th>
                            <th>
                                Parent Context
                                <a href="#">
                                    <i class="fas fa-fw fa-search"></i>
                                </a>
                            </th>
                            <th>
                                Created At
                                <a href="#">
                                    <i class="fas fa-fw fa-search"></i>
                                </a>
                            </th>
                            <th>
                                Updated At
                                <a href="#">
                                    <i class="fas fa-fw fa-search"></i>
                                </a>
                            </th>
                            <th>
                                Last Editor
                                <a href="#">
                                    <i class="fas fa-fw fa-search"></i>
                                </a>
                            </th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td>
                                quo
                            </td>
                            <td>
                                <i class="fas fa-fw fa-seedling"></i> a
                            </td>
                            <td>
                                <ul>
                                    <li>eum (list)</li>
                                </ul>
                            </td>
                            <td>
                                POINT(10.609361 50.044158)
                                <a href="#">
                                    <i class="fas fa-fw fa-map-marker-alt"></i>
                                </a>
                            </td>
                            <td>
                            </td>
                            <td>
                            </td>
                            <td>
                                <ul>
                                    <li>ab</li>
                                    <li>Test für Datum</li>
                                    <li>Test für Datum (es muss das untere 'incidunt' sein...)</li>
                                </ul>
                            </td>
                            <td>
                            </td>
                            <td>
                                2017-10-06 13:13:39
                            </td>
                            <td>
                                2017-10-06 13:13:39
                            </td>
                            <td>
                                Monserrate Veum Sr.
                            </td>
                        </tr>
                        <tr>
                            <td>
                                a
                            </td>
                            <td>
                                <i class="fas fa-fw fa-seedling"></i> aut
                            </td>
                            <td>
                            </td>
                            <td>
                            </td>
                            <td>
                            </td>
                            <td>
                            </td>
                            <td>
                            </td>
                            <td>
                                et
                            </td>
                            <td>
                                2017-10-06 13:13:39
                            </td>
                            <td>
                                2017-10-06 13:13:39
                            </td>
                            <td>
                                Dr. Lisandro Wisoky
                            </td>
                        </tr>
                        <tr>
                            <td>
                                qui
                            </td>
                            <td>
                                <i class="fas fa-fw fa-seedling"></i> id
                            </td>
                            <td>
                                <ul>
                                    <li>est (stringf) <i class="fas fa-fw fa-reply"></i></li>
                                    <li>a (string-mc)</li>
                                    <li>est (stringf) <i class="fas fa-fw fa-reply"></i></li>
                                </ul>
                            </td>
                            <td>
                                POINT(14.17841 50.777991)
                                <a href="#">
                                    <i class="fas fa-fw fa-map-marker-alt"></i>
                                </a>
                            </td>
                            <td>
                            </td>
                            <td>
                            </td>
                            <td>
                                <ul>
                                    <li>fuga</li>
                                    <li>natus</li>
                                    <li>libero</li>
                                </ul>
                            </td>
                            <td>
                            </td>
                            <td>
                                2017-10-06 13:13:39
                            </td>
                            <td>
                                2017-10-06 13:13:39
                            </td>
                            <td>
                                Victoria Witting I
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
@endsection
