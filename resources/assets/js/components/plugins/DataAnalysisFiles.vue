<template>
    <div class="table-responsive mt-1">
        <table class="table table-striped table-hover table-bordered table-sm table-cell-250">
            <thead class="thead-light sticky-top">
                <tr>
                    <th>
                        Name
                        <a href="#">
                            <i class="fas fa-fw fa-search"></i>
                        </a>
                    </th>
                    <th>
                        Modified
                        <a href="#">
                            <i class="fas fa-fw fa-search"></i>
                        </a>
                    </th>
                    <th>
                        Created
                        <a href="#">
                            <i class="fas fa-fw fa-search"></i>
                        </a>
                    </th>
                    <th>
                        Size
                        <a href="#">
                            <i class="fas fa-fw fa-search"></i>
                        </a>
                    </th>
                    <th>
                        Description
                        <a href="#">
                            <i class="fas fa-fw fa-search"></i>
                        </a>
                    </th>
                    <th>
                        Copyright
                        <a href="#">
                            <i class="fas fa-fw fa-search"></i>
                        </a>
                    </th>
                    <th>
                        Mime Type
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
                    <td>
                        {{ res.modified }}
                    </td>
                    <td>
                        {{ res.created }}
                    </td>
                    <td>
                        {{ res.size | bytes }}
                    </td>
                    <td>
                        {{ res.description }}
                    </td>
                    <td>
                        {{ res.copyright }}
                    </td>
                    <td>
                        {{ res.mime_type }}
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
