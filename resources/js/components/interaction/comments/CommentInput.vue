<template>
    <form
        role="form"
        @submit.prevent="postComment"
    >
        <div class="mb-3 d-flex position-relative">
            <textarea
                id="comment-content"
                ref="messageInput"
                v-model="state.composedMessage"
                :class="textareaClasses"
                class="form-control"
                :placeholder="t('global.comments.text_placeholder')"
                @input="checkForMentionInput"
                @keydown.esc="cancelMentionInput()"
            />

            <ul
                v-if="mentionSearch.show"
                class="list-group position-absolute bg-light m-2"
                :style="mentionSearch.offset"
            >
                <li
                    v-for="user in mentionSearch.result"
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
        <div class="d-flex justify-content-between text-center mt-2">
            <emoji-picker @selected="addEmoji" />
            <button
                type="submit"
                class="btn btn-outline-primary"
                :disabled="!commentValid"
            >
                {{ t('global.comments.submit') }}
                <i class="fas fa-fw fa-paper-plane" />
            </button>
        </div>
    </form>
</template>

<script>

    import {
        postComment as postCommentApi,
    } from '@/api.js';

    import {
        filterUsers,
        getInputCursorPosition,
    } from '@/helpers/helpers.js';

    import {
        computed,
        reactive,
        ref
    } from 'vue';
    import { useI18n } from 'vue-i18n';

    export default {
        props: {
            disabled: {
                required: false,
                type: Boolean,
                default: false,
            },
            metadata: {
                required: false,
                type: Object,
                default: () => ({}),
            },
            postUrl: {
                required: true,
                type: String,
            },
            resource: {
                required: true,
                type: Object,
            },
            replyTarget: {
                required: false,
                type: Object,
                default: () => null,
            },
            textareaClasses: {
                required: false,
                type: Object,
                default: () => ({}),
            },
        },
        emits: ['added'],
        setup(props, context) {

            const state = reactive({
                composedMessage: '',
            });

            const mentionSearch = reactive({
                show: false,
                query: '',
                result: [],
                position: -1,
                positionEnd: -1,
                offset: '',
                target: null,
            });

            const postComment = _ => {
                // comment needs at least changed metadata OR a message
                if(!commentValid.value) return;

                console.log('posting comment', props.resource);

                const replyTargetId = props.replyTarget?.id ?? null;
                postCommentApi(state.composedMessage, props.resource, replyTargetId, props.metadata, props.postUrl).then(data => {
                    context.emit('added', {
                        comment: data,
                        replyTo: replyTargetId,
                    });
                    resetMessage();
                });
            };

            const messageInput = ref(null);

            const focus = () => {
                if(messageInput.value)
                    messageInput.value.focus();
            };

            const resetMessage = _ => {
                state.composedMessage = '';
            };

            const insertMention = user => {
                const from = mentionSearch.position;
                const to = mentionSearch.positionEnd;
                const msg = state.composedMessage;
                const newMsg = msg.substring(0, from) + user.nickname + msg.substring(to);
                state.composedMessage = newMsg;
                cancelMentionInput();
            };

            const cancelMentionInput = _ => {
                mentionSearch.show = false;
                mentionSearch.query = '';
                mentionSearch.result = [];
                mentionSearch.position = -1;
                mentionSearch.positionEnd = -1;
                mentionSearch.offset = '';
                mentionSearch.target = null;
            };

            const checkForMentionInput = event => {
                if(!mentionSearch.show && event.data == '@') {
                    mentionSearch.show = true;
                    mentionSearch.query = '';
                    mentionSearch.result = filterUsers(state.mentionQuery);
                    mentionSearch.position = event.target.selectionStart;
                    mentionSearch.target = event.target;
                } else if(mentionSearch.show) {
                    // If char got deleted...
                    if(event.inputType == 'deleteContentBackward') {
                        // ...and that char was our mention trigger (@)
                        // hide mention search
                        if(mentionSearch.position - 1 == event.target.selectionStart) {
                            cancelMentionInput();
                            return;
                        } else {
                            mentionSearch.query = mentionSearch.query.slice(0, -1);
                            mentionSearch.result = filterUsers(mentionSearch.query);
                        }
                    } else {
                        mentionSearch.query += event.data;
                        mentionSearch.result = filterUsers(mentionSearch.query);
                    }
                    mentionSearch.positionEnd = event.target.selectionStart;
                }

                const cursorPos = getInputCursorPosition(event.target);
                mentionSearch.offset = `left: ${cursorPos.x}px`;
            };


            const addEmoji = event => {
                state.composedMessage += event.emoji;
            };

            const hasMetadata = computed(_ => !!props.metadata && Object.keys(props.metadata).length > 0);
            const commentValid = computed(_ => !props.disabled && (!!state.composedMessage || state.hasMetadata));

            return {
                addEmoji,
                cancelMentionInput,
                checkForMentionInput,
                commentValid,
                focus,
                hasMetadata,
                insertMention,
                messageInput,
                mentionSearch,
                postComment,
                state,
                t: useI18n().t,
            };
        }
    };
</script>