<template>
    <vue-final-modal
        class="modal-container modal"
        content-class="sp-modal-content sp-modal-content-sm"
        name="edit-attribute-modal"
    >
        <div class="sp-modal-content sp-modal-content-sm">
            <div class="modal-header">
                <h5 class="modal-title">
                    {{
                        t('main.entity.modals.edit.title_attribute', {
                            name: t('global.attributes.system-separator')
                        })
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
            <div class="modal-body nonscrollable">
                <div class="row">
                    <label class="col-form-label col-3">
                        {{ t('global.text') }}:
                    </label>
                    <div class="col">
                        <simple-search
                            :default-value="state.concept"
                            :endpoint="searchLabel"
                            :key-fn="getConceptLabel"
                            @selected="handleSeparatorRename"
                        />
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button
                    type="submit"
                    class="btn btn-outline-success"
                    :disabled="!state.isValid"
                    @click="confirmEdit()"
                >
                    <i class="fas fa-fw fa-save" /> {{ t('global.update') }}
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
        computed,
        reactive,
        toRefs,
    } from 'vue';
    import { useI18n } from 'vue-i18n';

    import {
        searchLabel,
    } from '@/api.js';

    import {
        getConceptLabel,
    } from '@/helpers/helpers.js';

    export default {
        props: {
            metadata: {
                required: true,
                type: Object,
            },
        },
        emits: ['closing', 'confirm'],
        setup(props, context) {
            const { t } = useI18n();
            const {
                metadata,
            } = toRefs(props);

            // FUNCTIONS
            const confirmEdit = _ => {
                context.emit('confirm', {title: state.concept_url});
            };
            const closeModal = _ => {
                context.emit('closing', false);
            };
            const handleSeparatorRename = label => {
                if(label === null){
                    state.concept = null;
                } else {
                    state.concept = label;
                }
            };

            // DATA
            const state = reactive({
                concept: null,
                concept_url: computed(_ => {
                    return state.concept ? state.concept.concept_url : null;
                }),
                isValid: computed(_ => {
                    return Boolean(state.concept_url);
                }),
            });

            // RETURN
            return {
                t,
                // HELPERS
                searchLabel,
                getConceptLabel,
                // PROPS
                // LOCAL
                confirmEdit,
                closeModal,
                handleSeparatorRename,
                // STATE
                state,
            };
        },
    };
</script>