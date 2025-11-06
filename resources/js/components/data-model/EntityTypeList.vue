<template>
    <div class="entity-type-list">
        <header v-if="$slots.header">
            <slot name="header"></slot>
        </header>
        <div class="mb-2">
            <ListToolbar v-model="state.order" />
        </div>
        <div class="list-group overflow-y-auto">
            <Alert
                v-if="state.entries.length > 0 && state.sortedEntityTypes.length === 0"
                type="info"
                :message="t('global.search_no_results_for') + ' ' + state.order.text"
            />
            <a
                v-for="(entityType, index) in state.sortedEntityTypes"
                :key="index"
                href="#"
                class="list-group-item list-group-item-action d-flex flex-row align-items-center py-1 px-3"
                :class="{ 'active': entityType.id == selectedId }"
                @click.prevent="selectEntityType(entityType)"
                @mouseenter="onEnter(index)"
                @mouseleave="onLeave(index)"
            >
                <div class="d-flex flex-fill">
                    <span class="flex-fill">
                        {{ translateConcept(entityType.thesaurus_url) }}
                    </span>
                </div>
                <FabButtonList
                    v-if="index == state.hoveredItem"
                    class="position-absolute end-0 me-2"
                    :buttons="getControlButtons(entityType)"
                />
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
    
    import { FabButtonList } from 'dhc-components';

    import { useI18n } from 'vue-i18n';

    import {
        _cloneDeep,
        sortAlphabeticallyBy,
        translateConcept,
    } from '@/helpers/helpers.js';

    import {
        showDeleteEntityType,
        showEditEntityType,
    } from '@/helpers/modal.js';
    
    import ListToolbar from '../forms/ListToolbar.vue';

    export default {
        components: {
            FabButtonList,
            ListToolbar,
        },
        emits: ['select'],
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
        },
        setup(props, context) {
            const { t } = useI18n();

            // FUNCTIONS
            const onEnter = i => {
                state.hoveredItem = i;
            };
            const onLeave = i => {
                state.hoveredItem = -1;
            };
            const activeClasses = entry => {
                return ['badge', 'rounded-pill', 'bg-light'];
            };
            
            const selectEntityType = entityType => {
                context.emit('select', { type: entityType });
            };

            const requestDeleteEntityType = async event => {
                getEntityTypeOccurrenceCount(event.type.id).then(data => {
                    const metadata = {
                        entityCount: data,
                    };
                    showDeleteEntityType(event.type, metadata, _ => {
                        if(currentRoute.name == 'dmdetail' && currentRoute.params.id == event.type.id) {
                            router.push({
                                name: 'dme',
                            });
                        }
                    });
                });
            };

            // DATA
            const state = reactive({
                hoverStates: new Array(props.data.length).fill(false),
                entries: computed(_ => props.data.slice()),
                sortedEntityTypes: computed(_ => {
                    let entries = _cloneDeep(state.entries);
                    entries = entries.map(entry => {
                        entry.translated = translateConcept(entry.thesaurus_url);
                        return entry;
                    });
                    let filtered = entries.filter((entry) => {
                        return entry.translated.toLowerCase().includes(state.order.text.toLowerCase());
                    });

                    if(state.order.type === 'text') {
                        return filtered.toSorted(sortAlphabeticallyBy("translated", state.order.asc));
                    } else {
                        return state.order.asc ? filtered : filtered.reverse();
                    }
                }),
                order: {
                    text: '',
                    asc: true,
                    type: 'number',
                }
            });

            const getControlButtons = (entityType) => {
                let buttons = [];
                buttons.push({
                    icon: 'fas fa-xs fa-edit',
                    title: t('global.edit'),
                    action: async () => {
                        console.log('edit entity type', entityType);
                        showEditEntityType(entityType)
                    }
                });
                buttons.push({
                    icon: 'fas fa-xs fa-clone',
                    title: t('global.duplicate'),
                    action: async () => entityStore.duplicateEntityType(entityType.id),
                });
                buttons.push({
                    icon: 'fas fa-xs fa-trash',
                    title: t('global.delete'),
                    color: 'danger',
                    action: async () => requestDeleteEntityType(entityType),
                });
                return buttons;
            };

            // RETURN
            return {
                t,
                // HELPERS
                translateConcept,
                // LOCAL
                getControlButtons,
                onEnter,
                onLeave,
                activeClasses,
                selectEntityType,
                // STATE
                state,
            };
        },
    };
</script>
