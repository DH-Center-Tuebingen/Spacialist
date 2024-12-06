<template>
    <vue-final-modal
        class="modal-container modal"
        name="multi-edit-attribute-modal">
        <div class="sp-modal-content sp-modal-content-xl">
            <div class="modal-header">
                <h5 class="modal-title">
                    {{
                        t('main.entity.modals.multiedit.title')
                    }}
                </h5>
                <button
                    type="button"
                    class="btn-close"
                    aria-label="Close"
                    data-bs-dismiss="modal"
                    @click="closeModal()"
                />
            </div>
            <div class="modal-body">
                <attribute-list
                    v-if="state.attributesInitialized"
                    :ref="el => listRef = el"
                    v-dcan="'entity_data_read'"
                    class="pt-2 h-100 overflow-y-auto overflow-x-hidden"
                    :options="{'ignore_metadata': true}"
                    :attributes="state.sortedAttributes"
                    :values="state.defaultValues"
                    :selections="state.attributeSelections"
                    :disable-drag="true"
                    @dirty="setDirtyState"
                />
            </div>
            <div class="modal-footer">
                <button
                    type="submit"
                    class="btn btn-outline-success"
                    :disabled="!state.isDirty"
                    @click="confirm()"
                >
                    <i class="fas fa-fw fa-save" /> {{ t('global.save') }}
                </button>
                <button
                    type="button"
                    class="btn btn-outline-secondary"
                    data-bs-dismiss="modal"
                    @click="closeModal()"
                >
                    <i class="fas fa-fw fa-times" /> {{ t('global.cancel') }}
                </button>
            </div>
        </div>
    </vue-final-modal>
</template>

<script>
    import {
        onMounted,
        reactive,
        ref,
        toRefs,
    } from 'vue';

    import { useI18n } from 'vue-i18n';

    import useAttributeStore from '@/bootstrap/stores/attribute.js';

    import {
        can,
        getInitialAttributeValue,
    } from '@/helpers/helpers.js';

    export default {
        props: {
            attributes: {
                required: true,
                type: Array,
            },
            entityIds: {
                required: true,
                type: Array,
            },
        },
        emits: ['closing', 'confirm'],
        setup(props, context) {
            const { t } = useI18n();
            const attributeStore = useAttributeStore();
            const {
                attributes,
            } = toRefs(props);

            // FUNCTIONS
            const setDirtyState = e => {
                state.isDirty = e.dirty && e.valid;
            };
            const confirm = _ => {
                if(!can('entity_data_write')) return;

                const values =  listRef.value.getDirtyValues();

                context.emit('confirm', {
                    values: values,
                });
            };
            const closeModal = _ => {
                context.emit('closing', false);
            };

            // DATA
            const listRef = ref({});
            const state = reactive({
                attributesInitialized: false,
                isDirty: false,
                sortedAttributes: attributes.value.filter(a => a.datatype != 'system-separator').sort((a, b) => a.id > b.id),
                defaultValues: {},
                attributeSelections: null,
            });

            // ON MOUNTED
            onMounted(_ => {
                for(let i=0; i<state.sortedAttributes.length; i++) {
                    const curr = state.sortedAttributes[i];
                    state.defaultValues[curr.id] = {
                        value: getInitialAttributeValue(curr),
                    };
                }
                state.attributeSelections = attributeStore.getAttributeSelections(state.sortedAttributes);
                state.attributesInitialized = true;
            });

            // RETURN
            return {
                t,
                // HELPERS
                // PROPS
                // LOCAL
                setDirtyState,
                confirm,
                closeModal,
                // STATE
                listRef,
                state,
            };
        },
    };
</script>