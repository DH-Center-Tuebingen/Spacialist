<template>
    <vue-final-modal
        classes="modal-container"
        content-class="sp-modal-content sp-modal-content-sm"
        v-model="state.show"
        name="edit-attribute-modal">
        <div class="modal-header">
            <h5 class="modal-title">
                {{
                    t('main.entity.modals.edit.title_attribute', {
                        name: translateConcept(state.attribute.thesaurus_url)
                    })
                }}
            </h5>
            <button type="button" class="btn-close" aria-label="Close" data-bs-dismiss="modal" @click="closeModal()">
            </button>
        </div>
        <div class="modal-body nonscrollable">
            <div class="mb-3 row">
                <label class="col-form-label text-end col-md-2">
                    {{ t('global.label') }}:
                </label>
                <div class="col-md-10">
                    <input type="text" class="form-control" :value="translateConcept(state.attribute.thesaurus_url)" disabled />
                </div>
            </div>
            <div class="mb-3 row">
                <label class="col-form-label text-end col-md-2">
                    {{ t('global.type') }}:
                </label>
                <div class="col-md-10">
                    <input type="text" class="form-control" :value="t(`global.attributes.${state.attribute.datatype}`)" disabled />
                </div>
            </div>
        </div>
        <div class="modal-footer">
            <button type="submit" class="btn btn-outline-success" @click="confirmEdit()">
                <i class="fas fa-fw fa-save"></i> {{ t('global.update') }}
            </button>
            <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal" @click="closeModal()">
                <i class="fas fa-fw fa-times"></i> {{ t('global.cancel') }}
            </button>
        </div>
    </vue-final-modal>

        <!-- <form id="editEntityAttributeForm" name="editEntityAttributeForm" role="form" v-on:submit.prevent="editEntityAttribute(modalSelectedAttribute, selectedDependency)">
                        <div class="form-group row">
                            <label class="col-form-label col-md-3">
                                {{ t('global.label') }}:
                            </label>
                            <div class="col-md-9">
                                <input type="text" class="form-control-plaintext" :value="translateConcept(modalSelectedAttribute.thesaurus_url)" readonly />
                            </div>
                        </div>
                        <div class="form-group row">
                            <label class="col-form-label col-md-3">
                                {{ t('global.type') }}:
                            </label>
                            <div class="col-md-9">
                                <input type="text" class="form-control-plaintext" :value="t(`global.attributes.${modalSelectedAttribute.datatype}`)" readonly />
                            </div>
                        </div>
                        <div class="form-group row">
                            <label class="col-form-label col-md-3">
                                {{ t('global.depends-on') }}:
                            </label>
                            <div class="col-md-9">
                                <multiselect
                                    class="mb-2"
                                    label="thesaurus_url"
                                    track-by="id"
                                    v-model="selectedDependency.attribute"
                                    :allowEmpty="true"
                                    :closeOnSelect="true"
                                    :customLabel="translateLabel"
                                    :hideSelected="false"
                                    :multiple="false"
                                    :options="depends.attributes"
                                    :placeholder="t('global.select.placeholder')"
                                    @input="dependencyAttributeSelected">
                                </multiselect>
                                <multiselect
                                    class="mb-2"
                                    label="id"
                                    track-by="id"
                                    v-if="selectedDependency.attribute && selectedDependency.attribute.id"
                                    v-model="selectedDependency.operator"
                                    :allowEmpty="true"
                                    :closeOnSelect="true"
                                    :hideSelected="false"
                                    :multiple="false"
                                    :options="dependencyOperators"
                                    :placeholder="t('global.select.placeholder')">
                                </multiselect>
                                <div v-if="selectedDependency.attribute && selectedDependency.attribute.id">
                                    <input type="checkbox" class="form-check-input" v-if="dependencyType == 'boolean'" v-model="selectedDependency.value" />
                                    <input type="number" class="form-control" step="1" v-else-if="dependencyType == 'integer'" v-model="selectedDependency.value" />
                                    <input type="number" class="form-control" step="0.01" v-else-if="dependencyType == 'double'" v-model="selectedDependency.value" />
                                    <multiselect
                                        label="concept_url"
                                        track-by="id"
                                        v-else-if="dependencyType == 'select'"
                                        v-model="selectedDependency.value"
                                        :allowEmpty="true"
                                        :closeOnSelect="true"
                                        :customLabel="translateLabel"
                                        :hideSelected="false"
                                        :multiple="false"
                                        :options="depends.values"
                                        :placeholder="t('global.select.placeholder')">
                                    </multiselect>
                                    <input type="text" class="form-control" v-else v-model="selectedDependency.value" />
                                </div>
                            </div>
                        </div>
                    </form> -->
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
        getAttribute,
        translateConcept,
    } from '../../../helpers/helpers.js';

    export default {
        props: {
            attributeId: {
                required: true,
                type: Number,
            },
        },
        emits: ['closing', 'confirm'],
        setup(props, context) {
            const { t } = useI18n();
            const {
                attributeId,
            } = toRefs(props);

            // FUNCTIONS
            const confirmEdit = _ => {
                state.show = false;
                context.emit('confirm', false);
            };
            const closeModal = _ => {
                state.show = false;
                context.emit('closing', false);
            };

            // DATA
            const state = reactive({
                show: false,
                attribute: computed(_ => getAttribute(attributeId.value)),
            });

            // ON MOUNTED
            onMounted(_ => {
                state.show = true;
            });

            // RETURN
            return {
                t,
                // HELPERS
                translateConcept,
                // PROPS
                // LOCAL
                confirmEdit,
                closeModal,
                // STATE
                state,
            }
        },
    }
</script>