<template>
    <div
        :class="commentBubbleClasses"
        class="comment-bubble d-flex"
    >
        <slot
            v-if="showAvatar"
            name="avatar"
            :user="author"
        >
            <UserAvatarInfo
                :user="author"
                :size="avatar"
                class="me-2"
            />
        </slot>
        <div class="flex-grow-1">
            <div class="card">
                <header
                    v-if="showHeader"
                    class="card-header d-flex flex-row justify-content-between align-items-center py-1 px-3"
                    :class="{ 'border-0': !comment.content }"
                >
                    <div>
                        <slot
                            name="author"
                            :comment="author"
                        >
                            <UsernameInfo :user="author" />
                        </slot>
                    </div>
                    <div class="small">
                        <slot
                            name="metadata"
                            class="me-2"
                            :comment="comment"
                        />
                        <template v-if="comment.updated_at != comment.created_at">
                            <span class="badge bg-light text-dark border">
                                {{ t('global.edited') }}
                            </span>
                            &bull;
                        </template>
                        <span
                            class="text-muted fw-light"
                            :title="datestring(comment.updated_at)"
                        >
                            {{ ago(comment.updated_at) }}
                        </span>
                        <span
                            v-if="!readOnly && !isDeleted() && hasActiveCommands"
                            class="dropdown ms-1"
                        >
                            <span
                                :id="`edit-comment-dropdown-${comment.id}`"
                                class="clickable ms-2 user-select-none"
                                data-bs-toggle="dropdown"
                                aria-haspopup="true"
                                aria-expanded="false"
                            >
                                <i class="fas fa-lg fa-fw fa-ellipsis-h" />
                            </span>
                            <div
                                class="dropdown-menu dropdown-menu-end"
                                :aria-labelledby="`edit-comment-dropdown-${comment.id}`"
                            >
                                <a
                                    v-if="showEditButton(comment)"
                                    class="dropdown-item"
                                    href="#"
                                    @click.prevent="enableEditing(comment)"
                                >
                                    <i class="fas fa-fw fa-edit text-info" /> {{ t('global.edit') }}
                                </a>
                                <a
                                    v-if="showReplyTo(comment)"
                                    class="dropdown-item"
                                    href="#"
                                    @click.prevent="replyTo(comment)"
                                >
                                    <i class="fas fa-fw fa-reply text-primary" /> {{ t('global.reply_to') }}
                                </a>
                                <a
                                    v-if="showDeleteButton(comment)"
                                    class="dropdown-item"
                                    href="#"
                                    @click.prevent="handleDelete(comment)"
                                >
                                    <i class="fas fa-fw fa-trash text-danger" /> {{ t('global.delete') }}
                                </a>
                            </div>
                        </span>
                    </div>
                </header>
                <div v-if="!emptyMetadata()">
                    <slot
                        v-if="!isDeleted() && state.editing"
                        name="body-editing"
                        :comment="comment"
                        :content="state.editContent"
                    >
                        <div class="card-body px-3 py-2">
                            <textarea
                                v-model="state.editContent"
                                class="form-control"
                            />
                            <div class="mt-1 d-flex flex-row justify-content-end">
                                <button
                                    type="button"
                                    class="btn btn-success btn-sm me-2"
                                    :disabled="state.editContent == comment.content"
                                    @click="handleEdit(comment, state.editContent)"
                                >
                                    <i class="fas fa-fw fa-save" /> {{ t('global.save') }}
                                </button>
                                <button
                                    type="button"
                                    class="btn btn-danger btn-sm"
                                    @click="disableEditing()"
                                >
                                    <i class="fas fa-fw fa-times" /> {{ t('global.cancel') }}
                                </button>
                            </div>
                        </div>
                    </slot>
                    <slot
                        v-else-if="!isDeleted()"
                        name="body"
                        :comment="comment"
                    >
                        <div
                            v-if="comment.content"
                            class="card-body px-3 py-2"
                        >
                            <!-- TODO; Here we insert user input into the comment. This is bad!-->
                            <!-- eslint-disable vue/no-v-html -->
                            <p
                                class="card-text"
                                v-html="mentionify(comment.content)"
                            />
                            <!-- eslint-enable vue/no-v-html -->
                        </div>
                    </slot>
                    <slot
                        v-else
                        name="body-deleted"
                        :comment="comment"
                    >
                        <div
                            class="card-body px-3 py-2"
                            style="opacity: 0.75;"
                        >
                            <p class="card-text fst-italic">
                                {{ t('global.comments.deleted_info') }}
                            </p>
                        </div>
                    </slot>
                </div>
                <footer>
                    <slot
                        name="footer"
                        :comment="comment"
                    />
                </footer>
            </div>
            <div
                v-if="repliesCount > 0"
                class="d-flex flex-row justify-content-end"
            >
                <a
                    href="#"
                    class="small text-body"
                    @click.prevent="toggleReplies()"
                >
                    <div v-show="state.repliesOpen">
                        <span>
                            {{
                                t('global.comments.hide_reply', repliesCount, {
                                    cnt:
                                        repliesCount
                                })
                            }}
                        </span>
                        <i class="fas fa-fw fa-caret-up" />
                    </div>
                    <div v-show="!state.repliesOpen">
                        <span>
                            {{
                                t('global.comments.show_reply', repliesCount, {
                                    cnt:
                                        repliesCount
                                })
                            }}
                        </span>
                        <i class="fas fa-fw fa-caret-down" />
                    </div>
                </a>
            </div>

            <div v-if="state.repliesOpen">
                <slot
                    name="replies"
                    :comment="comment"
                />
            </div>
        </div>
    </div>
</template>

<script>

    import {
        computed,
        reactive,
    } from 'vue';

    import {
        deleteComment,
        editComment,
    } from '@/api.js';

    import {
        userId,
    } from '@/helpers/helpers.js';

    import {
        ago,
        datestring,
        mentionify,
    } from '@/helpers/filters.js';


    import UserAvatarInfo from '../../user/UserAvatarInfo.vue';
    import UsernameInfo from '../../user/UsernameInfo.vue';
    import { useI18n } from 'vue-i18n';
    import { can } from '../../../helpers/helpers';

    export default {
        components: {
            UserAvatarInfo,
            UsernameInfo,
        },
        props: {
            readOnly: {
                required: false,
                type: Boolean,
                default: false,
            },
            showAvatar: {
                required: false,
                type: Boolean,
                default: true,
            },
            showHeader: {
                required: false,
                type: Boolean,
                default: true,
            },
            avatar: {
                required: false,
                type: Number,
                default: 42
            },
            comment: {
                type: Object,
                required: true,
            },
            editUrl: {
                required: false,
                type: String,
                default: '/comment/{cid}',
            },
            deleteUrl: {
                required: false,
                type: String,
                default: '/comment/{cid}',
            },
        },
        emits: ['reply', 'toggle-replies'],
        setup(props, context) {

            const state = reactive({
                editing: false,
                editContent: '',
                repliesOpen: false,
                currentUserId: userId(),
            });

            const isDeleted = _ => {
                return !!props.comment.deleted_at;
            };

            const isOwn = _ => {
                //TODO REMOVE
                return true;
                return state.currentUserId === props.comment.author.id;
            };

            const emptyMetadata = _ => {
                return props.comment.metadata && props.comment.metadata.is_empty;
            };

            const toggleReplies = _ => {
                state.repliesOpen = !state.repliesOpen;
                context.emit('toggle-replies', state.repliesOpen);
            };

            const repliesCount = computed(_ => {
                return props.comment?.replies_count ?? 0;
            });

            const handleDelete = comment => {
                const cid = comment.id;
                deleteComment(cid, props.deleteUrl).then(data => {
                    comment.deleted_at = Date();
                });
            };

            const showEditButton = _ => {
                return isOwn() && !state.editing && can('comments_edit');
            };

            const showDeleteButton = _ => {
                return isOwn() && can('comments_delete');
            };

            const disableEditing = _ => {
                state.editing = false;
            };

            const enableEditing = comment => {
                state.editing = true;
                state.editContent = comment.content;
            };

            const handleEdit = async (comment, composedMessage) => {
                disableEditing();
                await editComment(comment.id, composedMessage, editUrl.value);
                // .then(data => {
                //     comment.content = composedMessage;
                //     comment.updated_at = data.updated_at;
                // });
            };

            const showReplyTo = _ => {
                return can('comments_create');
            };

            const replyTo = (comment) => {
                context.emit('reply', comment);
            };

            const author = computed(_ => {
                return props.comment?.author ?? {
                    id: 0,
                    nickname: 'Unknown',
                };
            });

            const hasActiveCommands = computed(_ => {
                return showEditButton() || showDeleteButton() || showReplyTo();
            });

            const commentBubbleClasses = computed(_ => {
                return {
                    'opacity-50': isDeleted(),
                };
            });

            return {
                ago,
                author,
                commentBubbleClasses,
                datestring,
                disableEditing,
                emptyMetadata,
                enableEditing,
                handleDelete,
                handleEdit,
                hasActiveCommands,
                isDeleted,
                isOwn,
                mentionify,
                repliesCount,
                replyTo,
                showEditButton,
                showDeleteButton,
                showReplyTo,
                state,
                t: useI18n().t,
                toggleReplies,
            };
        }
    };
</script>