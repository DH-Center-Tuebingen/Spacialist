<template>
    <vue-final-modal
        classes="modal-container"
        content-class="sp-modal-content sp-modal-content-sm"
        v-model="state.show"
        name="delete-entity-type-modal">
        <div class="modal-header">
            <h5 class="modal-title">
                {{ t('main.datamodel.entity.modal.new.title') }}
            </h5>
            <button type="button" class="btn-close" aria-label="Close" data-bs-dismiss="modal" @click="closeModal()">
            </button>
        </div>
        <div class="modal-body">
            <form name="newEntityTypeForm" id="newEntityTypeForm" role="form" v-on:submit.prevent="createEntityType(newEntityType)">
                <div class="form-group">
                    <label class="col-form-label col-md-3" for="name">
                        {{ t('global.label') }}:
                    </label>
                    <div class="col-md-9">
                        <!-- <label-search
                            :on-select="newEntityTypeSearchResultSelected"
                        ></label-search> -->
                    </div>
                </div>
                <div class="form-check">
                    <input type="checkbox" class="form-check-input" id="isRoot" v-model="state.entityType.is_root" />
                    <label class="form-check-label" for="isRoot">
                        {{ t('main.datamodel.detail.properties.top-level') }}
                    </label>
                </div>
                <div class="form-group">
                    <label class="col-form-label col-md-3" for="name">
                        {{ t('global.geometry-type') }}:
                    </label>
                    <div class="col-md-9">
                        <multiselect
                            v-model="state.entityType.geometryType"
                            :name="'geometry-type-selection'"
                            :object="true"
                            :label="'label'"
                            :track-by="'label'"
                            :valueProp="'id'"
                            :mode="'single'"
                            :options="state.availableGeometryTypes"
                            :placeholder="t('global.select.placeholder')">
                        </multiselect>
                    </div>
                </div>
            </form>
        </div>
        <div class="modal-footer">
            <button type="submit" class="btn btn-outline-success" @click="add()" form="newEntityTypeForm" :disabled="state.dataMissing">
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

    export default {
        props: {
        },
        emits: ['closing', 'confirm'],
        setup(props, context) {
            const { t } = useI18n();

            // FUNCTIONS
            const add = _ => {
                state.show = false;
                context.emit('confirm', state.entityType);
            };
            const closeModal = _ => {
                state.show = false;
                context.emit('closing', false);
            };

            // DATA
            const state = reactive({
                show: false,
                entityType: {
                    is_root: false,
                    geometryType: null,
                    label: null,
                },
                availableGeometryTypes: computed(_ => store.getters.geometryTypes),
                dataMissing: computed(_ => !state.entityType.label),
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