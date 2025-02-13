<template>
    <div class="tab reference-tab d-flex flex-column h-100">
        <div class="flex-fill overflow-y-auto">
            <p
                v-if="!state.hasReferences"
                class="alert alert-info mt-2"
            >
                {{ t('main.entity.references.empty') }}
            </p>
            <div
                v-if="state.hasEntityReferences"
                class="reference-group mb-1"
            >
                <h5 class="mb-2 fw-medium">
                    {{ t('main.entity.references.general') }}
                </h5>
                <div class="list-group">
                    <div
                        v-for="(reference, i) in state.entityReferences"
                        :key="i"
                        class="list-group-item pt-0"
                    >
                        <EditableQuotation
                            :value="reference"
                            @update="updateEntityReference"
                            @delete="() => deleteEntityReference(reference)"
                        />
                    </div>
                </div>
            </div>
            <hr
                v-if="state.hasEntityReferences && state.hasAttributeReferences"
            >
            <template v-if="state.hasAttributeReferences">
                <template
                    v-for="(referenceGroup, key) in state.attributeReferences"
                    :key="key"
                >
                    <div
                        v-if="referenceGroup.length > 0"
                        class="reference-group"
                    >
                        <h5 class="mb-2 fw-medium">
                            <a
                                href="#"
                                class="text-decoration-none"
                                @click.prevent="showMetadataForReferenceGroup(referenceGroup)"
                            >
                                {{ translateConcept(key) }}
                            </a>
                        </h5>
                        <div class="list-group">
                            <div
                                v-for="(reference, i) in referenceGroup"
                                :key="i"
                                class="list-group-item pt-0"
                            >
                                <header class="text-end">
                                    <span class="text-muted fw-light small">
                                        {{ date(reference.updated_at) }}
                                    </span>
                                </header>
                                <Quotation :value="reference" />
                            </div>
                        </div>
                    </div>
                </template>
            </template>
        </div>
        <hr>
        <h6 class="mb-2">
            {{ t('main.entity.references.bibliography.add') }}
        </h6>
        <ReferenceForm
            ref="referenceForm"
            @add="addEntityReference"
        />
    </div>
</template>

<script>
    import {
        computed,
        reactive,
        ref,
    } from 'vue';

    import { useI18n } from 'vue-i18n';
    import { useToast } from '@/plugins/toast.js';

    import {
        translateConcept,
    } from '@/helpers/helpers.js';

    import {
        canShowReferenceModal,
    } from '@/helpers/modal.js';

    import {
        date,
    } from '@/helpers/filters.js';

    import useEntityStore from '@/bootstrap/stores/entity.js';

    import EditableQuotation from '@/components/bibliography/EditableQuotation.vue';
    import Quotation from '@/components/bibliography/Quotation.vue';
    import ReferenceForm from '@/components/bibliography/ReferenceForm.vue';

    import {
        router,
    } from '@/bootstrap/router.js';

    export default {
        components: {
            ReferenceForm,
            Quotation,
            EditableQuotation,
        },
        setup() {

            const entityStore = useEntityStore();
            const toast = useToast();
            const referenceForm = ref(null);

            const addEntityReference = async data => {
                try {
                    await entityStore.addReference(state.entity.id, null, null, data);
                    referenceForm.value.reset();
                } catch(e) {
                    // Error is handled by http class.
                    console.error(e);
                }
            };
            const updateEntityReference = async (reference, callback) => {
                try {
                    await entityStore.updateReference(reference.id, state.entity.id, null, reference);
                    callback(true);
                } catch(e) {
                    // Error is handled by http class.
                    console.error(e);
                    callback(false);
                }
            };
            const deleteEntityReference = async reference => {
                try {
                    await entityStore.deleteReference(reference.id, state.entity.id);
                } catch(e) {
                    // Error is handled by http class.
                    console.error(e);
                }
            };
            const showMetadataForReferenceGroup = referenceGroup => {
                if(!referenceGroup) return;
                if(!state.entity) return;
                const aid = referenceGroup[0].attribute_id;

                const canOpen = canShowReferenceModal(aid);
                if(canOpen) {
                    router.push({
                        append: true,
                        name: 'entityrefs',
                        query: currentRoute.query,
                        params: {
                            aid: aid,
                        },
                    });
                } else {
                    const msg = t('main.entity.references.toasts.cannot_edit_metadata.msg');
                    toast.$toast(msg, '', {
                        duration: 2500,
                        autohide: true,
                        channel: 'warning',
                        icon: true,
                        simple: true,
                    });
                }
            };

            const state = reactive({
                entity: computed(_ => entityStore.selectedEntity),
                hasEntityReferences: computed(_ => {
                    const isNotSet = !state.entity.references;
                    if(isNotSet) return false;

                    const isEmpty = !Object.keys(state.entity.references).length > 0;
                    if(isEmpty) return false;
                    return state.entity.references.on_entity?.length > 0;
                }),
                hasAttributeReferences: computed(_ => {
                    const isNotSet = !state.entity.references;
                    if(isNotSet) return false;
                    const {
                        on_entity,
                        ...refs
                    } = state.entity.references;
                    const isEmpty = !Object.keys(refs).length > 0;
                    if(isEmpty) return false;
                    return Object.values(refs).some(v => v.length > 0);
                }),
                hasReferences: computed(_ => state.hasEntityReferences || state.hasAttributeReferences),
                entityReferences: computed(_ => state.hasEntityReferences ? state.entity.references.on_entity : []),
                attributeReferences: computed(_ => {
                    if(state.hasAttributeReferences) {
                        const {
                            on_entity,
                            ...refs
                        } = state.entity.references;
                        return refs;
                    } else {
                        return {};
                    }
                }),
            });

            return {
                t: useI18n().t,
                date,
                state,
                addEntityReference,
                updateEntityReference,
                deleteEntityReference,
                showMetadataForReferenceGroup,
                translateConcept,
                referenceForm,
            };
        }
    };
</script>