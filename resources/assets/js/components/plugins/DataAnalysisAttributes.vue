<template>
    <div class="table-responsive mt-1">
        <table class="table table-striped table-hover table-bordered table-sm table-cell-250">
            <thead class="thead-light sticky-top">
                <tr>
                    <th v-show="$parent.showAmbiguous">
                        Name
                        <a href="#">
                            <i class="fas fa-fw fa-search"></i>
                        </a>
                    </th>
                    <th v-show="$parent.showAmbiguous">
                        Corresponding Context
                        <a href="#">
                            <i class="fas fa-fw fa-search"></i>
                        </a>
                    </th>
                    <th v-show="$parent.showAmbiguous">
                        Value
                        <a href="#">
                            <i class="fas fa-fw fa-search"></i>
                        </a>
                    </th>
                    <th v-show="$parent.showAmbiguous">
                        Certainty
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
                        {{ $parent.getConceptLabel(res.attribute.thesaurus_url) }}
                    </td>
                    <td v-show="$parent.showAmbiguous">
                        <span v-if="res.context">
                            {{ res.context.name }}
                        </span>
                    </td>
                    <td v-show="$parent.showAmbiguous">
                        {{ res.value }}
                    </td>
                    <td v-show="$parent.showAmbiguous">
                        {{ res.possibility }}%
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
