<template>
    <div :class="classes">
        <div :class="listClasses">
            <div v-show="!state.commentsHidden">
                <div
                    v-for="comment in comments"
                    :key="comment.id"
                    class="mt-2 d-flex"
                >
                    <slot
                        name="avatar"
                        :user="comment.author"
                    >
                        <a
                            href="#"
                            @click.prevent="showUserInfo(comment.author)"
                        >
                            <user-avatar
                                :user="comment.author"
                                :size="avatar"
                            />
                <div
                    v-for="comment in comments"
                    :key="comment.id"
                    class="mt-2 d-flex"
                >
                    <slot
                        name="avatar"
                        :user="comment.author"
                    >
                        <a
                            href="#"
                            @click.prevent="showUserInfo(comment.author)"
                        >
                            <user-avatar
                                :user="comment.author"
                                :size="avatar"
                            />
                        </a>
                    </slot>
                    <div class="ms-3 flex-grow-1">
                        <div class="card">
                            <div
                                class="card-header d-flex flex-row justify-content-between py-2 px-3"
                                :class="{'border-0': !comment.content}"
                            >
                            <div
                                class="card-header d-flex flex-row justify-content-between py-2 px-3"
                                :class="{'border-0': !comment.content}"
                            >
                                <div>
                                    <slot
                                        name="author"
                                        :comment="comment.author"
                                    >
                                        <a
                                            href="#"
                                            class="text-body text-decoration-none"
                                            @click.prevent="showUserInfo(comment.author)"
                                        >
                                    <slot
                                        name="author"
                                        :comment="comment.author"
                                    >
                                        <a
                                            href="#"
                                            class="text-body text-decoration-none"
                                            @click.prevent="showUserInfo(comment.author)"
                                        >
                                            <span class="fw-medium">
                                                {{ comment.author.name }}
                                            </span>
                                            &bull;
                                            <span class="text-muted fw-light">
                                                {{ comment.author.nickname }}
                                            </span>
                                        </a>
                                    </slot>
                                </div>
                                <div class="small">
                                    <slot
                                        name="metadata"
                                        class="me-2"
                                        :comment="comment"
                                    />
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
                                    <span
                                        class="text-muted fw-light"
                                        :title="datestring(comment.updated_at)"
                                    >
                                        {{ ago(comment.updated_at) }}
                                    </span>
                                    <span
                                        v-if="!comment.deleted_at && (showEditButton(comment) || showDeleteButton(comment) || state.showReplyTo)"
                                        class="dropdown ms-1"
                                    >
                                        <span
                                            :id="`edit-comment-dropdown-${comment.id}`"
                                            class="clickable"
                                            data-bs-toggle="dropdown"
                                            aria-haspopup="true"
                                            aria-expanded="false"
                                        >
                                            <i class="fas fa-fw fa-ellipsis-h" />
                                    <span
                                        v-if="!comment.deleted_at && (showEditButton(comment) || showDeleteButton(comment) || state.showReplyTo)"
                                        class="dropdown ms-1"
                                    >
                                        <span
                                            :id="`edit-comment-dropdown-${comment.id}`"
                                            class="clickable"
                                            data-bs-toggle="dropdown"
                                            aria-haspopup="true"
                                            aria-expanded="false"
                                        >
                                            <i class="fas fa-fw fa-ellipsis-h" />
                                        </span>
                                        <div
                                            class="dropdown-menu"
                                            :aria-labelledby="`edit-comment-dropdown-${comment.id}`"
                                        >
                                            <a
                                                v-if="showEditButton(comment)"
                                                class="dropdown-item"
                                                href="#"
                                                @click.prevent="enableEditing(comment)"
                                            >
                                                <i class="fas fa-fw fa-edit text-info" /> {{ t('global.edit') }}
                                        <div
                                            class="dropdown-menu"
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
                                                v-if="state.showReplyTo"
                                                class="dropdown-item"
                                                href="#"
                                                @click.prevent="setReplyTo(comment)"
                                            >
                                                <i class="fas fa-fw fa-reply text-success" /> {{ t('global.reply_to') }}
                                            <a
                                                v-if="state.showReplyTo"
                                                class="dropdown-item"
                                                href="#"
                                                @click.prevent="setReplyTo(comment)"
                                            >
                                                <i class="fas fa-fw fa-reply text-success" /> {{ t('global.reply_to') }}
                                            </a>
                                            <a
                                                v-if="showDeleteButton(comment)"
                                                class="dropdown-item"
                                                href="#"
                                                @click.prevent="handleDelete(comment)"
                                            >
                                                <i class="fas fa-fw fa-trash text-danger" /> {{ t('global.delete') }}
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
                            </div>
                            <div v-if="!emptyMetadata(comment)">
                                <slot
                                    v-if="!isDeleted(comment) && state.editing.id == comment.id"
                                    name="body-editing"
                                    :comment="comment"
                                    :content="state.editing.content"
                                >
                                <slot
                                    v-if="!isDeleted(comment) && state.editing.id == comment.id"
                                    name="body-editing"
                                    :comment="comment"
                                    :content="state.editing.content"
                                >
                                    <div class="card-body px-3 py-2">
                                        <textarea
                                            v-model="state.editing.content"
                                            class="form-control"
                                        />
                                        <textarea
                                            v-model="state.editing.content"
                                            class="form-control"
                                        />
                                        <div class="mt-1 d-flex flex-row justify-content-end">
                                            <button
                                                type="button"
                                                class="btn btn-success btn-sm me-2"
                                                :disabled="state.editing.content == comment.content"
                                                @click="handleEdit(comment, state.editing.content)"
                                            >
                                                <i class="fas fa-fw fa-save" /> {{ t('global.save') }}
                                            <button
                                                type="button"
                                                class="btn btn-success btn-sm me-2"
                                                :disabled="state.editing.content == comment.content"
                                                @click="handleEdit(comment, state.editing.content)"
                                            >
                                                <i class="fas fa-fw fa-save" /> {{ t('global.save') }}
                                            </button>
                                            <button
                                                type="button"
                                                class="btn btn-danger btn-sm"
                                                @click="disableEditing()"
                                            >
                                                <i class="fas fa-fw fa-times" /> {{ t('global.cancel') }}
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
                                    v-else-if="!isDeleted(comment)"
                                    name="body"
                                    :comment="comment"
                                >
                                    <div
                                        v-if="comment.content"
                                        class="card-body px-3 py-2"
                                    >
                                        <!-- eslint-disable -->
                                        <p
                                            class="card-text"
                                            v-html="mentionify(comment.content)"
                                        />
                                        <!-- eslint-enable -->
                                    </div>
                                </slot>
                                <slot
                                    v-else
                                    name="body-deleted"
                                    :comment="comment"
                                >
                                    <div
                                        class="card-body bg-warning px-3 py-2"
                                        style="opacity: 0.75;"
                                    >
                                <slot
                                    v-else
                                    name="body-deleted"
                                    :comment="comment"
                                >
                                    <div
                                        class="card-body bg-warning px-3 py-2"
                                        style="opacity: 0.75;"
                                    >
                                        <p class="card-text fst-italic">
                                            {{ t('global.comments.deleted_info') }}
                                        </p>
                                    </div>
                                </slot>
                            </div>
                        </div>
                        <div
                            v-if="comment.replies_count > 0"
                            class="d-flex flex-row justify-content-end"
                        >
                            <a
                                href="#"
                                class="small text-body"
                                @click.prevent="toggleReplies(comment)"
                            >
                        <div
                            v-if="comment.replies_count > 0"
                            class="d-flex flex-row justify-content-end"
                        >
                            <a
                                href="#"
                                class="small text-body"
                                @click.prevent="toggleReplies(comment)"
                            >
                                <div v-show="state.repliesOpen[comment.id]">
                                    <!-- eslint-disable-next-line vue/no-v-html -->
                                    <span v-html="t('global.comments.hide_reply', comment.replies_count, {cnt: comment.replies_count})" />
                                    <i class="fas fa-fw fa-caret-up" />
                                </div>
                                <div v-show="!state.repliesOpen[comment.id]">
                                    <!-- eslint-disable-next-line vue/no-v-html -->
                                    <span v-html="t('global.comments.show_reply', comment.replies_count, {cnt: comment.replies_count})" />
                                    <i class="fas fa-fw fa-caret-down" />
                                </div>
                            </a>
                        </div>
                        <comment-list
                            v-if="state.repliesOpen[comment.id] && comment.replies"
                            :comments="comment.replies"
                            :hide-button="true"
                            :post-form="false"
                            :disabled="disabled"
                            :avatar="avatar"
                            :resource="resource"
                            :post-url="postUrl"
                            :edit-url="editUrl"
                            :delete-url="deleteUrl"
                            :reply-url="replyUrl"
                            :post-url="postUrl"
                            :edit-url="editUrl"
                            :delete-url="deleteUrl"
                            :reply-url="replyUrl"
                            :classes="classes"
                            :list-classes="listClasses"
                        />
                            :list-classes="listClasses"
                        />
                    </div>
                </div>
            </div>
            <div
                v-show="state.displayHideButton"
                class="text-center mt-2"
            >
                <button
                    class="btn btn-sm btn-outline-primary"
                    @click="toggleHideState()"
                >
                    <i class="fas fa-fw fa-comments me-1" />
            <div
                v-show="state.displayHideButton"
                class="text-center mt-2"
            >
                <button
                    class="btn btn-sm btn-outline-primary"
                    @click="toggleHideState()"
                >
                    <i class="fas fa-fw fa-comments me-1" />
                    <span v-if="state.hideComments">
                        {{ t('global.comments.show') }}
                        ({{ comments.length }})
                    </span>
                    <span v-else>
                        {{ t('global.comments.hide') }}
                    </span>
                </button>
            </div>
            <div
                v-if="!state.hasComments"
                class="alert alert-info m-0 mt-2"
                role="alert"
            >
                {{ t('global.comments.empty_list') }}
            </div>
        </div>
        <hr>
        <div v-if="postForm">
            <div
                v-if="state.replyTo.comment_id > 0"
                class="mb-1"
            >
                <span class="badge bg-info">
                    {{ t('global.replying_to', {name: state.replyTo.author.name}) }}
                    <a
                        href="#"
                        class="text-white"
                        @click.prevent="cancelReplyTo"
                    >
                        <i class="fas fa-fw fa-times" />
                    </a>
                </span>
            </div>
            <form
                role="form"
                @submit.prevent="postComment"
            >
                <div class="mb-3 d-flex position-relative">
                    <textarea
                        id="comment-content"
                        ref="messageInput"
                        v-model="state.composedMessage"
                        class="form-control"
                        :placeholder="t('global.comments.text_placeholder')"
                        @input="checkForMentionInput"
                        @keydown.esc="cancelMentionInput()"
                    />
                    <div class="ms-2 mt-auto">
                        <emoji-picker @selected="addEmoji" />
                    </div>
                    <ul
                        v-if="state.mentionSearch.show"
                        class="list-group position-absolute bg-light m-2"
                        :style="state.mentionSearch.offset"
                    >
                        <li
                            v-for="user in state.mentionSearch.result"
                            :key="user.id"
                            class="list-group-item list-group-item-hover clickable d-flex flex-row gap-2 align-items-center"
                            @click="insertMention(user)"
                        >
                            <span class="fw-bold">
                                {{ user.name }}
                            </span>
                            <span class="text-muted small">
                                {{ user.nickname }}
                            </span>
                        </li>
                    </ul>
                </div>
                <div class="text-center mt-2">
                    <button
                        type="submit"
                        class="btn btn-outline-success"
                        :disabled="disabled"
                    >
                        <i class="fas fa-fw fa-save" />
                        {{ t('global.comments.submit') }}
                    </button>
                </div>
            </form>
        </div>
    </div>
</template>

<script>
    import {
        computed,
        reactive,
        ref,
        toRefs,
    } from 'vue';

    import { useI18n } from 'vue-i18n';

    import {
        ago,
        datestring,
        mentionify,
    } from '@/helpers/filters.js';
    import {
        userId,
        filterUsers,
        getInputCursorPosition,
    } from '@/helpers/helpers.js';
    import {
        showUserInfo,
    } from '@/helpers/modal.js';
    import {
        postComment as postCommentApi,
        getCommentReplies,
        deleteComment,
        editComment,
    } from '@/api.js';

    export default {
        props: {
            disabled: {
                required: false,
                type: Boolean,
                default: false,
            },
            comments: {
                required: true,
                type: Array
            },
            avatar: {
                required: false,
                type: Number,
                default: 32
            },
            hideButton: {
                required: false,
                type: Boolean,
                default: true
            },
            postForm: {
                required: false,
                type: Boolean,
                default: true,
            },
            resource: {
                required: true,
                type: Object,
            },
            metadata: {
                required: false,
                type: Object,
                default: ()=>({}),
            },
            postUrl: {
                required: false,
                type: String,
                default: '/comment',
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
            replyUrl: {
                required: false,
                type: String,
                default: '/comment/{cid}/reply',
            },
            classes: {
                required: false,
                type: String,
                default: 'd-flex flex-column h-100',
            },
            listClasses: {
                required: false,
                type: String,
                default: 'flex-grow-1',
            },
        },
        emits: ['added'],
        setup(props, context) {
            const { t } = useI18n();
            const {
                disabled,
                comments,
                avatar,
                hideButton,
                postForm,
                resource,
                metadata,
                postUrl,
                editUrl,
                deleteUrl,
                replyUrl,
                classes,
                listClasses,
            } = toRefs(props);
            const messageInput = ref(null);

            // DATA
            const state = reactive({
                hideComments: false,
                repliesOpen: {},
                editing: {
                    id: null,
                    content: null
                },
                replyTo: {
                    comment_id: null,
                    author: {
                        name: null,
                        nickname: null
                    }
                },
                mentionSearch: {
                    show: false,
                    query: '',
                    result: [],
                    position: -1,
                    positionEnd: -1,
                    offset: '',
                    target: null,
                },
                composedMessage: '',
                currentUserId: userId(),
                commentsHidden: computed(_ => state.hideComments || !state.hasComments),
                displayHideButton: computed(_ => hideButton.value && state.hasComments),
                hasComments: computed(_ => comments.value.length > 0),
                showReplyTo: computed(_ =>  postForm.value),
            });

            // FUNCTIONS
            const resetMessage = _ => {
                state.composedMessage = '';
            };
            const resetReplyTo = _ => {
                state.replyTo.comment_id = null,
                state.replyTo.author = {};
            };
            const showEditButton = comment => {
                return comment.author.id === state.currentUserId && state.editing.id != comment.id;
            };
            const showDeleteButton = comment => {
                return comment.author.id === state.currentUserId;
            };
            const enableEditing = comment => {
                if(state.editing.id) {
                    state.editing.content = null;
                }
                state.editing.id = comment.id;
                state.editing.content = comment.content;
            };
            const disableEditing = _ => {
                state.editing.id = null;
                state.editing.content = null;
            };
            const handleEdit = (comment, composedMessage) => {
                editComment(comment.id, composedMessage, editUrl.value).then(data => {
                    comment.content = composedMessage;
                    comment.updated_at = data.updated_at;
                });
                disableEditing();
            };
            const toggleHideState = _ => {
                state.hideComments = !state.hideComments;
            };
            const toggleReplies = comment => {
                const cid = comment.id;
                if(state.repliesOpen[cid]) {
                    state.repliesOpen[cid] = false;
                } else if(state.repliesOpen[cid] === false) {
                    state.repliesOpen[cid] = true;
                } else {
                    state.repliesOpen[cid] = true;
                    getCommentReplies(cid, replyUrl.value).then(data => {
                        comment.replies = data;
                    });
                }
            };
            const postComment = _ => {
                // comment needs at least changed metadata OR a message
                if(disabled.value || (!state.composedMessage && !metadata.value)) return;

                const replyTo = state.replyTo.comment_id || null;
                postCommentApi(state.composedMessage, resource.value, replyTo, metadata.value, postUrl.value).then(data => {
                    context.emit('added', {
                        comment: data,
                        replyTo: replyTo,
                    });
                    resetMessage();
                    resetReplyTo();
                });
            };
            const setReplyTo = comment => {
                state.replyTo.comment_id = comment.id,
                state.replyTo.author.name = comment.author.name;
                state.replyTo.author.nickname = comment.author.nickname;
                messageInput.value.focus();
            };
            const cancelReplyTo = _ => {
                resetReplyTo();
            };
            const handleDelete = comment => {
                const cid = comment.id;
                deleteComment(cid, deleteUrl.value).then(data => {
                    comment.deleted_at = Date();
                });
            };
            const addEmoji = event => {
                state.composedMessage += event.emoji;
            };
            const cancelMentionInput = _ => {
                state.mentionSearch.show = false;
                state.mentionSearch.query = '';
                state.mentionSearch.result = [];
                state.mentionSearch.position = -1;
                state.mentionSearch.positionEnd = -1;
                state.mentionSearch.offset = '';
                state.mentionSearch.target = null;
            };
            const checkForMentionInput = event => {
                if(!state.mentionSearch.show && event.data == '@') {
                    state.mentionSearch.show = true;
                    state.mentionSearch.query = '';
                    state.mentionSearch.result = filterUsers(state.mentionQuery);
                    state.mentionSearch.position = event.target.selectionStart;
                    state.mentionSearch.target = event.target;
                } else if(state.mentionSearch.show) {
                    // If char got deleted...
                    if(event.inputType == 'deleteContentBackward') {
                        // ...and that char was our mention trigger (@)
                        // hide mention search
                        if(state.mentionSearch.position - 1 == event.target.selectionStart) {
                            cancelMentionInput();
                            return;
                        } else {
                            state.mentionSearch.query = state.mentionSearch.query.slice(0, -1);
                            state.mentionSearch.result = filterUsers(state.mentionSearch.query);
                        }
                    } else {
                        state.mentionSearch.query += event.data;
                        state.mentionSearch.result = filterUsers(state.mentionSearch.query);
                    }
                    state.mentionSearch.positionEnd = event.target.selectionStart;
                }

                const cursorPos = getInputCursorPosition(event.target);
                state.mentionSearch.offset = `left: ${cursorPos.x}px`;
            };
            const insertMention = user => {
                const from = state.mentionSearch.position;
                const to = state.mentionSearch.positionEnd;
                const msg = state.composedMessage;
                const newMsg = msg.substring(0, from) + user.nickname + msg.substring(to);
                state.composedMessage = newMsg;
                cancelMentionInput();
            };
            const isDeleted = comment => {
                return !!comment.deleted_at;
            };
            const emptyMetadata = comment => {
                return comment.metadata && comment.metadata.is_empty;
            };

            // RETURN
            return {
                t,
                // HELPERS
                ago,
                datestring,
                mentionify,
                showUserInfo,
                // LOCAL
                messageInput,
                showEditButton,
                showDeleteButton,
                enableEditing,
                disableEditing,
                handleEdit,
                toggleHideState,
                toggleReplies,
                postComment,
                setReplyTo,
                cancelReplyTo,
                handleDelete,
                addEmoji,
                cancelMentionInput,
                checkForMentionInput,
                insertMention,
                isDeleted,
                emptyMetadata,
                // STATE
                state,
            };
        },
    }
</script>
