<template>
    <div class="table-responsive">
        <table class="table table-striped table-hover table-bordered table-sm table-cell-250">
            <thead class="thead-light sticky-top">
                <tr>
                    <th>
                        Author
                        <a href="#">
                            <i class="fas fa-fw fa-search"></i>
                        </a>
                    </th>
                    <th>
                        Title
                        <a href="#">
                            <i class="fas fa-fw fa-search"></i>
                        </a>
                    </th>
                    <th>
                        Type
                        <a href="#">
                            <i class="fas fa-fw fa-search"></i>
                        </a>
                    </th>
                    <th>
                        Year
                        <a href="#">
                            <i class="fas fa-fw fa-search"></i>
                        </a>
                    </th>
                    <th>
                        Volume
                        <a href="#">
                            <i class="fas fa-fw fa-search"></i>
                        </a>
                    </th>
                    <th>
                        Series
                        <a href="#">
                            <i class="fas fa-fw fa-search"></i>
                        </a>
                    </th>
                    <th>
                        School
                        <a href="#">
                            <i class="fas fa-fw fa-search"></i>
                        </a>
                    </th>
                    <th>
                        Publisher
                        <a href="#">
                            <i class="fas fa-fw fa-search"></i>
                        </a>
                    </th>
                    <th>
                        Pages
                        <a href="#">
                            <i class="fas fa-fw fa-search"></i>
                        </a>
                    </th>
                    <th>
                        Organization
                        <a href="#">
                            <i class="fas fa-fw fa-search"></i>
                        </a>
                    </th>
                    <th>
                        Number
                        <a href="#">
                            <i class="fas fa-fw fa-search"></i>
                        </a>
                    </th>
                    <th>
                        Note
                        <a href="#">
                            <i class="fas fa-fw fa-search"></i>
                        </a>
                    </th>
                    <th>
                        Month
                        <a href="#">
                            <i class="fas fa-fw fa-search"></i>
                        </a>
                    </th>
                    <th>
                        Journal
                        <a href="#">
                            <i class="fas fa-fw fa-search"></i>
                        </a>
                    </th>
                    <th>
                        Institution
                        <a href="#">
                            <i class="fas fa-fw fa-search"></i>
                        </a>
                    </th>
                    <th>
                        Chapter
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
                        {{ res.author }}
                    </td>
                    <td>
                        {{ res.title || res.booktitle }}
                    </td>
                    <td>
                        {{ res.type }}
                    </td>
                    <td>
                        {{ res.year }}
                    </td>
                    <td>
                        {{ res.volume }}
                    </td>
                    <td>
                        {{ res.series }}
                    </td>
                    <td>
                        {{ res.school }}
                    </td>
                    <td>
                        {{ res.publisher }}
                    </td>
                    <td>
                        {{ res.pages }}
                    </td>
                    <td>
                        {{ res.organization }}
                    </td>
                    <td>
                        {{ res.number }}
                    </td>
                    <td>
                        {{ res.note }}
                    </td>
                    <td>
                        {{ res.month }}
                    </td>
                    <td>
                        {{ res.journal }}
                    </td>
                    <td>
                        {{ res.institution }}
                    </td>
                    <td>
                        {{ res.chapter }}
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
