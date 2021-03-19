<template>
    <vue-final-modal
        classes="modal-container"
        content-class="sp-modal-content sp-modal-content-sm"
        v-model="state.show"
        name="delete-entity-type-modal">
        <div class="modal-header">
            <h5 class="modal-title">
                {{ t('main.datamodel.attribute.modal.new.title') }}
            </h5>
            <button type="button" class="btn-close" aria-label="Close" data-bs-dismiss="modal" @click="closeModal()">
            </button>
        </div>
        <div class="modal-body">
            <attribute-template
                :type="'default'"
                :external="state.formId"
                @created="add"
                @selected-type="checkAttributeType"
                @validation="checkValidation"
            >
            </attribute-template>
            <div v-if="needsColumns">
                <h5>
                    {{ t('global.column', 2) }}
                    <span class="badge">
                        {{ columns.length }}
                    </span>
                </h5>
                <attribute-template
                    :type="'table'"
                    @created="addColumn"
                >
                </attribute-template>
            </div>
        </div>
        <div class="modal-footer">
            <button type="submit" class="btn btn-outline-success" :form="state.formId" :disabled="!state.validated">
                    <i class="fas fa-fw fa-plus"></i> {{ t('global.add') }}
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
    } from 'vue';
    import { useI18n } from 'vue-i18n';

    import store from '../../../bootstrap/store.js';

    import AttributeTemplate from '../../AttributeTemplate.vue';

    export default {
        components: {
            'attribute-template': AttributeTemplate,
        },
        props: {
        },
        emits: ['closing', 'confirm'],
        setup(props, context) {
            const { t } = useI18n();

            // FUNCTIONS
            const add = _ => {
                state.show = false;
                context.emit('confirm', null);
            };
            const closeModal = _ => {
                state.show = false;
                context.emit('closing', false);
            };

            // DATA
            const state = reactive({
                show: false,
                formId: 'new-attribute-form-external-submit',
            });

            // ON MOUNTED
            onMounted(_ => {
                state.show = true;
            });

            // RETURN
            return {
                t,
                // HELPERS
                // PROPS
                // LOCAL
                add,
                closeModal,
                // STATE
                state,
            }
        },
    }
</script>