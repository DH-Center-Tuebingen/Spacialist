@extends('layouts.app')

@section('content')
    <div class="row">
        <div class="col-md-2" id="tree-container">
            <div>
                <h3>Contexts</h3>
                <div class="col-md-12">
                    <form>
                        <div class="form-group row">
                            <div class="input-group">
                                <div class="input-group-prepend">
                                    <span class="input-group-text">
                                        <i class="fas fa-search"></i>
                                    </span>
                                </div>
                                <input class="form-control" type="text" placeholder="Search" />
                            </div>
                        </div>
                    </form>
                </div>
                <div class="col-md-12">
                    <context-tree :contexts="{{$contexts}}"></context-tree>
                </div>
            </div>
        </div>
        <div class="col-md-5" style="border-right: 1px solid #ddd; border-left: 1px solid #ddd;" id="attribute-container" >
            Test
        </div>
        <div class="col-md-5" id="addon-container">
            <ul class="nav nav-tabs">
                <li class="nav-item">
                    <a class="nav-link active" href="#">
                        <i class="fas fa-fw fa-map-marker-alt"></i> Map
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="#">
                        <i class="fas fa-fw fa-folder"></i> Files
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="#">
                        <i class="fas fa-fw fa-bookmark"></i> References
                    </a>
                </li>
            </ul>
            <ol-map></ol-map>
        </div>
    </div>
@endsection
