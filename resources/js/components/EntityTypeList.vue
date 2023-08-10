<template>
    <div class="list-group scroll-y-auto px-2">
        <a href="#" @click.prevent="selectEntry(entry)" v-for="(entry, i) in state.entries" class="list-group-item list-group-item-action d-flex flex-row align-items-center" :class="{ 'active': entry.id == selectedId }" @mouseenter="onEnter(i)" @mouseleave="onLeave(i)" :key="i">
            <div>
                <i class="fas fa-fw fa-monument"></i>
                <span class="p-1">
                    {{ translateConcept(entry.thesaurus_url) }}
                </span>
            </div>
            <div class="ms-auto btn-fab-list" v-if="state.hasOnHoverListener" v-show="state.hoverStates[i]" :class="activeClasses(entry)">
                <button class="btn btn-outline-info btn-fab-sm rounded-circle" v-if="state.hasEditListener" @click="onEdit(entry)" data-bs-toggle="popover" :data-content="t('global.edit')" data-trigger="hover" data-placement="bottom">
                    <i class="fas fa-fw fa-xs fa-edit" style="vertical-align: 0;"></i>
                </button>
                <button class="btn btn-outline-primary btn-fab-sm rounded-circle" v-if="state.hasDuplicateListener" @click="onDuplicate(entry)" data-bs-toggle="popover" :data-content="t('global.duplicate')" data-trigger="hover" data-placement="bottom">
                    <i class="fas fa-fw fa-xs fa-clone" style="vertical-align: 0;"></i>
                </button>
                <button class="btn btn-outline-danger btn-fab-sm rounded-circle" v-if="state.hasDeleteListener" @click="onDelete(entry)" data-bs-toggle="popover" :data-content="t('global.delete')" data-trigger="hover" data-placement="bottom">
                    <i class="fas fa-fw fa-xs fa-trash" style="vertical-align: 0;"></i>
                </button>
            </div>
        </a>
    </div>
</template>

<script>
    import {
        computed,
        onMounted,
        reactive,
        toRefs,
    } from 'vue';

    import { useI18n } from 'vue-i18n';

    import {
        translateConcept,
    } from '@/helpers/helpers.js';

    export default {
        props: {
            data: {
                type: Array,
                required: true
            },
            selectedId: {
                type: Number,
                required: false,
                default: -1,
            }
        },
        setup(props, context) {
            const { t } = useI18n();
            const {
                data,
                selectedId,
            } = toRefs(props);
            // FETCH

            // FUNCTIONS
            const onEnter = i => {
                state.hoverStates[i] = true;
            };
            const onLeave = i => {
                state.hoverStates[i] = false;
            };
            const activeClasses = entry => {
                if(entry.id != selectedId.value) return [];

                return ['badge', 'rounded-pill', 'bg-light'];
            };
            const selectEntry = entityType => {
                context.emit('select-element', {type: entityType});
            };
            const onEdit = entityType => {
                context.emit('edit-element', {type: entityType});
            }
            const onDuplicate = entityType => {
                context.emit('duplicate-element', {id: entityType.id});
            }
            const onDelete = entityType => {
                context.emit('delete-element', {type: entityType});
            }

            // DATA
            const state = reactive({
                hoverStates: new Array(data.value.length).fill(false),
                entries: computed(_ => data.value.slice()),
                hasDeleteListener: !!context.attrs.onDeleteElement,
                hasDuplicateListener: !!context.attrs.onDuplicateElement,
                hasEditListener: !!context.attrs.onEditElement,
                hasOnHoverListener: computed(_ => state.hasDeleteListener || state.hasDuplicateListener || state.hasEditListener),
            });

            // ON MOUNTED
            onMounted(_ => {

            });

            // RETURN
            return {
                t,
                // HELPERS
                translateConcept,
                // LOCAL
                onEnter,
                onLeave,
                activeClasses,
                selectEntry,
                onEdit,
                onDuplicate,
                onDelete,
                // PROPS
                selectedId,
                // STATE
                state,
            }
        },
    }
</script>
