<template>
    <div>
        <ul class="ms-3 list-unstyled">
            <li v-for="(l, i) in layer" class="pb-1 d-flex align-items-center justify-content-between" @mouseenter="onEnter(i)" @mouseleave="onLeave(i)">
                <i class="fas fa-fw fa-map-marked-alt"></i>
                <a class="p-1" href="#" @click.prevent="onSelect(l)">
                    {{ getTitle(l) }}
                </a>
                <span class="ms-auto">
                    <button class="btn btn-danger btn-fab rounded-circle" v-show="hoverStates[i] && deleteAllowed(l)" @click="onDelete(l)">
                        <i class="fas fa-fw fa-xs fa-trash" style="vertical-align: 0;"></i>
                    </button>
                </span>
            </li>
            <li v-if="addNew">
                <i class="fas fa-fw fa-plus"></i>
                <a href="#" @click.prevent="addNew" class="text-secondary" v-html="$t('plugins.map.new-item')"></a>
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
            },
            onDelete: {
                type: Function,
                required: false
            }
        },
        mounted() {},
        methods: {
            getTitle(layer) {
                if(layer.name) {
                    return layer.name
                }
                if(layer.entity_type) {
                    return this.$translateConcept(layer.entity_type.thesaurus_url);
                }
                return this.$t('plugins.map.untitled');
            },
            deleteAllowed(layer) {
                if(!this.onDelete) return false;
                return layer.type != 'unlinked' && !layer.entity_type_id;
            },
            onEnter(i) {
                Vue.set(this.hoverStates, i, true);
            },
            onLeave(i) {
                Vue.set(this.hoverStates, i, false);
            },
        },
        data() {
            return {
                hoverStates: {}
            }
        }
    }
</script>
