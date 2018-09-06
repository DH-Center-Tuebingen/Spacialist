<template>
    <div>
        <ul class="ml-3 list-unstyled">
            <li v-for="l in layer" class="pb-1" @contextmenu.prevent="openContextMenu($event, l)">
                <i class="fas fa-fw fa-map-marked-alt"></i>
                <a href="#" @click.prevent="onSelect(l)">{{ getTitle(l) }}</a>
            </li>
            <li v-if="addNew">
                <i class="fas fa-fw fa-plus"></i>
                <a href="#" @click.prevent="addNew" class="text-secondary" v-html="$t('plugins.map.new-item')"></a>
            </li>
        </ul>

        <vue-context ref="layerMenu" class="context-menu-wrapper">
            <ul class="list-group list-group-vue-context" slot-scope="fileScope" v-if="fileScope.data">
                <li class="list-group-item list-group-item-vue-context" v-for="entry in contextMenuEntries" @click.prevent="entry.callback(fileScope.data.layer)">
                    <i :class="entry.getIconClasses(fileScope.data.layer)">
                        {{ entry.getIconContent(fileScope.data.layer) }}
                    </i>
                    {{ entry.getLabel(fileScope.data.layer) }}
                </li>
            </ul>
        </vue-context>
    </div>
</template>

<script>
    import { VueContext } from 'vue-context';

    export default {
        components: {
            VueContext
        },
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
            contextMenu: {
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
            openContextMenu(event, layer) {
                if(!this.contextMenu) return;
                this.contextMenuEntries = this.contextMenu(layer);
                this.$refs.layerMenu.open(event, {layer: layer});
            }
        },
        data() {
            return {
                contextMenuEntries: []
            }
        }
    }
</script>
