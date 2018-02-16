<template>
    <div>
        <ul class="ml-3 list-unstyled">
            <li v-for="d in data" class="pb-1">
                <i class="far fa-fw fa-map"></i>
                <a href="#" v-if="d.name.length">{{ d.name }}</a>
                <a href="#" v-else>{{ d.context_type.thesaurus_url }}</a>
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
            addNew: {
                type: Function,
                required: false
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
