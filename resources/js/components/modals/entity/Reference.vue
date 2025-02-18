<template>
    <vue-final-modal
        v-model="state.show"
        class="modal-container modal"
        content-class="sp-modal-content"
        name="entity-reference-modal"
        :esc-to-close="true"
        :click-to-close="true"
        @closed="closeModal()"
    >
        <div class="sp-modal-content">
            <div class="modal-header">
                <h5 class="modal-title">
                    {{ t('main.entity.references.title') }}
                    -
                    <small class="fw-medium text-muted">
                        {{ translateConcept(state.attribute.thesaurus_url) }}
                    </small>
                </h5>
                <button
                    type="button"
                    class="btn-close"
                    aria-label="Close"
                    data-bs-dismiss="modal"
                    @click="closeModal()"
                />
            </div>
            <div class="modal-body my-2">
                <h5>
                    {{ t('main.entity.references.certainty') }}
                </h5>
                <div
                    class="progress mb-3"
                    @click="setCertainty"
                >
                    <div
                        class="progress-bar"
                        role="progressbar"
                        :class="getCertaintyClass(state.certainty)"
                        :aria-valuenow="state.certainty"
                        aria-valuemin="0"
                        aria-valuemax="100"
                        :style="{ width: state.certainty + '%' }"
                    >
                        <span class="sr-only">
                            {{ state.certainty }}% certainty
                        </span>
                        {{ state.certainty }}%
                    </div>
                </div>
                <div v-dcan="'comments_read'" />
                <comment-list
                    v-if="can('comments_read')"
                    :avatar="48"
                    :disabled="!can('comments_write')"
                    :comments="state.comments"
                    :classes="''"
                    :list-classes="''"
                    :resource="state.resourceInfo"
                    :metadata="state.commentMetadata"
                    @added="onUpdateCertainty"
                >
                    <template #metadata="data">
                        <span
                            v-if="data?.comment?.metadata && Object.keys(data.comment.metadata).length > 0"
                            class="me-1 small"
                        >
                            <span
                                class="badge"
                                :class="getCertaintyClass(data.comment.metadata.certainty_from)"
                            >
                                {{ data.comment.metadata.certainty_from || '???' }}
                            </span>
                            &rarr;
                            <span
                                class="badge"
                                :class="getCertaintyClass(data.comment.metadata.certainty_to)"
                            >
                                {{ data.comment.metadata.certainty_to || '???' }}
                            </span>
                        </span>
                    </template>
                </comment-list>
                <hr>
                <h5>
                    {{ t('main.entity.references.bibliography.title') }}
                </h5>
                <ul class="list-group">
                    <li
                        v-for="reference in state.references"
                        :key="reference.id"
                        class="list-group-item d-flex flex-row justify-content-between pt-0"
                    >
                        <EditableQuotation
                            class="flex-fill"
                            :value="reference"
                            @update="onUpdateReference"
                            @delete="onDeleteReference"
                        />
                    </li>
                </ul>
                <h6 class="mt-2">
                    {{ t('main.entity.references.bibliography.add') }}
                </h6>
                <ReferenceForm
                    ref="referenceFormRef"
                    @add="onAddReference"
                />
            </div>
            <div class="modal-footer">
                <button
                    type="button"
                    class="btn btn-outline-secondary"
                    data-bs-dismiss="modal"
                    @click="closeModal()"
                >
                    <i class="fas fa-fw fa-times" /> {{ t('global.close') }}
                </button>
            </div>
        </div>
    </vue-final-modal>
</template>

<script>
    import {
        computed,
        onMounted,
        reactive,
        toRefs,
        ref,
    } from 'vue';

    import { useI18n } from 'vue-i18n';
    import router from '%router';
    import useAttributeStore from '@/bootstrap/stores/attribute.js';
    import useEntityStore from '@/bootstrap/stores/entity.js';
    import useReferenceStore from '@/bootstrap/stores/reference.js';

    import {
        can,
        throwError,
        getCertaintyClass,
        translateConcept,
    } from '@/helpers/helpers.js';

    // TODO: This should be done in the store
    import {
        getAttributeValueComments,
    } from '@/api.js';

    import EditableQuotation from '@/components/bibliography/EditableQuotation.vue';
    import ReferenceForm from '@/components/bibliography/ReferenceForm.vue';

    export default {
        components: {
            EditableQuotation,
            ReferenceForm,
        },
        props: {
            entity: {
                required: true,
                type: Object,
            },
        },
        setup(props, context) {
            const { t } = useI18n();
            const attributeStore = useAttributeStore();
            const entityStore = useEntityStore();
            const referenceStore = useReferenceStore();
            const {
                entity,
            } = toRefs(props);
            const aid = router.currentRoute.value.params.aid;
            const referenceFormRef = ref(null);

            // FETCH
            if(can('comments_read')) {
                // TODO: This should be done in the store
                getAttributeValueComments(entity.value.id, aid).then(comments => {
                    state.comments = comments;
                });
            }

            // FUNCTIONS
            const setCertainty = event => {
                const maxSize = event.target.parentElement.scrollWidth; // progress bar width in px
                const clickPos = event.layerX; // in px
                const finalPos = Math.max(0, Math.min(clickPos, maxSize)); // clamp cursor pos to progress bar size

                const currentValue = state.certainty;
                let value = parseInt(finalPos / maxSize * 100);
                const diff = Math.abs(value - currentValue);
                if(diff < 10) {
                    if(value > currentValue) {
                        value = parseInt((value + 10) / 10) * 10;
                    } else {
                        value = parseInt(value / 10) * 10;
                    }
                } else {
                    value = parseInt((value + 5) / 10) * 10;
                }

                state.certainty = value;
            };
            const onUpdateCertainty = async event => {
                let data = {
                    certainty: state.certainty,
                };
                try {
                    await entityStore.patchAttribute(entity.value.id, aid, data);
                    state.comments.push(event.comment);
                    // set startCertainty to new, stored value
                    state.startCertainty = state.certainty;
                } catch(e) {
                    // Error will be handled elsewhere ...
                    console.error(e);
                }
            };

            const onAddReference = async data => {
                if(!can('bibliography_read|entity_data_write')) return;
                try {
                    await referenceStore.add(entity.value.id, state.attribute.id, state.attribute.thesaurus_url, data);
                    referenceFormRef.value.reset();
                } catch(e) {
                    // Error will be handled elsewhere ...
                    console.error(e);
                }
            };
            const onDeleteReference = reference => {
                if(!can('bibliography_read|entity_data_write')) return;
                referenceStore.delete(entity.value.id, state.attribute.thesaurus_url, reference);
            };
            const onUpdateReference = (updatedReference, successCallback) => {
                if(!can('bibliography_read|entity_data_write')) return;
                const ref = state.references.find(r => r.id == updatedReference.id);
                if(ref.description == updatedReference.description) {
                    // We can return early here, because the reference was not changed
                    successCallback(true);
                    return;
                }

                referenceStore.update(entity.value.id, state.attribute.thesaurus_url, updatedReference).then(_ => {
                    successCallback(true);
                }).catch(e => {
                    successCallback(false);
                    throwError(e);
                });
            };
            const closeModal = _ => {
                state.show = false;
                router.push({
                    name: 'entitydetail',
                    params: {
                        id: entity.value.id,
                    },
                    query: router.currentRoute.value.query,
                });
            };

            // DATA
            const state = reactive({
                show: false,
                newItem: {
                    bibliography: {},
                    description: '',
                },
                attribute: attributeStore.getAttribute(aid),
                references: computed(_ => entity.value.references[state.attribute.thesaurus_url]),
                startCertainty: entity.value.data[aid].certainty,
                certainty: entity.value.data[aid].certainty,
                comments: [],
                comments_count: entity.value.data[aid].comments_count,
                resourceInfo: computed(_ => {
                    return {
                        id: entity.value.data[aid].id,
                        type: 'attribute_value',
                    };
                }),
                commentMetadata: computed(_ => {
                    if(state.startCertainty == state.certainty) {
                        return {};
                    } else {
                        return {
                            certainty_from: state.startCertainty,
                            certainty_to: state.certainty,
                        };
                    }
                }),
            });

            // ON MOUNTED
            onMounted(_ => {
                state.show = true;
            });

            // RETURN
            return {
                t,
                // HELPERS
                can,
                getCertaintyClass,
                translateConcept,
                // PROPS
                // LOCAL
                setCertainty,
                onUpdateCertainty,
                onAddReference,
                onDeleteReference,
                onUpdateReference,
                closeModal,
                // STATE
                referenceFormRef,
                state,
            };

        },
    };
</script>
