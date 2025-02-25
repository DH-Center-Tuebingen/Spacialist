<template>
    <div class="entity-type-list">
        <div class="mb-2">
            <ListToolbar v-model="state.order" />
        </div>
        <div class="list-group overflow-y-auto">
            <Alert
                v-if="state.entries.length > 0 && state.sortedEntries.length === 0"
                type="info"
                :message="t('global.search_no_results_for') + ' ' + state.order.text"
            />
            <a
                v-for="(entry, i) in state.sortedEntries"
                :key="i"
                href="#"
                class="list-group-item list-group-item-action d-flex flex-row align-items-center py-1 px-3"
                :class="{ 'active': entry.id == selectedId }"
                @click.prevent="selectEntry(entry)"
                @mouseenter="onEnter(i)"
                @mouseleave="onLeave(i)"
            >
                <div class="d-flex flex-fill">
                    <span class="flex-fill">
                        {{ translateConcept(entry.thesaurus_url) }}
                    </span>
                </div>
                <div
                    v-if="state.hasOnHoverListener"
                    v-show="state.hoverStates[i]"
                    class="ms-auto btn-fab-list bg-white position-absolute z-1 end-0 me-2"
                    :class="activeClasses(entry)"
                >
                    <button
                        v-if="state.hasEditListener"
                        class="btn btn-outline-info btn-fab-sm rounded-circle"
                        data-bs-toggle="popover"
                        :data-content="t('global.edit')"
                        data-trigger="hover"
                        data-placement="bottom"
                        @click="onEdit(entry)"
                    >
                        <i
                            class="fas fa-fw fa-xs fa-edit"
                            style="vertical-align: 0;"
                        />
                    </button>
                    <button
                        v-if="state.hasDuplicateListener"
                        class="btn btn-outline-primary btn-fab-sm rounded-circle"
                        data-bs-toggle="popover"
                        :data-content="t('global.duplicate')"
                        data-trigger="hover"
                        data-placement="bottom"
                        @click="onDuplicate(entry)"
                    >
                        <i
                            class="fas fa-fw fa-xs fa-clone"
                            style="vertical-align: 0;"
                        />
                    </button>
                    <button
                        v-if="state.hasDeleteListener"
                        class="btn btn-outline-danger btn-fab-sm rounded-circle"
                        data-bs-toggle="popover"
                        :data-content="t('global.delete')"
                        data-trigger="hover"
                        data-placement="bottom"
                        @click="onDelete(entry)"
                    >
                        <i
                            class="fas fa-fw fa-xs fa-trash"
                            style="vertical-align: 0;"
                        />
                    </button>
                </div>
            </a>
        </div>
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
    import { _cloneDeep } from '../helpers/helpers';
    import ListToolbar from './forms/ListToolbar.vue';

    export default {
        components: {
            ListToolbar,
        },
        props: {
            data: {
                type: Array,
                required: true
            },
            selectedId: {
                type: Number,
                required: false,
                default: -1,
            },
            onDeleteElement: {
                type: Function,
                required: false,
            },
            onDuplicateElement: {
                type: Function,
                required: false,
            },
            onEditElement: {
                type: Function,
                required: false,
            },
            onSelectElement: {
                type: Function,
                required: false,
            },
        },
        setup(props, context) {
            const { t } = useI18n();
            const {
                data,
                selectedId,
                onDeleteElement,
                onDuplicateElement,
                onEditElement,
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
                // if(entry.id != selectedId.value) return [];

                return ['badge', 'rounded-pill', 'bg-light'];
            };
            const selectEntry = entityType => {
                context.emit('select-element', { type: entityType });
            };
            const onEdit = entityType => {
                context.emit('edit-element', { type: entityType });
            };
            const onDuplicate = entityType => {
                context.emit('duplicate-element', { id: entityType.id });
            };
            const onDelete = entityType => {
                context.emit('delete-element', { type: entityType });
            };

            // DATA
            const state = reactive({
                hoverStates: new Array(data.value.length).fill(false),
                entries: computed(_ => data.value.slice()),
                sortedEntries: computed(_ => {
                    let entries = _cloneDeep(state.entries);
                    entries = entries.map(entry => {
                        entry.translated = translateConcept(entry.thesaurus_url);
                        return entry;
                    });
                    let filtered = entries.filter((entry) => {
                        return entry.translated.toLowerCase().includes(state.order.text.toLowerCase());
                    });

                    if(state.order.type === 'text') {
                        return filtered.toSorted((a, b) => a.translated.localeCompare(b.translated) * (state.order.asc ? 1 : -1));
                    } else {
                        return state.order.asc ? filtered : filtered.reverse();
                    }
                }),
                hasDeleteListener: !!onDeleteElement.value,
                hasDuplicateListener: !!onDuplicateElement.value,
                hasEditListener: !!onEditElement.value,
                hasOnHoverListener: computed(_ => state.hasDeleteListener || state.hasDuplicateListener || state.hasEditListener),
                order: {
                    text: '',
                    asc: true,
                    type: 'number',
                }
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
                // STATE
                state,
            };
        },
    };
</script>
