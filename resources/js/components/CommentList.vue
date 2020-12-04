<template>
    <div>
        <div v-for="comment in comments" :key="comment.id" class="mt-3" v-if="!hideComments && comments.length > 0">
            <div class="card">
                <div class="card-header d-flex flex-row justify-content-between py-2 px-3" :class="{'border-0': !comment.content}">
                    <div>
                        <slot name="author" :comment="comment.author">
                            <span class="font-weight-bold">
                                {{ comment.author.name }}
                            </span>
                            &bull;
                            <span class="text-muted font-weight-light">
                                {{ comment.author.nickname }}
                            </span>
                        </slot>
                    </div>
                    <div class="small">
                        <slot name="metadata" class="mr-2" :comment="comment"></slot>
                        <template v-if="comment.updated_at != comment.created_at">
                            <span class="badge badge-light border">
                                {{ $t('global.edited') }}
                            </span>
                            &bull;
                        </template>
                        <span class="text-muted font-weight-light" :title="comment.updated_at | datestring">
                            {{ comment.updated_at | ago }}
                        </span>
                        <span class="dropdown">
                            <span :id="`edit-comment-dropdown-${comment.id}`" class="clickable" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                <i class="fas fa-fw fa-ellipsis-h"></i>
                            </span>
                            <div class="dropdown-menu" :aria-labelledby="`edit-comment-dropdown-${comment.id}`">
                                <a class="dropdown-item" href="#" @click.prevent="enableEditing(comment)" v-if="comment.author.id == $userId() && editing.id != comment.id">
                                    <i class="fas fa-fw fa-edit text-info"></i> {{ $t('global.edit') }}
                                </a>
                                <a class="dropdown-item" href="#" @click.prevent="emitReplyTo(comment)">
                                    <i class="fas fa-fw fa-reply text-success"></i> {{ $t('global.reply_to') }}
                                </a>
                                <a class="dropdown-item" href="#" @click.prevent="emitDelete(comment)" v-if="comment.author.id == $userId()">
                                    <i class="fas fa-fw fa-trash text-danger"></i> {{ $t('global.delete') }}
                                </a>
                            </div>
                        </span>
                    </div>
                </div>
                <slot name="body-editing" :comment="comment" :content="editing.content" v-if="editing.id == comment.id">
                    <div class="card-body p-3">
                        <textarea class="form-control" v-model="editing.content"></textarea>
                        <div class="mt-1 d-flex flex-row justify-content-end">
                            <button type="button" class="btn btn-success btn-sm mr-2" :disabled="editing.content == comment.content" @click="emitEdited(comment, editing.content)">
                                <i class="fas fa-fw fa-save"></i> {{ $t('global.save') }}
                            </button>
                            <button type="button" class="btn btn-danger btn-sm" @click="disableEditing()">
                                <i class="fas fa-fw fa-times"></i> {{ $t('global.cancel') }}
                            </button>
                        </div>
                    </div>
                </slot>
                <slot name="body" :comment="comment" v-else>
                    <div class="card-body p-3" v-if="comment.content">
                        <p class="card-text" v-html="$options.filters.mentionify(comment.content)"></p>
                    </div>
                </slot>
            </div>
            <div v-if="comment.replies_count > 0" class="d-flex flex-row justify-content-end">
                <a href="#" class="small text-body" @click.prevent="toggleReplies(comment)">
                    <div v-show="repliesOpen[comment.id]">
                        <span v-html="$tc('global.comments.hide_reply', comment.replies_count, {cnt: comment.replies_count})"></span>
                        <i class="fas fa-fw fa-caret-up"></i>
                    </div>
                    <div v-show="!repliesOpen[comment.id]">
                        <span v-html="$tc('global.comments.show_reply', comment.replies_count, {cnt: comment.replies_count})"></span>
                        <i class="fas fa-fw fa-caret-down"></i>
                    </div>
                </a>
            </div>
            <comment-list
                class="offset-md-2"
                v-if="repliesOpen[comment.id] && comment.replies"
                :comments="comment.replies"
                :show-hide-button="false"
                @edited="passEdit"
                @on-delete="passDelete"
                @reply-to="passReplyTo"
                @load-replies="passLoadReplies">
            </comment-list>
        </div>
        <div class="text-center mt-2" v-if="showHideButton && comments.length > 0">
            <a href="#" @click.prevent="hideComments = !hideComments">
                <i class="fas fa-fw fa-comments"></i>
                <span v-if="hideComments">
                    {{ $t('global.comments.show') }}
                </span>
                <span v-else>
                    {{ $t('global.comments.hide') }}
                </span>
            </a>
        </div>
        <p v-else-if="comments.length == 0" class="alert alert-info m-0 mt-2">
            {{ $t('global.comments.empty_list') }}
        </p>
    </div>
</template>

<script>
    export default {
        props: {
            comments: {
                required: true,
                type: Array
            },
            showHideButton: {
                required: false,
                type: Boolean,
                default: true
            }
        },
        mounted() {
        },
        methods: {
            enableEditing(comment) {
                if(this.editing.id) {
                    this.editing.content = null;
                }
                this.editing.id = comment.id;
                this.editing.content = comment.content;
            },
            disableEditing() {
                this.editing.id = null;
                this.editing.content = null;
            },
            emitEdited(comment, newContent) {
                this.$emit('edited', {
                    content: newContent,
                    comment_id: comment.id
                });
                this.disableEditing();
            },
            emitReplyTo(comment) {
                this.$emit('reply-to', {
                    comment: comment
                });
            },
            emitDelete(comment) {
                this.$emit('on-delete', {
                    comment_id: comment.id,
                    reply_to: comment.reply_to
                });
            },
            passEdit(event) {
                this.$emit('edited', event);
            },
            passDelete(event) {
                this.$emit('on-delete', event);
            },
            passReplyTo(event) {
                this.$emit('reply-to', event);
            },
            passLoadReplies(event) {
                this.$emit('load-replies', event);
            },
            toggleReplies(comment) {
                const cid = comment.id;
                if(this.repliesOpen[cid]) {
                    this.repliesOpen[cid] = false;
                } else if(this.repliesOpen[cid] === false) {
                    this.repliesOpen[cid] = true;
                } else {
                    Vue.set(this.repliesOpen, cid, true);
                    this.$emit('load-replies', {
                        comment_id: comment.id
                    });
                }
            }
        },
        data() {
            return {
                hideComments: false,
                repliesOpen: {},
                editing: {
                    id: null,
                    content: null
                }
            }
        }
    }
</script>
