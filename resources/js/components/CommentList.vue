<template>
    <!-- eslint-disable vue/no-v-html-->
    <div
        class="comment-list"
        :class="classes"
    >
        <div :class="listClasses">
            <div v-show="!state.commentsHidden">
                <CommentBubble
                    v-for="comment in comments"
                    :key="comment.id"
                    class="mt-2 d-flex me-2"
                    :avatar="avatar"
                    :comment="comment"
                    :delete-url="deleteUrl"
                    :edit-url="editUrl"
                    @reply="setReplyToComment(comment)"
                    @toggle-replies="toggleReplies(comment)"
                >
                    <template #replies>
                        <comment-list
                            :avatar="avatar"
                            :classes="classes"
                            :comments="comment.replies"
                            :delete-url="deleteUrl"
                            :disabled="disabled"
                            :edit-url="editUrl"
                            :list-classes="listClasses"
                            :post-form="false"
                            :post-url="postUrl"
                            :reply-url="replyUrl"
                            :resource="resource"
                            @reply="setReplyToComment"
                        />
                    </template>
                </CommentBubble>
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
                    <span v-if="state.hideComments">
                        {{ t('global.comments.show') }}
                        ({{ comments.length }})
                    </span>
                    <span v-else>
                        {{ t('global.comments.hide') }}
                    </span>
                </button>
            </div>

            <Alert
                v-if="!state.hasComments"
                class="m-0 mt-2"
                :message="t('global.comments.empty_list')"
            />
        </div>
        <div v-if="postForm">
            <hr>
            <!-- <div
                v-if="state.replyToComment.comment_id > 0"
                class="mb-1"
            >
                <span class="badge bg-info">
                    {{ t('global.replying_to', { name: state.replyToComment.author.name }) }}
                    <a
                        href="#"
                        class="text-white"
                        @click.prevent="cancelReplyToComment"
                    >
                        <i class="fas fa-fw fa-times" />
                    </a>
                </span>
            </div> -->

            <div v-if="state.replyToComment?.id">
                <span class="badge bg-primary align-self-start fs-6 rounded-0 rounded-top ms-2">
                    <a
                        href="#"
                        class="text-white text-decoration-none"
                        @click.prevent="resetReplyToComment"
                    >
                        <i class="fas fa-fw fa-reply" />
                        <span class="mx-2">
                            {{ t('global.replying_to', { name: state.replyToComment.author.name }) }}
                        </span>
                        <i class="fas fa-fw fa-times" />
                    </a>
                </span>
            </div>
            <CommentInput
                ref="commentInput"
                :disabled="disabled"
                :metadata="metadata"
                :post-url="postUrl"
                :resource="resource"
                :reply-target="state.replyToComment"
                :textarea-classes="inputClasses"
                @added="added"
            />
        </div>
    </div>
</template>

<script>
    import {
        computed,
        reactive,
        ref,
    } from 'vue';

    import { useI18n } from 'vue-i18n';

    import { getCommentReplies } from '@/api.js';

    import Alert from '@/components/Alert.vue';
    import CommentBubble from './interaction/comments/CommentBubble.vue';
    import CommentInput from './interaction/comments/CommentInput.vue';

    export default {
        components: {
            Alert,
            CommentBubble,
            CommentInput,
        },
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
            showHideButton: {
                required: false,
                type: Boolean,
                default: false
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
                default: () => ({}),
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
                default: 'd-flex flex-column',
            },
            listClasses: {
                required: false,
                type: String,
                default: 'flex-grow-1',
            },
        },
        emits: ['added', 'reply'],
        setup(props, context) {
            const { t } = useI18n();
            const commentInput = ref(null);

            const emptyReply = {
                id: null,
                author: {
                    name: null,
                    nickname: null
                },
                message: ''
            };

            // DATA
            const state = reactive({
                comments: computed(_ => Array.isArray(props.comments)? props.comments : []),
                commentsHidden: computed(_ => state.hideComments || !state.hasComments),
                displayHideButton: computed(_ => props.hideButton && state.hasComments),
                hasComments: computed(_ => state.comments.length > 0),
                hideComments: false,
                replyToComment: emptyReply,
            });

            // FUNCTIONS
            const resetReplyToComment = _ => {
                state.replyToComment = emptyReply;
            };

            const toggleHideState = _ => {
                state.hideComments = !state.hideComments;
            };

            const setReplyToComment = comment => {
                context.emit('reply', comment);
                state.replyToComment = comment;
                commentInput.value.focus();
            };

            const toggleReplies = comment => {
                getCommentReplies(comment.id, props.replyUrl).then(data => {
                    console.log(data);
                    comment.replies = data || [];
                });
            };

            const added = ({ comment, replyTo }) => {
                resetReplyToComment();                
            };

            const replying = computed(_ => state.replyToComment.id != null);

            const inputClasses = computed(_ => {
                return replying.value ? [
                    'border-primary',
                    'border-3',
                ] : [];
            });

            // RETURN
            return {
                added,
                commentInput,
                inputClasses,
                resetReplyToComment,
                replying,
                setReplyToComment,
                state,
                t: useI18n().t,
                toggleHideState,
                toggleReplies
            };
        },
    };
</script>
