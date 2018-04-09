<template>
    <div>
        <ul class="ml-3 list-unstyled">
            <li v-for="l in layer" class="pb-1">
                <i class="far fa-fw fa-map"></i>
                <a href="#" @click="onSelect(l)">{{ getTitle(l) }}</a>
            </li>
            <li v-if="addNew">
                <i class="fas fa-fw fa-plus"></i>
                <a href="#" @click="addNew" class="text-secondary">New Layer...</a>
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
            concepts: {
                type: Object,
                required: false
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
                if(this.concepts && layer.context_type) {
                    const concept = this.concepts[layer.context_type.thesaurus_url];
                    if(concept) {
                        return concept.label;
                    }
                }
                return 'No Title';
            }
        },
        data() {
            return {

            }
        }
    }
</script>
