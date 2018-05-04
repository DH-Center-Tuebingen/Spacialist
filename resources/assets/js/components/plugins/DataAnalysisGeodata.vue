<template>
    <div class="table-responsive mt-1">
        <table class="table table-striped table-hover table-bordered table-sm table-cell-250">
            <thead class="thead-light sticky-top">
                <tr>
                    <th>
                        Geometry
                        <a href="#">
                            <i class="fas fa-fw fa-search"></i>
                        </a>
                    </th>
                    <th>
                        Context
                        <a href="#">
                            <i class="fas fa-fw fa-search"></i>
                        </a>
                    </th>
                    <th>
                        Color
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
                    <td ng-show="$ctrl.showAmbiguous">
                        {{ res.geowkt }}
                        <a href="" @click="$parent.openGeographyModal(res.geom)">
                            <i class="fas fa-fw fa-map-marker-alt"></i>
                        </a>
                    </td>
                    <td ng-show="$ctrl.showAmbiguous">
                        <span v-if="res.context">
                            {{ res.context.name }}
                        </span>
                    </td>
                    <td>
                        {{ res.color }}
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
