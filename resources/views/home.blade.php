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
                    <context-tree :contexts="{{$contexts}}" :selection-callback="onSelectContext"></context-tree>
                </div>
            </div>
        </div>
        <div class="col-md-5" style="border-right: 1px solid #ddd; border-left: 1px solid #ddd;" id="attribute-container" >
            <h1 v-if="selectedContext.name">@{{ selectedContext.name }}</h1>
            <h1 v-else>Nothing selected</h1>
        </div>
        <div class="col-md-5" id="addon-container">
            <ul class="nav nav-tabs">
                <li class="nav-item">
                    <a class="nav-link" href="#" v-bind:class="{active: tab == 'map'}" v-on:click="tab = 'map'">
                        <i class="fas fa-fw fa-map-marker-alt"></i> Map
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="#" v-bind:class="{active: tab == 'files'}" v-on:click="tab = 'files'">
                        <i class="fas fa-fw fa-folder"></i> Files
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="#" v-bind:class="{active: tab == 'references'}" v-on:click="tab = 'references'">
                        <i class="fas fa-fw fa-bookmark"></i> References
                    </a>
                </li>
            </ul>
            <div class="mt-2">
                <ol-map v-if="tab == 'map'"></ol-map>
                <div v-if="tab == 'files'">
                    <h2>Files loaded</h2>
                </div>
                <div v-if="tab == 'references'">
                    <h2>References loaded</h2>
                </div>
            </div>
        </div>
    </div>
@endsection
