<template>
    <div id="literature-container" class="table-responsive">
        <table class="table table-striped table-hover" id="literature-table">
            <thead class="thead-light">
                <tr>
                    <td>
                        <a href="#" v-on:click="setOrderColumn('type')">
                            Type
                            <span v-show="orderColumn == 'type'">
                                <span v-show="orderType == 'asc'">
                                    <i class="fas fa-fw fa-sort-down"></i>
                                </span>
                                <span v-show="orderType == 'desc'">
                                    <i class="fas fa-fw fa-sort-up"></i>
                                </span>
                            </span>
                        </a>
                    </td>
                    <td>
                        <a href="#" v-on:click="setOrderColumn('citekey')">
                            Citation Key
                            <span v-show="orderColumn == 'citekey'">
                                <span v-show="orderType == 'asc'">
                                    <i class="fas fa-fw fa-sort-down"></i>
                                </span>
                                <span v-show="orderType == 'desc'">
                                    <i class="fas fa-fw fa-sort-up"></i>
                                </span>
                            </span>
                        </a>
                    </td>
                    <td>
                        <a href="#" v-on:click="setOrderColumn('author')">
                            Author
                            <span v-show="orderColumn == 'author'">
                                <span v-show="orderType == 'asc'">
                                    <i class="fas fa-fw fa-sort-down"></i>
                                </span>
                                <span v-show="orderType == 'desc'">
                                    <i class="fas fa-fw fa-sort-up"></i>
                                </span>
                            </span>
                        </a>
                    </td>
                    <td>
                        <a href="#" v-on:click="setOrderColumn('editor')">
                            Editor
                            <span v-show="orderColumn == 'editor'">
                                <span v-show="orderType == 'asc'">
                                    <i class="fas fa-fw fa-sort-down"></i>
                                </span>
                                <span v-show="orderType == 'desc'">
                                    <i class="fas fa-fw fa-sort-up"></i>
                                </span>
                            </span>
                        </a>
                    </td>
                    <td>
                        <a href="#" v-on:click="setOrderColumn('title')">
                            Title
                            <span v-show="orderColumn == 'title'">
                                <span v-show="orderType == 'asc'">
                                    <i class="fas fa-fw fa-sort-down"></i>
                                </span>
                                <span v-show="orderType == 'desc'">
                                    <i class="fas fa-fw fa-sort-up"></i>
                                </span>
                            </span>
                        </a>
                    </td>
                    <td>
                        <a href="#" v-on:click="setOrderColumn('journal')">
                            Journal
                            <span v-show="orderColumn == 'journal'">
                                <span v-show="orderType == 'asc'">
                                    <i class="fas fa-fw fa-sort-down"></i>
                                </span>
                                <span v-show="orderType == 'desc'">
                                    <i class="fas fa-fw fa-sort-up"></i>
                                </span>
                            </span>
                        </a>
                    </td>
                    <td>
                        <a href="#" v-on:click="setOrderColumn('year')">
                            Year
                            <span v-show="orderColumn == 'year'">
                                <span v-show="orderType == 'asc'">
                                    <i class="fas fa-fw fa-sort-down"></i>
                                </span>
                                <span v-show="orderType == 'desc'">
                                    <i class="fas fa-fw fa-sort-up"></i>
                                </span>
                            </span>
                        </a>
                    </td>
                    <td>
                        literature.bibtex.month
                    </td>
                    <td>
                        literature.bibtex.pages
                    </td>
                    <td>
                        literature.bibtex.volume
                    </td>
                    <td>
                        literature.bibtex.number
                    </td>
                    <td>
                        literature.bibtex.chapter
                    </td>
                    <td>
                        literature.bibtex.edition
                    </td>
                    <td>
                        literature.bibtex.series
                    </td>
                    <td>
                        <a href="#" v-on:click="setOrderColumn('booktitle')">
                            Book Title
                            <span v-show="orderColumn == 'booktitle'">
                                <span v-show="orderType == 'asc'">
                                    <i class="fas fa-fw fa-sort-down"></i>
                                </span>
                                <span v-show="orderType == 'desc'">
                                    <i class="fas fa-fw fa-sort-up"></i>
                                </span>
                            </span>
                        </a>
                    </td>
                    <td>
                        <a href="#" v-on:click="setOrderColumn('publisher')">
                            Publisher
                            <span v-show="orderColumn == 'publisher'">
                                <span v-show="orderType == 'asc'">
                                    <i class="fas fa-fw fa-sort-down"></i>
                                </span>
                                <span v-show="orderType == 'desc'">
                                    <i class="fas fa-fw fa-sort-up"></i>
                                </span>
                            </span>
                        </a>
                    </td>
                    <td>
                        <a href="#" v-on:click="setOrderColumn('address')">
                            Address
                            <span v-show="orderColumn == 'address'">
                                <span v-show="orderType == 'asc'">
                                    <i class="fas fa-fw fa-sort-down"></i>
                                </span>
                                <span v-show="orderType == 'desc'">
                                    <i class="fas fa-fw fa-sort-up"></i>
                                </span>
                            </span>
                        </a>
                    </td>
                    <td>
                        literature.bibtex.note
                    </td>
                    <td>
                        literature.created-at
                    </td>
                    <td>
                        literature.updated-at
                    </td>
                    <td>
                        <a href="#" v-on:click="setOrderColumn('misc')">
                            misc
                            <span v-show="orderColumn == 'misc'">
                                <span v-show="orderType == 'asc'">
                                    <i class="fas fa-fw fa-sort-down"></i>
                                </span>
                                <span v-show="orderType == 'desc'">
                                    <i class="fas fa-fw fa-sort-up"></i>
                                </span>
                            </span>
                        </a>
                    </td>
                    <td>
                        <a href="#" v-on:click="setOrderColumn('howpublished')">
                            howpublished
                            <span v-show="orderColumn == 'howpublished'">
                                <span v-show="orderType == 'asc'">
                                    <i class="fas fa-fw fa-sort-down"></i>
                                </span>
                                <span v-show="orderType == 'desc'">
                                    <i class="fas fa-fw fa-sort-up"></i>
                                </span>
                            </span>
                        </a>
                    </td>
                    <td>
                        literature.bibtex.institution
                    </td>
                    <td>
                        literature.bibtex.organization
                    </td>
                    <td>
                        literature.bibtex.school
                    </td>
                    <td>
                        literature.delete
                    </td>
                    <td>
                        literature.edit
                    </td>
                </tr>
            </thead>
            <tbody>
                <tr v-for="entry in orderedList">
                    <td>
                        {{ entry.type }}
                    </td>
                    <td>
                        {{ entry.citekey }}
                    </td>
                    <td>
                        {{ entry.author }}
                    </td>
                    <td>
                        {{ entry.editor }}
                    </td>
                    <td>
                        {{ entry.title }}
                    </td>
                    <td>
                        {{ entry.journal }}
                    </td>
                    <td>
                        {{ entry.year }}
                    </td>
                    <td>
                        {{ entry.month }}
                    </td>
                    <td>
                        {{ entry.pages }}
                    </td>
                    <td>
                        {{ entry.volume }}
                    </td>
                    <td>
                        {{ entry.number }}
                    </td>
                    <td>
                        {{ entry.chapter }}
                    </td>
                    <td>
                        {{ entry.edition }}
                    </td>
                    <td>
                        {{ entry.series }}
                    </td>
                    <td>
                        {{ entry.booktitle }}
                    </td>
                    <td>
                        {{ entry.publisher }}
                    </td>
                    <td>
                        {{ entry.address }}
                    </td>
                    <td>
                        {{ entry.note }}
                    </td>
                    <td>
                        {{ entry.created_at }}
                    </td>
                    <td>
                        {{ entry.updated_at }}
                    </td>
                    <td>
                        {{ entry.misc }}
                    </td>
                    <td>
                        {{ entry.howpublished }}
                    </td>
                    <td>
                        {{ entry.institution }}
                    </td>
                    <td>
                        {{ entry.organization }}
                    </td>
                    <td>
                        {{ entry.school }}
                    </td>
                    <td>
                        <button type="button" class="btn btn-outline-danger">
                            <i class="fas fa-fw fa-trash"></i>
                        </button>
                    </td>
                    <td>
                        <button type="button" class="btn btn-outline-info">
                            <i class="fas fa-fw fa-edit"></i>
                        </button>
                    </td>
                </tr>
            </tbody>
        </table>
    </div>
</template>

<script>
    export default {
        props: {
            entries: {
                type: Array,
                required: true
            }
        },
        mounted() {},
        methods: {
            setOrderColumn(column) {
                if(this.orderColumn == column) {
                    if(this.orderType == 'asc') {
                        this.orderType = 'desc';
                    } else {
                        this.orderType = 'asc';
                    }
                } else {
                    this.orderColumn = column;
                    this.orderType = 'asc';
                }
            }
        },
        data() {
            return {
                orderColumn: 'author',
                orderType: 'asc'
            }
        },
        computed: {
            orderedList: function() {
                return _.orderBy(this.entries, this.orderColumn, this.orderType);
            }
        }
    }
</script>
