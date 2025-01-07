<template>
    <ConfirmModal
        :title="t('main.datamodel.entity.modal.new.title')"
        name="add-entity-type-modal"
    >
        <form
            id="newEntityTypeForm"
            name="newEntityTypeForm"
            role="form"
            @submit.prevent="add()"
        >
            <div class="mb-3">
                <label
                    class="col-form-label col-md-3"
                    for="name"
                >
                    {{ t('global.label') }}:
                </label>
                <div class="col-md-9">
                    <simple-search
                        :endpoint="searchLabel"
                        :key-fn="getConceptLabel"
                        @selected="labelSelected"
                    />
                </div>
            </div>
            <div class="form-check form-switch mb-3">
                <input
                    id="isRoot"
                    v-model="state.entityType.is_root"
                    type="checkbox"
                    class="form-check-input"
                >
                <label
                    class="form-check-label"
                    for="isRoot"
                >
                    {{ t('main.datamodel.detail.properties.top_level') }}
                </label>
            </div>
            <div class="mb-3">
                <label
                    class="col-form-label col-md-3"
                    for="name"
                >
                    {{ t('global.geometry_type') }}:
                </label>
                <div class="col-md-9">
                    <multiselect
                        v-model="state.entityType.geometryType"
                        :classes="multiselectResetClasslist"
                        :name="'geometry-type-selection'"
                        :object="true"
                        :label="'label'"
                        :track-by="'key'"
                        :value-prop="'key'"
                        :mode="'single'"
                        :options="state.availableGeometryTypes"
                        :placeholder="t('global.select.placeholder')"
                    />
                </div>
            </div>
        </form>
    </ConfirmModal>
</template>

<script>
    import {
        computed,
        reactive,
    } from 'vue';
    import { useI18n } from 'vue-i18n';

    import store from '@/bootstrap/store.js';

    import {
        searchLabel,
    } from '@/api.js';

    import {
        getConceptLabel,
        multiselectResetClasslist,
    } from '@/helpers/helpers.js';
    import ConfirmModal from '../ConfirmModal.vue';

    export default {
        components: {
            ConfirmModal
        },
        emits: ['closing', 'confirm'],
        setup(props, context) {
            const { t } = useI18n();

            // FUNCTIONS
            const add = _ => {
                if(state.dataMissing) {
                    return;
                }
                context.emit('confirm', state.entityType);
            };
            const closeModal = _ => {
                context.emit('closing', false);
            };
            const labelSelected = label => {
                state.entityType.label = label;
            };

            // DATA
            const state = reactive({
                entityType: {
                    is_root: false,
                    geometryType: null,
                    label: null,
                },
                availableGeometryTypes: computed(_ => store.getters.geometryTypes),
                dataMissing: computed(_ => !state.entityType.label),
            });

            // RETURN
            return {
                t,
                // HELPERS
                searchLabel,
                getConceptLabel,
                multiselectResetClasslist,
                // PROPS
                // LOCAL
                add,
                closeModal,
                labelSelected,
                // STATE
                state,
            };
        },
    };
</script>