<template>
    <modal name="entity-references-modal" width="50%" height="80%" :scrollable="true" :draggable="true" :resizable="true" @closed="routeBack">
        <div class="modal-content h-100">
            <div class="modal-header">
                <h5 class="modal-title">{{ $t('main.entity.references.title') }}</h5>
                <button type="button" class="close" aria-label="Close" @click="hideModal">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body col col-md-8 offset-md-2 scroll-y-auto">
                <h4>{{ $t('main.entity.references.certainty') }}</h4>
                <div class="progress" @click="setCertainty">
                    <div class="progress-bar" role="progressbar" :class="getCertaintyClass(refs.value.certainty)" :aria-valuenow="refs.value.certainty" aria-valuemin="0" aria-valuemax="100" :style="{width: refs.value.certainty+'%'}">
                        <span class="sr-only">
                            {{ refs.value.certainty }}% certainty
                        </span>
                        {{ refs.value.certainty }}%
                    </div>
                </div>
                <comment-list
                    :comments="comments"
                    @edited="editComment"
                    @on-delete="deleteComment"
                    @reply-to="addReplyTo"
                    @load-replies="loadReplies">
                    <template v-slot:metadata="data">
                        <span class="badge" :class="getCertaintyClass(data.comment.metadata.certainty_from, 'badge')">
                            {{ data.comment.metadata.certainty_from || '???' }}
                        </span>
                        &rarr;
                        <span class="badge" :class="getCertaintyClass(data.comment.metadata.certainty_to, 'badge')">
                            {{ data.comment.metadata.certainty_to || '???' }}
                        </span>
                    </template>
                </comment-list>
                <hr />
                <div v-if="replyTo.comment_id > 0" class="mb-1">
                    <span class="badge badge-info">
                        {{ $t('global.replying_to', {name: replyTo.author.name}) }}
                        <a href="#" @click.prevent="cancelReplyTo" class="text-white">
                            <i class="fas fa-fw fa-times"></i>
                        </a>
                    </span>
                </div>
                <form role="form" @submit.prevent="onUpdateCertainty">
                    <div class="form-group">
                        <textarea class="form-control" v-model="certainty_description" id="comment-content" ref="comCnt" :placeholder="$t('main.entity.references.certaintyc')"></textarea>
                    </div>
                    <div class="text-center">
                        <button type="submit" class="btn btn-outline-success">
                            <i class="fas fa-fw fa-save"></i> {{ $t('main.entity.references.certaintyu') }}
                        </button>
                    </div>
                </form>
                <h4 class="mt-3">{{ $t('main.entity.references.bibliography.title') }}</h4>
                <table class="table table-hover">
                    <tbody>
                        <tr class="d-flex flex-row" v-for="reference in refs.refs">
                            <td class="text-left py-2 col px-0 pl-1">
                                <div class="d-flex flex-column">
                                    <h6>{{ reference.bibliography.title }}</h6>
                                    <span class="mb-0">
                                        {{ reference.bibliography.author }}, <span class="text-muted font-weight-light">{{ reference.bibliography.year}}</span>
                                    </span>
                                </div>
                            </td>
                            <td class="text-right p-2 col">
                                <div class="d-flex flex-column">
                                    <div>
                                        <p class="font-weight-light font-italic mb-0" v-if="editReference.id != reference.id">
                                            {{ reference.description }}
                                        </p>
                                        <div class="d-flex" v-else>
                                            <input type="text" class="form-control mr-1" v-model="editReference.description" />
                                            <button type="button" class="btn btn-outline-success mr-1" @click="onUpdateReference(editReference)">
                                                <i class="fas fa-fw fa-check"></i>
                                            </button>
                                            <button type="button" class="btn btn-outline-danger" @click="cancelEditReference">
                                                <i class="fas fa-fw fa-times"></i>
                                            </button>
                                        </div>
                                    </div>
                                    <span class="text-muted font-weight-light">
                                        {{ reference.updated_at | date(undefined, true, true) }}
                                    </span>
                                </div>
                            </td>
                            <td class="px-0 pr-1">
                                <div class="dropdown">
                                    <span id="dropdownMenuButton" class="clickable" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                        <i class="fas fa-fw fa-ellipsis-h"></i>
                                    </span>
                                    <div class="dropdown-menu" aria-labelledby="dropdownMenuButton">
                                        <a class="dropdown-item" href="#" @click="enableEditReference(reference)">
                                            <i class="fas fa-fw fa-edit text-info"></i> {{ $t('global.edit') }}
                                        </a>
                                        <a class="dropdown-item" href="#" @click="onDeleteReference(reference)">
                                            <i class="fas fa-fw fa-trash text-danger"></i> {{ $t('global.delete') }}
                                        </a>
                                    </div>
                                </div>
                            </td>
                        </tr>
                    </tbody>
                </table>
                <h5>{{ $t('main.entity.references.bibliography.add') }}</h5>
                <form role="form" @submit.prevent="onAddReference(newItem)">
                    <div class="row">
                        <div class="col-md-6">
                            <multiselect
                                id="bibliography-search"
                                label="title"
                                track-by="id"
                                v-model="newItem.bibliography"
                                :closeOnSelect="true"
                                :hideSelected="true"
                                :internal-search="false"
                                :multiple="false"
                                :options="matchingBibliography"
                                :placeholder="$t('global.select.placehoder')"
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
                                            {{ props.option.author }}, <span class="text-muted font-weight-light">{{ props.option.year}}</span>
                                        </span>
                                    </div>
                                </template>
                            </multiselect>
                        </div>
                        <div class="col-md-6">
                            <textarea class="form-control" v-model="newItem.description" :placeholder="$t('main.entity.references.bibliography.comment')"></textarea>
                        </div>
                    </div>
                    <button type="submit" class="btn btn-outline-success col-md-12 mt-2" :disabled="addReferenceDisabled">
                        <i class="fas fa-fw fa-plus"></i> {{ $t('main.entity.references.bibliography.add-button') }}
                    </button>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" @click="hideModal">
                    <i class="fas fa-fw fa-times"></i> {{ $t('global.close') }}
                </button>
            </div>
        </div>
    </modal>
</template>

<script>
    import { EventBus } from '../event-bus.js';

    export default {
        props: {
            bibliography: {
                required: true,
                type: Array
            },
            refs: {
                required: true,
                type: Object
            }
        },
        beforeRouteEnter(to, from, next) {
            next(vm => {
                vm.init();
            });
        },
        beforeRouteUpdate(to, from, next) {
            vm.init();
            next();
        },
        beforeRouteLeave(to, from, next) {
            this.$modal.hide('entity-references-modal');
            next();
        },
        methods: {
            init() {
                if(this.refs.value.certainty) {
                    this.initialCertaintyValue = this.refs.value.certainty;
                }
                const curr = this.$route;
                this.entityId = curr.params.id;
                this.attributeId = curr.params.aid;
                $http.get(`entity/${this.entityId}/comment/${this.attributeId}`).then(response => {
                    this.comments.length = 0;
                    this.comments = response.data;
                    this.$modal.show('entity-references-modal');
                })
            },
            setCertainty(event) {
                const maxSize = event.target.parentElement.scrollWidth; // progress bar width in px
                const clickPos = event.layerX; // in px
                const currentValue = this.refs.value.certainty;
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
                Vue.set(this.refs.value, 'certainty', value);
            },
            onUpdateCertainty() {
                if(!this.$can('duplicate_edit_concepts')) return;
                if(
                    this.refs.value.certainty == this.initialCertaintyValue
                    &&
                    !this.certainty_description
                ) {
                    return;
                }
                let data = {
                    certainty: this.refs.value.certainty,
                    content: this.certainty_description,
                };
                // only add metadata if certainty has changed
                // (as it's the only part of metadata)
                if(this.initialCertaintyValue != this.refs.value.certainty) {
                    data.metadata = {
                        certainty_from: this.initialCertaintyValue,
                        certainty_to: this.refs.value.certainty
                    };
                }
                if(this.replyTo.comment_id) {
                    data.reply_to = this.replyTo.comment_id;
                }
                $httpQueue.add(() => $http.patch(`/entity/${this.entityId}/attribute/${this.attributeId}`, data).then(response => {
                    const addedComment = response.data.comment;
                    if(this.replyTo.comment_id) {
                        let comment = this.comments.find(c => c.id == this.replyTo.comment_id);
                        if(comment.replies) {
                            comment.replies.push(addedComment);
                        }
                        comment.replies_count++;
                        this.cancelReplyTo();
                    } else {
                        this.comments.push(addedComment);
                    }
                    this.certainty_description = null;
                    this.initialCertaintyValue = this.refs.value.certainty;
                    const attributeName = this.$translateConcept(this.refs.attribute.thesaurus_url);
                    this.$showToast(
                        this.$t('main.entity.references.toasts.updated-certainty.title'),
                        this.$t('main.entity.references.toasts.updated-certainty.msg', {
                            name: attributeName,
                            i: this.refs.value.certainty
                        }),
                        'success'
                    );
                }));
            },
            getComment(list, id) {
                if(!list || list.length == 0) return;
                for(let i=0; i<list.length; i++) {
                    if(list[i].id == id) {
                        return list[i];
                    }
                    const gotIt = this.getComment(list[i].replies, id);
                    if(gotIt) return gotIt;
                }
            },
            loadReplies(event) {
                const cid = event.comment_id;
                $http.get(`entity/comment/${cid}/reply`).then(response => {
                    let comment = this.getComment(this.comments, cid);
                    if(comment) {
                        if(!comment.replies) {
                            Vue.set(comment, 'replies', []);
                        }
                        comment.replies = response.data;
                    }
                });
            },
            editComment(event) {
                const cid = event.comment_id;
                const data = {
                    content: event.content
                };
                $http.patch(`entity/comment/${cid}`, data).then(response => {
                    let comment = this.getComment(this.comments, cid);
                    if(comment) {
                        comment.content = event.content;
                    }
                });
            },
            addReplyTo(event) {
                if(!event.comment) return;
                const comment = event.comment;
                this.replyTo.comment_id = comment.id;
                this.replyTo.author.name = comment.author.name;
                this.replyTo.author.nickname = comment.author.nickname;
                this.$refs.comCnt.focus();
            },
            cancelReplyTo() {
                this.replyTo.comment_id = null;
                this.replyTo.author.name = null;
                this.replyTo.author.nickname = null;
            },
            deleteComment(event) {
                const eid = this.entityId;
                const aid = this.attributeId;
                const cid = event.comment_id;
                const parent_id = event.reply_to;
                $http.delete(`entity/${eid}/attribute/${aid}/comment/${cid}`).then(response => {
                    let siblings, parent;
                    if(parent_id) {
                        parent = this.getComment(this.comments, parent_id);
                        siblings = parent.replies;
                    } else {
                        siblings = this.comments;
                    }
                    const idx = siblings.findIndex(s => s.id == cid);
                    siblings.splice(idx, 1);
                    if(parent) {
                        parent.replies_count--;
                    }
                });
            },
            getCertaintyClass(value, prefix = 'bg') {
                let classes = {};

                if(value <= 25) {
                    classes[`${prefix}-danger`] = true;
                } else if(value <= 50) {
                    classes[`${prefix}-warning`] = true;
                } else if(value <= 75) {
                    classes[`${prefix}-info`] = true;
                } else {
                    classes[`${prefix}-success`] = true;
                }

                return classes;
            },
            onBibliographySearchChanged(query) {
                if(!!query && query.length) {
                    this.matchingBibliography = this.bibliography.filter(b => {
                        let matchesTitle = false;
                        let matchesAuthor = false;
                        if(b.title) {
                            matchesTitle = b.title.toLowerCase().includes(query.toLowerCase());
                        }
                        if(b.author) {
                            matchesAuthor = b.author.toLowerCase().includes(query.toLowerCase());
                        }
                        return matchesTitle || matchesAuthor;
                    });
                } else {
                    this.matchingBibliography = this.bibliography.slice();
                }
            },
            onAddReference(item) {
                if(!this.$can('add_remove_bibliography')) return;
                const data = {
                    bibliography_id: item.bibliography.id,
                    description: item.description
                };
                $httpQueue.add(() => $http.post(`/entity/${this.entityId}/reference/${this.attributeId}`, data).then(response => {
                    EventBus.$emit('references-updated', {
                        action: 'add',
                        reference: response.data,
                        group: this.refs.attribute.thesaurus_url
                    });
                    item.bibliography = {};
                    item.description = '';
                }));
            },
            onDeleteReference(reference) {
                if(!this.$can('add_remove_bibliography')) return;
                const id = reference.id;
                $httpQueue.add(() => $http.delete(`/entity/reference/${id}`).then(response => {
                    const index = this.refs.refs.findIndex(r => r.id == reference.id);
                    if(index > -1) {
                        EventBus.$emit('references-updated', {
                            action: 'delete',
                            reference: reference,
                            group: this.refs.attribute.thesaurus_url
                        });
                    }
                }));
            },
            onUpdateReference(editedReference) {
                if(!this.$can('edit_bibliography')) return;
                const id = editedReference.id;
                let ref = this.refs.refs.find(r => r.id == editedReference.id);
                if(ref.description == editedReference.description) {
                    return;
                }
                const data = {
                    description: editedReference.description
                };
                $httpQueue.add(() => $http.patch(`/entity/reference/${id}`, data).then(response => {
                    EventBus.$emit('references-updated', {
                        action: 'edit',
                        reference: response.data,
                        group: this.refs.attribute.thesaurus_url
                    });
                    this.cancelEditReference();
                }));
            },
            enableEditReference(reference) {
                Vue.set(this, 'editReference', Object.assign({}, reference));
            },
            cancelEditReference() {
                Vue.set(this, 'editReference', {});
            },
            hideModal() {
                this.$modal.hide('entity-references-modal');
            },
            routeBack() {
                this.refs.value.certainty = this.initialCertaintyValue;
                const curr = this.$route;
                this.$router.push({
                    name: 'entitydetail',
                    params: {
                        id: curr.params.id
                    },
                    query: curr.query
                });
            }
        },
        data() {
            return {
                entityId: 0,
                attributeId: 0,
                editReference: {},
                newItem: {
                    bibliography: {},
                    description: ''
                },
                initialCertaintyValue: null,
                certainty_description: null,
                matchingBibliography: this.bibliography.slice(),
                comments: [],
                replyTo: {
                    comment_id: null,
                    author: {
                        name: null,
                        nickname: null
                    }
                }
            }
        },
        computed: {
            addReferenceDisabled() {
                return !this.newItem.bibliography.id || this.newItem.description.length == 0;
            }
        },
        watch: {
            'refs.value.certainty': function(newVal, oldVal) {
                if(!oldVal && newVal) {
                    this.initialCertaintyValue = newVal;
                }
            }
        }
    }
</script>
