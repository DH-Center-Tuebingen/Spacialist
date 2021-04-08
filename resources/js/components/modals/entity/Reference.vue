<template>
    <vue-final-modal
        classes="modal-container"
        content-class="sp-modal-content"
        v-model="state.show"
        name="entity-reference-modal"
        :esc-to-close="true"
        :click-to-close="true"
        @closed="closeModal()">
        <div class="modal-header">
            <h5 class="modal-title">
                {{ t('main.entity.references.title') }}
            </h5>
            <button type="button" class="btn-close" aria-label="Close" data-bs-dismiss="modal" @click="closeModal()">
            </button>
        </div>
        <div class="modal-body">
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
            <comment-list
                :avatar="48"
                :disabled="!can('duplicate_edit_concepts')"
                :comments="state.comments"
                :classes="''"
                :list-classes="''"
                :resource="state.resourceInfo"
                :metadata="state.commentMetadata"
                @edited="editComment"
                @on-delete="deleteComment"
                @added="onUpdateCertainty">
                    <template v-slot:metadata="data">
                        <span class="me-1 small">
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
            <table class="table table-hover">
                <tbody>
                    <tr class="d-flex flex-row" v-for="reference in state.references" :key="reference.id">
                        <td class="text-start py-2 col px-0 ps-1">
                            <div class="d-flex flex-column">
                                <h6>{{ reference.bibliography.title }}</h6>
                                <span class="mb-0">
                                    {{ reference.bibliography.author }}, <span class="text-muted fw-light">{{ reference.bibliography.year}}</span>
                                </span>
                            </div>
                        </td>
                        <td class="text-end p-2 col">
                            <div class="d-flex flex-column">
                                <div>
                                    <p class="fw-light font-italic mb-0" v-if="editReference.id != reference.id">
                                        {{ reference.description }}
                                    </p>
                                    <div class="d-flex" v-else>
                                        <input type="text" class="form-control me-1" v-model="editReference.description" />
                                        <button type="button" class="btn btn-outline-success me-1" @click="onUpdateReference(editReference)">
                                            <i class="fas fa-fw fa-check"></i>
                                        </button>
                                        <button type="button" class="btn btn-outline-danger" @click="cancelEditReference">
                                            <i class="fas fa-fw fa-times"></i>
                                        </button>
                                    </div>
                                </div>
                                <span class="text-muted fw-light">
                                    {{ date(reference.updated_at, undefined, true, true) }}
                                </span>
                            </div>
                        </td>
                        <td class="px-0 pe-1">
                            <div class="dropdown">
                                <span id="dropdownMenuButton" class="clickable" data-bs-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                    <i class="fas fa-fw fa-ellipsis-h"></i>
                                </span>
                                <div class="dropdown-menu" aria-labelledby="dropdownMenuButton">
                                    <a class="dropdown-item" href="#" @click="enableEditReference(reference)">
                                        <i class="fas fa-fw fa-edit text-info"></i> {{ t('global.edit') }}
                                    </a>
                                    <a class="dropdown-item" href="#" @click="onDeleteReference(reference)">
                                        <i class="fas fa-fw fa-trash text-danger"></i> {{ t('global.delete') }}
                                    </a>
                                </div>
                            </div>
                        </td>
                    </tr>
                </tbody>
            </table>
            <h6>
                {{ t('main.entity.references.bibliography.add') }}
            </h6>
            <form role="form" @submit.prevent="onAddReference(state.newItem)">
                <div class="row">
                    <div class="col-md-6">
                        DROPDOWN
                        <!-- <multiselect
                            id="bibliography-search"
                            label="title"
                            track-by="id"
                            v-model="state.newItem.bibliography"
                            :closeOnSelect="true"
                            :hideSelected="true"
                            :internal-search="false"
                            :multiple="false"
                            :options="matchingBibliography"
                            :placeholder="$t('global.select.placeholder')"
                            :select-label="$t('global.select.select')"
                            :deselect-label="$t('global.select.deselect')"
                            @search-change="onBibliographySearchChanged">
                            <template slot="singleLabel" slot-scope="props">
                                <span class="option__desc">
                                    <span class="option__title">
                                        {{ props.option.title }}
                                    </span>
                                </span>
                            </template>
                            <template slot="option" slot-scope="props">
                                <div class="option__desc">
                                    <span class="option__title d-block">
                                        {{ props.option.title }}
                                    </span>
                                    <span class="option__small">
                                        {{ props.option.author }}, <span class="text-muted fw-light">{{ props.option.year}}</span>
                                    </span>
                                </div>
                            </template>
                        </multiselect> -->
                    </div>
                    <div class="col-md-6">
                        <textarea class="form-control" v-model="state.newItem.description" :placeholder="t('main.entity.references.bibliography.comment')"></textarea>
                    </div>
                </div>
                <button type="submit" class="btn btn-outline-success col-md-12 mt-2" :disabled="addReferenceDisabled">
                    <i class="fas fa-fw fa-plus"></i> {{ t('main.entity.references.bibliography.add-button') }}
                </button>
            </form>
        </div>
        <div class="modal-footer">
            <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal" @click="closeModal()">
                <i class="fas fa-fw fa-times"></i> {{ t('global.close') }}
            </button>
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
    import router from '../../../bootstrap/router.js';

    import {
        can,
        getCertaintyClass,
        translateConcept,
    } from '../../../helpers/helpers.js';
    import {
        patchAttribute,
        getAttributeValueComments,
    } from '../../../api.js';

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
            getAttributeValueComments(entity.value.id, aid).then(comments => {
                state.comments = comments;
            });

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
                });
            };
            const editComment = event => {
                const cid = event.comment_id;
                const data = {
                    content: event.content
                };
                $http.patch(`/comment/${cid}`, data).then(response => {
                    let comment = this.getComment(this.comments, cid);
                    if(comment) {
                        comment.content = event.content;
                        comment.updated_at = response.data.updated_at;
                    }
                });
            };
            const deleteComment = event => {
                const cid = event.comment_id;
                const parent_id = event.reply_to;
                $http.delete(`/comment/${cid}`).then(response => {
                    let siblings, parent;
                    if(parent_id) {
                        parent = this.getComment(this.comments, parent_id);
                        siblings = parent.replies;
                    } else {
                        siblings = this.comments;
                    }
                    const comment = siblings.find(s => s.id == cid);
                    comment.deleted_at = Date();
                    
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
                newItem: {},
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
                    return {
                        certainty_from: state.startCertainty,
                        certainty_to: state.certainty,
                    };
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
                editComment,
                deleteComment,
                closeModal,
                // STATE
                state,
            }

        },
        // methods: {
        //     getComment(list, id) {
        //         if(!list || list.length == 0) return;
        //         for(let i=0; i<list.length; i++) {
        //             if(list[i].id == id) {
        //                 return list[i];
        //             }
        //             const gotIt = this.getComment(list[i].replies, id);
        //             if(gotIt) return gotIt;
        //         }
        //     },
        //     loadReplies(event) {
        //         const cid = event.comment_id;
        //         $http.get(`/comment/${cid}/reply`).then(response => {
        //             let comment = this.getComment(this.comments, cid);
        //             if(comment) {
        //                 if(!comment.replies) {
        //                     Vue.set(comment, 'replies', []);
        //                 }
        //                 comment.replies = response.data;
        //             }
        //         });
        //     },
        //     onBibliographySearchChanged(query) {
        //         if(!!query && query.length) {
        //             this.matchingBibliography = this.bibliography.filter(b => {
        //                 let matchesTitle = false;
        //                 let matchesAuthor = false;
        //                 if(b.title) {
        //                     matchesTitle = b.title.toLowerCase().includes(query.toLowerCase());
        //                 }
        //                 if(b.author) {
        //                     matchesAuthor = b.author.toLowerCase().includes(query.toLowerCase());
        //                 }
        //                 return matchesTitle || matchesAuthor;
        //             });
        //         } else {
        //             this.matchingBibliography = this.bibliography.slice();
        //         }
        //     },
        //     onAddReference(item) {
        //         if(!this.$can('add_remove_bibliography')) return;
        //         const data = {
        //             bibliography_id: item.bibliography.id,
        //             description: item.description
        //         };
        //         $httpQueue.add(() => $http.post(`/entity/${this.entityId}/reference/${this.attributeId}`, data).then(response => {
        //             EventBus.$emit('references-updated', {
        //                 action: 'add',
        //                 reference: response.data,
        //                 group: this.refs.attribute.thesaurus_url
        //             });
        //             item.bibliography = {};
        //             item.description = '';
        //         }));
        //     },
        //     onDeleteReference(reference) {
        //         if(!this.$can('add_remove_bibliography')) return;
        //         const id = reference.id;
        //         $httpQueue.add(() => $http.delete(`/entity/reference/${id}`).then(response => {
        //             const index = this.refs.refs.findIndex(r => r.id == reference.id);
        //             if(index > -1) {
        //                 EventBus.$emit('references-updated', {
        //                     action: 'delete',
        //                     reference: reference,
        //                     group: this.refs.attribute.thesaurus_url
        //                 });
        //             }
        //         }));
        //     },
        //     onUpdateReference(editedReference) {
        //         if(!this.$can('edit_bibliography')) return;
        //         const id = editedReference.id;
        //         let ref = this.refs.refs.find(r => r.id == editedReference.id);
        //         if(ref.description == editedReference.description) {
        //             return;
        //         }
        //         const data = {
        //             description: editedReference.description
        //         };
        //         $httpQueue.add(() => $http.patch(`/entity/reference/${id}`, data).then(response => {
        //             EventBus.$emit('references-updated', {
        //                 action: 'edit',
        //                 reference: response.data,
        //                 group: this.refs.attribute.thesaurus_url
        //             });
        //             this.cancelEditReference();
        //         }));
        //     },
        //     enableEditReference(reference) {
        //         Vue.set(this, 'editReference', Object.assign({}, reference));
        //     },
        //     cancelEditReference() {
        //         Vue.set(this, 'editReference', {});
        //     },
        //     hideModal() {
        //         this.$modal.hide('entity-references-modal');
        //     },
        //     routeBack() {
        //         this.refs.value.certainty = this.initialCertaintyValue;
        //         const curr = this.$route;
        //         this.$router.push({
        //             name: 'entitydetail',
        //             params: {
        //                 id: curr.params.id
        //             },
        //             query: curr.query
        //         });
        //     }
        // },
        // data() {
        //     return {
        //         entityId: 0,
        //         attributeId: 0,
        //         editReference: {},
        //         newItem: {
        //             bibliography: {},
        //             description: ''
        //         },
        //         initialCertaintyValue: null,
        //         certainty_description: '',
        //         matchingBibliography: this.bibliography.slice(),
        //         comments: [],
        //         replyTo: {
        //             comment_id: null,
        //             author: {
        //                 name: null,
        //                 nickname: null
        //             }
        //         },
        //     }
        // },
        // computed: {
        //     addReferenceDisabled() {
        //         return !this.newItem.bibliography.id || this.newItem.description.length == 0;
        //     }
        // },
        // watch: {
        //     'refs.value.certainty': function(newVal, oldVal) {
        //         if(!oldVal && newVal) {
        //             this.initialCertaintyValue = newVal;
        //         }
        //     }
        // }
    }
</script>
