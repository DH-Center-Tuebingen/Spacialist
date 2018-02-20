<template>
    <div>
        <ul class="ml-3 list-unstyled">
            <li v-for="d in data" class="pb-1">
                <i class="fas fa-fw fa-leaf"></i>
                <a href="#">{{ concepts[d.thesaurus_url].label }}</a>
            </li>
            <!-- <li v-if="addNew"> -->
            <li>
                <i class="fas fa-fw fa-plus"></i>
                <a href="#" v-on:click="addNew()" class="text-secondary">New Layer...</a>
            </li>
        </ul>
    </div>
</template>

<script>
    export default {
        props: {
            data: {
                type: Array,
                required: true
            },
            concepts: {
                type: Object,
                required: true
            }
        },
        mounted() {},
        methods: {},
        data() {
            return {
            }
        }
    }
</script>
