<template>
    <div class="table-responsive">
        <table class="table table-striped table-hover table-bordered table-sm table-cell-250">
            <thead class="thead-light sticky-top">
                <tr>
                    <th>
                        Name
                        <a href="#" data-container="body" data-toggle="popover" data-trigger="focus" data-placement="bottom" data-content="Test" title="Title">
                            <i class="fas fa-fw fa-search"></i>
                        </a>
                    </th>
                    <th v-show="$parent.showAmbiguous">
                        Context-Type
                        <a href="#">
                            <i class="fas fa-fw fa-search"></i>
                        </a>
                    </th>
                    <th v-show="$parent.showAmbiguous">
                        Attributes
                        <a href="#">
                            <i class="fas fa-fw fa-search"></i>
                        </a>
                    </th>
                    <th v-for="(value, k) in $parent.splitResults">
                        {{ k }}
                        <a href="#">
                            <i class="fas fa-fw fa-trash text-danger"></i>
                        </a>
                    </th>
                    <th v-show="$parent.showAmbiguous">
                        Geodata
                        <a href="#">
                            <i class="fas fa-fw fa-search"></i>
                        </a>
                    </th>
                    <th v-show="$parent.showAmbiguous">
                        Literature
                        <a href="#">
                            <i class="fas fa-fw fa-search"></i>
                        </a>
                    </th>
                    <th v-show="$parent.showAmbiguous">
                        Files
                        <a href="#">
                            <i class="fas fa-fw fa-search"></i>
                        </a>
                    </th>
                    <th v-show="$parent.showAmbiguous">
                        Child Contexts
                        <a href="#">
                            <i class="fas fa-fw fa-search"></i>
                        </a>
                    </th>
                    <th v-show="$parent.showAmbiguous">
                        Parent Context
                        <a href="#">
                            <i class="fas fa-fw fa-search"></i>
                        </a>
                    </th>
                    <th v-show="$parent.showAmbiguous">
                        Created At
                        <a href="#">
                            <i class="fas fa-fw fa-search"></i>
                        </a>
                    </th>
                    <th v-show="$parent.showAmbiguous">
                        Updated At
                        <a href="#">
                            <i class="fas fa-fw fa-search"></i>
                        </a>
                    </th>
                    <th v-show="$parent.showAmbiguous">
                        Last Editor
                        <a href="#">
                            <i class="fas fa-fw fa-search"></i>
                        </a>
                    </th>
                </tr>
            </thead>
            <tbody>
                <tr v-for="res in $parent.combinedResults">
                    <td>
                        {{ res.name }}
                    </td>
                    <td v-show="$parent.showAmbiguous">
                        <i class="fas fa-fw fa-seedling"></i> {{ $parent.getConceptLabel(res.context_type.thesaurus_url) }}
                    </td>
                    <td v-show="$parent.showAmbiguous">
                        <ul>
                            <li v-for="attr in res.attributes">
                                {{ $parent.getConceptLabel(attr.thesaurus_url) }}
                                <a v-if="$parent.datatypeSupportsSplit(attr.datatype)" href="" @click="$parent.addSplit('attributes', 'id', attr.id, attr.thesaurus_url)">
                                    <i class="fas fa-fw fa-share-square"></i>
                                </a>
                            </li>
                        </ul>
                    </td>
                    <td v-show="$parent.showAmbiguous">
                        <div v-if="res.geodata">
                            {{ res.geodata.wkt }}
                            <a v-if="res.geodata.geom" href="" @click="$parent.openGeographyModal(res.geodata.geom)">
                                <i class="fas fa-fw fa-map-marker-alt"></i>
                            </a>
                        </div>
                    </td>
                    <td v-show="$parent.showAmbiguous">
                        <ul>
                            <li v-for="bib in res.literatures">
                                {{ bib.title }} - {{ bib.author }}
                            </li>
                        </ul>
                    </td>
                    <td v-show="$parent.showAmbiguous">
                        <ul>
                            <li v-for="file in res.files">
                                {{ file.name }}
                            </li>
                        </ul>
                    </td>
                    <td v-show="$parent.showAmbiguous">
                        <ul>
                            <li v-for="child in res.child_contexts">
                                {{ child.name }}
                            </li>
                        </ul>
                    </td>
                    <td v-show="$parent.showAmbiguous">
                        <span v-if="res.root_context">
                            {{ res.root_context.name }}
                        </span>
                    </td>
                    <td v-show="$parent.showAmbiguous">
                        {{ res.created_at }}
                    </td>
                    <td v-show="$parent.showAmbiguous">
                        {{ res.updated_at }}
                    </td>
                    <td v-show="$parent.showAmbiguous">
                        {{ res.lasteditor }}
                    </td>
                </tr>
            </tbody>
        </table>
    </div>
</template>

<script>
    export default {
        props: {
        },
        mounted() {
            $('[data-toggle="popover"]').popover();
        }
    }
</script>
