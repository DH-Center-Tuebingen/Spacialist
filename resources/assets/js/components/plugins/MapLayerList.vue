<template>
    <div>
        <ul class="ml-3 list-unstyled">
            <li v-for="l in layer" class="pb-1">
                <i class="fas fa-fw fa-map-marked-alt"></i>
                <a href="#" @click.prevent="onSelect(l)">{{ getTitle(l) }}</a>
            </li>
            <li v-if="addNew">
                <i class="fas fa-fw fa-plus"></i>
                <a href="#" @click="addNew" class="text-secondary" v-html="$t('plugins.map.new-item')"></a>
            </li>
        </ul>
    </div>
</template>

<script>
    export default {
        props: {
            layer: {
                type: Array,
                required: true
            },
            addNew: {
                type: Function,
                required: false
            },
            onSelect: {
                type: Function,
                required: false,
                default: l => l
            }
        },
        mounted() {},
        methods: {
            getTitle(layer) {
                if(layer.name) {
                    return layer.name
                }
                if(layer.context_type) {
                    return this.$translateConcept(layer.context_type.thesaurus_url);
                }
                return this.$t('plugins.map.untitled');
            }
        },
        data() {
            return {

            }
        }
    }
</script>
