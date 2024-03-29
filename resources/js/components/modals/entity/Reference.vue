<template>
    <vue-final-modal
        class="modal-container modal"
        content-class="sp-modal-content"
        v-model="state.show"
        name="entity-reference-modal"
        :esc-to-close="true"
        :click-to-close="true"
        @closed="closeModal()">
        <div class="sp-modal-content">
            <div class="modal-header">
                <h5 class="modal-title">
                    {{ t('main.entity.references.title') }}
                    -
                    <small class="fw-medium text-muted">
                        {{ translateConcept(state.attribute.thesaurus_url) }}
                    </small>
                </h5>
                <button type="button" class="btn-close" aria-label="Close" data-bs-dismiss="modal" @click="closeModal()">
                </button>
            </div>
            <div class="modal-body my-2">
                <h5>
                    {{ t('main.entity.references.certainty') }}
                </h5>
                <div class="progress mb-3" @click="setCertainty">
                    <div class="progress-bar" role="progressbar" :class="getCertaintyClass(state.certainty)" :aria-valuenow="state.certainty" aria-valuemin="0" aria-valuemax="100" :style="{width: state.certainty+'%'}">
                        <span class="sr-only">
                            {{ state.certainty }}% certainty
                        </span>
                        {{ state.certainty }}%
                    </div>
                </div>
                <div v-dcan="'comments_read'"></div>
                <comment-list
                    v-if="can('comments_read')"
                    :avatar="48"
                    :disabled="!can('comments_write')"
                    :comments="state.comments"
                    :classes="''"
                    :list-classes="''"
                    :resource="state.resourceInfo"
                    :metadata="state.commentMetadata"
                    @added="onUpdateCertainty">
                        <template v-slot:metadata="data">
                            <span class="me-1 small" v-if="Object.keys(data.comment.metadata).length > 0">
                                <span class="badge" :class="getCertaintyClass(data.comment.metadata.certainty_from)">
                                    {{ data.comment.metadata.certainty_from || '???' }}
                                </span>
                                &rarr;
                                <span class="badge" :class="getCertaintyClass(data.comment.metadata.certainty_to)">
                                    {{ data.comment.metadata.certainty_to || '???' }}
                                </span>
                            </span>
                        </template>
                </comment-list>
                <hr />
                <h5>
                    {{ t('main.entity.references.bibliography.title') }}
                </h5>
                <ul class="list-group">
                    <li class="list-group-item d-flex flex-row justify-content-between" v-for="reference in state.references" :key="reference.id">
                        <div class="flex-grow-1">
                            <div v-if="state.editItem.id !== reference.id">
                                <blockquote class="blockquote fs-09">
                                    <p class="text-muted">
                                        {{ reference.description }}
                                    </p>
                                </blockquote>
                                <figcaption class="blockquote-footer fw-medium mb-0">
                                    {{ reference.bibliography.author }} in <cite :title="reference.bibliography.title">
                                        {{ reference.bibliography.title }} ,{{ reference.bibliography.year }}
                                    </cite>
                                </figcaption>
                            </div>
                            <div class="d-flex align-items-center" v-else>
                                <input type="text" class="form-control me-1" v-model="state.editItem.description" />
                                <button type="button" class="btn btn-outline-success btn-sm me-1" @click.prevent="onUpdateReference(state.editItem)">
                                    <i class="fas fa-fw fa-check"></i>
                                </button>
                                <button type="button" class="btn btn-outline-danger btn-sm" @click.prevent="cancelEditReference()">
                                    <i class="fas fa-fw fa-times"></i>
                                </button>
                            </div>
                        </div>
                        <div class="d-flex flex-row small ms-2">
                            <span class="text-muted fw-light">
                                {{ date(reference.updated_at) }}
                            </span>
                            <div class="dropdown ms-1">
                                <span :id="`edit-reference-dropdown-${reference.id}`" class="clickable text-muted" data-bs-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                    <i class="fas fa-fw fa-ellipsis-h"></i>
                                </span>
                                <div class="dropdown-menu" :aria-labelledby="`edit-reference-dropdown-${reference.id}`">
                                    <a class="dropdown-item" href="#" @click.prevent="enableEditReference(reference)">
                                        <i class="fas fa-fw fa-edit text-info"></i> {{ t('global.edit') }}
                                    </a>
                                    <a class="dropdown-item" href="#" @click.prevent="onDeleteReference(reference)">
                                        <i class="fas fa-fw fa-trash text-danger"></i> {{ t('global.delete') }}
                                    </a>
                                </div>
                            </div>
                        </div>
                    </li>
                </ul>
                <h6 class="mt-2">
                    {{ t('main.entity.references.bibliography.add') }}
                </h6>
                <form role="form" @submit.prevent="onAddReference()" v-dcan="'bibliography_read|entity_data_write'">
                    <div class="d-flex flex-row">
                        <div class="flex-grow-1">
                            <multiselect
                                v-model="state.newItem.bibliography"
                                id="bibliography-search"
                                :object="true"
                                :label="'title'"
                                :track-by="'id'"
                                :hideSelected="true"
                                :value-prop="'id'"
                                :mode="'single'"
                                :delay="0"
                                :minChars="0"
                                :resolveOnLoad="true"
                                :filterResults="false"
                                :options="async query => await filterBibliographyList(query)"
                                :searchable="true"
                                :placeholder="t('global.select.placeholder')">
                                <template v-slot:singlelabel="{ value }">
                                    <div class="multiselect-single-label">
                                        <div>
                                            <span class="fw-medium">{{ value.title }}</span>
                                            -
                                            <cite class="small">
                                                {{ value.author }} ({{ value.year }})
                                            </cite>
                                        </div>
                                    </div>
                                </template>
                                <template v-slot:option="{ option }">
                                    <div>
                                        <div>
                                            <span class="fw-medium">{{ option.title }}</span>
                                        </div>
                                        <cite class="small">
                                            {{ option.author }} <span class="fw-light">({{ option.year }})</span>
                                        </cite>
                                    </div>
                                </template>
                            </multiselect>
                        </div>
                        <div class="flex-grow-1 ms-1">
                            <textarea class="form-control" v-model="state.newItem.description" :placeholder="t('main.entity.references.bibliography.comment')"></textarea>
                        </div>
                        <div class="ms-1 mt-auto">
                            <button type="submit" class="btn btn-outline-success btn-sm px-1 py-05" :disabled="state.addReferenceDisabled" :title="t('main.entity.references.bibliography.add_button')">
                                <i class="fas fa-fw fa-plus"></i>
                            </button>
                        </div>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal" @click="closeModal()">
                    <i class="fas fa-fw fa-times"></i> {{ t('global.close') }}
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
    } from 'vue';
    import { useI18n } from 'vue-i18n';
    import router from '@/bootstrap/router.js';
    import store from '@/bootstrap/store.js';

    import {
        can,
        getCertaintyClass,
        translateConcept,
    } from '@/helpers/helpers.js';
    import {
        patchAttribute,
        getAttributeValueComments,
        deleteReferenceFromEntity,
        updateReference,
        addReference,
    } from '@/api.js';
    import {
        date,
    } from '@/helpers/filters.js';

    export default {
        props: {
            entity: {
                required: true,
                type: Object,
            },
        },
        setup(props, context) {
            const { t } = useI18n();
            const {
                entity,
            } = toRefs(props);
            const aid = router.currentRoute.value.params.aid;

            // FETCH
            if(can('comments_read')) {
                getAttributeValueComments(entity.value.id, aid).then(comments => {
                    state.comments = comments;
                });
            }

            // FUNCTIONS
            const setCertainty = event => {
                const maxSize = event.target.parentElement.scrollWidth; // progress bar width in px
                const clickPos = event.layerX; // in px
                const currentValue = state.certainty;
                let value = parseInt(clickPos/maxSize*100);
                const diff = Math.abs(value-currentValue);
                if(diff < 10) {
                    if(value > currentValue) {
                        value = parseInt((value+10)/10)*10;
                    } else {
                        value = parseInt(value/10)*10;
                    }
                } else {
                    value = parseInt((value+5)/10)*10;
                }

                state.certainty = value;
            };
            const onUpdateCertainty = event => {
                let data = {
                    certainty: state.certainty,
                };
                patchAttribute(entity.value.id, aid, data).then(data => {
                    state.comments.push(event.comment);
                    // set startCertainty to new, stored value
                    state.startCertainty = state.certainty;
                });
            };
            const isMatch = (prop, exp) => {
                return !!prop && !!prop.match(exp);
            };
            const filterBibliographyList = async query => {
                if(!query) {
                    return await new Promise(r => r(state.bibliography));
                } else {
                    const exp = new RegExp(query, 'i');
                    return await new Promise(r => r(
                        state.bibliography.filter(entry => {
                            return (
                                isMatch(entry.title, exp) ||
                                isMatch(entry.booktitle, exp) ||
                                isMatch(entry.author, exp) ||
                                isMatch(entry.year, exp) ||
                                isMatch(entry.citekey, exp) ||
                                isMatch(entry.journal, exp)
                            );
                        })
                    ));
                }
            };
            const resetNewItem = _ => {
                state.newItem.bibliography = {};
                state.newItem.description = '';
            };
            const enableEditReference = reference => {
                state.editItem = {
                    ...reference
                };
            };
            const cancelEditReference = _ => {
                state.editItem = {};
            };
            const onAddReference = _ => {
                if(!can('bibliography_read|entity_data_write')) return;
                const data = {
                    bibliography_id: state.newItem.bibliography.id,
                    description: state.newItem.description,
                };
                addReference(entity.value.id, state.attribute.id, state.attribute.thesaurus_url, data).then(data => {
                    resetNewItem();
                });
            };
            const onDeleteReference = reference => {
                if(!can('bibliography_read|entity_data_write')) return;
                const id = reference.id;
                deleteReferenceFromEntity(reference.id, entity.value.id, state.attribute.thesaurus_url).then(data => {
                    cancelEditReference();
                });
            };
            const onUpdateReference = editedReference => {
                if(!can('bibliography_read|entity_data_write')) return;
                const ref = state.references.find(r => r.id == editedReference.id);
                if(ref.description == editedReference.description) {
                    cancelEditReference();
                    return;
                }
                const data = {
                    description: editedReference.description
                };
                updateReference(ref.id, entity.value.id, state.attribute.thesaurus_url, data).then(data => {
                    cancelEditReference();
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
                addReferenceDisabled: computed(_ => {
                    return (
                        (!!state.newItem.bibliography && !state.newItem.bibliography.id) ||
                        state.newItem.description.length == 0
                    );
                }),
                editItem: {},
                attribute: entity.value.data[aid].attribute,
                references: computed(_ => entity.value.references[state.attribute.thesaurus_url]),
                bibliography: computed(_ => store.getters.bibliography),
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
                date,
                // PROPS
                // LOCAL
                setCertainty,
                onUpdateCertainty,
                filterBibliographyList,
                enableEditReference,
                cancelEditReference,
                onAddReference,
                onDeleteReference,
                onUpdateReference,
                closeModal,
                // STATE
                state,
            }

        },
    }
</script>
