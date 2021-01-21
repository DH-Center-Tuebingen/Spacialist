<template>
    <ul class="ms-3 list-unstyled mb-0">
        <li v-for="(d, i) in entries" class="py-1 pe-1 d-flex align-items-center justify-content-between hover-item" @mouseenter="onEnter(i)" @mouseleave="onLeave(i)">
            <div>
                <i class="fas fa-fw fa-monument"></i>
                <a class="p-1" href="#" :class="{ 'font-weight-bold': d.id == selectedElement.id }" @click.prevent="select(d)">
                    {{ $translateConcept(d.thesaurus_url) }}
                </a>
            </div>
            <div class="ms-auto" v-show="hoverState[i]">
                <button class="btn btn-info btn-fab rounded-circle" v-if=" hasEditListener" @click="$emit('edit', {type: d})" data-bs-toggle="popover" :data-content="$t('global.edit')" data-trigger="hover" data-placement="bottom">
                    <i class="fas fa-fw fa-xs fa-edit" style="vertical-align: 0;"></i>
                </button>
                <button class="btn btn-primary btn-fab rounded-circle" v-if=" hasDuplicateListener" @click="$emit('duplicate', {id: d.id})" data-bs-toggle="popover" :data-content="$t('global.duplicate')" data-trigger="hover" data-placement="bottom">
                    <i class="fas fa-fw fa-xs fa-clone" style="vertical-align: 0;"></i>
                </button>
                <button class="btn btn-danger btn-fab rounded-circle" v-if=" hasDeleteListener" @click="$emit('delete', {type: d})" data-bs-toggle="popover" :data-content="$t('global.delete')" data-trigger="hover" data-placement="bottom">
                    <i class="fas fa-fw fa-xs fa-trash" style="vertical-align: 0;"></i>
                </button>
            </div>
        </li>
        <li v-if="hasAddListener">
            <i class="fas fa-fw fa-plus"></i>
            <a href="#" @click.prevent="$emit('add')" class="text-secondary">
                {{ $t('main.datamodel.entity.add-button') }}
            </a>
        </li>
    </ul>
</template>

<script>
    export default {
        props: {
            data: {
                type: Array,
                required: true
            }
        },
        beforeMount() {
            // Enable popovers
            $(function () {
                $('[data-bs-toggle="popover"]').popover()
            });
        },
        mounted() {},
        methods: {
            onEnter(i) {
                Vue.set(this.hovered, i, true);
            },
            onLeave(i) {
                Vue.set(this.hovered, i, false);
            },
            select(entityType) {
                this.selectedElement = Object.assign({}, entityType);
                this.$emit('select', {type: entityType})
            }
        },
        data() {
            return {
                hovered: [],
                selectedElement: {}
            }
        },
        created() {
            for(let i=0; i<this.data.length; i++) {
                this.hovered.push(false);
            }
        },
        computed: {
            hoverState: function() {
                return this.hovered;
            },
            entries: function() {
                return this.data.slice();
            },
            hasListeners: function() {
                return !!this.$listeners;
            },
            hasAddListener() {
                return this.hasListeners && this.$listeners.add;
            },
            hasDeleteListener() {
                return this.hasListeners && this.$listeners.delete;
            },
            hasDuplicateListener() {
                return this.hasListeners && this.$listeners.duplicate;
            },
            hasEditListener() {
                return this.hasListeners && this.$listeners.edit;
            }
        }
    }
</script>
