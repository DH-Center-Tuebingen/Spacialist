<template>
    <vue-final-modal
        classes="modal-container modal"
        content-class="sp-modal-content sp-modal-content-xl"
        v-model="state.show"
        name="multi-edit-attribute-modal">
        <div class="modal-header">
            <h5 class="modal-title">
                {{
                    t('main.entity.modals.multiedit.title')
                }}
            </h5>
            <button type="button" class="btn-close" aria-label="Close" data-bs-dismiss="modal" @click="closeModal()">
            </button>
        </div>
        <div class="modal-body nonscrollable">
            <attribute-list
                class="pt-2 h-100 scroll-y-auto scroll-x-hidden"
                v-if="state.attributesInitialized"
                v-dcan="'entity_data_read'"
                :ref="el => listRef = el"
                :attributes="state.sortedAttributes"
                :values="state.defaultValues"
                :selections="state.attributeSelections"
                :disable-drag="true"
                @dirty="setDirtyState"
            />
        </div>
        <div class="modal-footer">
            <button type="submit" class="btn btn-outline-success" :disabled="!state.isDirty" @click="confirm()">
                <i class="fas fa-fw fa-save"></i> {{ t('global.save') }}
            </button>
            <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal" @click="closeModal()">
                <i class="fas fa-fw fa-times"></i> {{ t('global.cancel') }}
            </button>
        </div>
    </vue-final-modal>
</template>

<script>
    import {
        computed,
        onMounted,
        reactive,
        ref,
        toRefs,
    } from 'vue';
    import { useI18n } from 'vue-i18n';

    import store from "@/bootstrap/store.js";

    import {
        can,
        getAttributeSelections,
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

                state.show = false;
                context.emit('confirm', {
                    values: values,
                });
            };
            const closeModal = _ => {
                state.show = false;
                context.emit('closing', false);
            };

            // DATA
            const listRef = ref({});
            const state = reactive({
                show: false,
                attributesInitialized: false,
                isDirty: false,
                sortedAttributes: attributes.value.sort((a, b) => a.id > b.id),
                defaultValues: {},
                attributeSelections: null,
            });

            // ON MOUNTED
            onMounted(_ => {
                state.show = true;
                for(let i=0; i<state.sortedAttributes.length; i++) {
                    const curr = state.sortedAttributes[i];
                    state.defaultValues[curr.id] = {
                        value: getInitialAttributeValue(curr),
                    };
                }
                state.attributeSelections = getAttributeSelections(state.sortedAttributes);
                state.attributesInitialized = true;
            });

            // RETURN
            return {
                t,
                // HELPERS
                // PROPS
                attributes,
                // LOCAL
                setDirtyState,
                confirm,
                closeModal,
                // STATE
                listRef,
                state,
            }
        },
    }
</script>