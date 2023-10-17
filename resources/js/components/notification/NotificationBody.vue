<template>
    <div
        class="d-flex flex-row px-3"
        :class="{'bg-light-dark': odd, 'bg-light': !odd, 'py-3': !smallText, 'py-2': smallText}"
    >
        <div
            v-if="state.showAvatar"
            class="me-3"
            :class="{'opacity-50': state.read}"
        >
            <a
                href="#"
                @click.prevent.stop="showUserInfo(state.sender)"
            >
                <user-avatar
                    :user="state.sender"
                    :size="avatar"
                />
            </a>
        </div>
        <div
            class="d-flex flex-column flex-grow-1"
            :class="{'small': smallText}"
        >
            <div class="d-flex flex-row justify-content-between">
                <span v-if="state.read">
                    {{ t('global.notifications.body.title_read') }}
                </span>
                <span
                    v-else
                    class="fw-bold"
                >
                    {{ t('global.notifications.body.title_new') }}
                </span>
                <div class="d-flex flex-row">
                    <div v-if="state.isSystem">
                        <span class="badge bg-warning me-2">
                            {{ t('global.notifications.body.type.system') }}
                        </span>
                    </div>
                    <a
                        v-if="!state.read"
                        href="#"
                        class="text-muted"
                        @click.prevent.stop="markNotificationAsRead()"
                    >
                        <i class="fas fa-xs fa-check" />
                    </a>
                    <a
                        v-if="!state.isSystem"
                        href="#"
                        class="text-muted ms-2"
                        @click.prevent.stop="deleteNotification()"
                    >
                        <i class="fas fa-xs fa-times" />
                    </a>
                </div>
            </div>
            <div :class="{'opacity-50': state.read}">
                <div v-if="state.notificationType == 'comment'">
                    <a
                        href="#"
                        class="fw-bold text-decoration-none me-1"
                        @click.prevent.stop="showUserInfo(state.sender)"
                    >
                        <span>{{ state.sender.nickname }}</span>
                    </a>
                    <span
                        v-html="t('global.notifications.body.user_left_comment_on', {
                            name: getCommentedObjectName(notf),
                        })"
                    />
                    <div>
                        <router-link :to="getNotificationSourceLink(notf)">
                            {{ t('global.notifications.body.goto_comments') }}
                        </router-link>
                    </div>
                    <p
                        v-if="state.hasContent"
                        class="mb-0 px-2 py-1 mt-1 rounded"
                        :class="{'bg-light-dark': !odd, 'bg-light': odd}"
                    >
                        {{ truncate(notf.data.content, 100) }}
                    </p>
                    <div
                        class="text-info d-flex flex-row justify-content-between"
                        :class="state.smallClass"
                    >
                        <a
                            v-if="state.canReply"
                            href="#"
                            class="text-body ms-1"
                            @click.prevent.stop="toggleReplyBox()"
                        >
                            {{ t('global.notifications.body.reply') }}
                            <span v-show="state.showReplyBox">
                                <i class="fas fa-fw fa-caret-up" />
                            </span>
                            <span v-show="!state.showReplyBox">
                                <i class="fas fa-fw fa-caret-down" />
                            </span>
                        </a>
                        <span
                            :title="datestring(notf.created_at)"
                            :class="{'opacity-50': state.read}"
                        >
                            {{ ago(notf.created_at) }}
                        </span>
                    </div>
                </div>
                <div v-else-if="state.notificationType == 'entity'">
                    <a
                        href="#"
                        class="fw-bold text-decoration-none me-1"
                        @click.prevent.stop="showUserInfo(state.sender)"
                    >
                        <span>{{ state.sender.nickname }}</span>
                    </a>
                    <span
                        v-html="t('global.notifications.body.user_edited_entity', {
                            name: `${notf.info.name}`
                        })"
                    />
                    <div
                        class="text-info d-flex flex-row justify-content-between"
                        :class="state.smallClass"
                    >
                        <router-link :to="getNotificationSourceLink(notf)">
                            {{ t('global.notifications.body.goto_entity') }}
                        </router-link>
                        <span
                            :title="datestring(notf.created_at)"
                            :class="{'opacity-50': state.read}"
                        >
                            {{ ago(notf.created_at) }}
                        </span>
                    </div>
                </div>
            </div>
            <div
                v-show="state.showReplyBox"
                :class="state.smallClass"
            >
                <p
                    v-show="state.replySend"
                    class="alert alert-success px-2 py-1 mb-0"
                >
                    <i class="fas fa-fw fa-check" />
                    {{ t('global.notifications.body.reply_sent') }}
                </p>
                <form
                    v-show="!state.replySend"
                    role="form"
                    @submit.prevent="postReply()"
                >
                    <div class="form-group mb-0">
                        <textarea
                            id="reply-message"
                            v-model="state.replyMessage"
                            class="form-control resize-none"
                            :style="state.smallStyle"
                            name="reply-message"
                            rows="1"
                            :placeholder="t('global.notifications.body.mention_info')"
                        />
                    </div>
                    <div
                        v-show="state.replyMessage"
                        class="text-end"
                        :class="state.smallClass"
                    >
                        <button
                            type="submit"
                            class="btn btn-sm btn-outline-success"
                            :disabled="!state.replyMessage"
                            @click.prevent="postReply()"
                        >
                            <i class="fas fa-fw fa-reply" />
                            <span v-html="t('global.notifications.body.reply_to_user', {name: state.sender.nickname})" />
                        </button>
                        <button
                            type="button"
                            class="btn btn-sm btn-outline-success ms-2"
                            :disabled="!state.replyMessage"
                            @click.prevent="postReply('to_chat')"
                        >
                            <i class="fas fa-fw fa-comment" />
                            {{ t('global.notifications.body.reply_to_chat') }}
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</template>

<script>
    import {
        computed,
        reactive,
        toRefs,
    } from 'vue';

    import { useI18n } from 'vue-i18n';

    import {
        truncate,
        datestring,
        ago,
    } from '@/helpers/filters.js';
    import {
        getUserBy,
        getNotificationSourceLink,
        simpleResourceType,
        translateConcept,
    } from '@/helpers/helpers.js';
    import {
        showUserInfo,
    } from '@/helpers/modal.js';

    export default {
        props: {
            notf: {
                required: true,
                type: Object
            },
            odd: {
                required: false,
                type: Boolean,
                default: false
            },
            avatar: {
                required: false,
                type: Number,
                default: 32
            },
            smallText: {
                required: false,
                type: Boolean,
                default: false
            },
        },
        emits: ['read', 'delete'],
        setup(props, context) {
            const { t } = useI18n();
            const {
                notf,
                odd,
                avatar,
                smallText,
            } = toRefs(props);

            // FETCH
            // DATA
            const state = reactive({
                showReplyBox: false,
                replySend: false,
                replyMessage: '',
                showAvatar: computed(_ => avatar.value > 0),
                read: computed(_ => !!notf.value.read_at),
                sender: computed(_ => getUserBy(notf.value.data.user_id) || {}),
                isSystem: computed(_ => notf.value.data.metadata.persistence === 'system'),
                smallStyle: computed(_ => smallText.value ? 'font-size: 0.7rem;' : ''),
                smallClass: computed(_ => {
                    if(smallText.value) {
                        return {
                            'mt-1': true,
                        }
                    } else {
                        return {
                            'mt-2': true,
                        }
                    }
                }),
                notificationType: computed(_ => {
                    switch(notf.value.type) {
                        case 'App\\Notifications\\CommentPosted':
                            return 'comment';
                        case 'App\\Notifications\\EntityUpdated':
                            return 'entity';
                    }
                }),
                canReply: computed(_ => {
                    return state.notificationType == 'comment';
                }),
                hasContent: computed(_ => {
                    return !!notf.value.data.content;
                }),
            });

            // FUNCTIONS
            const getCommentedObjectName = n => {
                switch(simpleResourceType(n.data.resource.type)) {
                    case 'entity':
                        return n.info.name;
                    case 'attribute_value':
                        return `${translateConcept(n.info.attribute_url)} (${n.info.name})`;
                    default:
                        return n.info.name;
                }
            };
            const markNotificationAsRead = _ => {
                context.emit('read', notf.value.id);
            };
            const deleteNotification = _ => {
                context.emit('delete', notf.value.id);
            };
            const toggleReplyBox = _ => {
                state.showReplyBox = !state.showReplyBox;
            };
            const postReply = action => {
                if(!state.replyMessage) return;

                const isReply = action === 'to_chat' ? null : this.notf.data.comment;
                this.$postComment(this.replyMessage, this.notf.data.resource, isReply, null, comment => {
                    this.replyMessage = '';
                    this.replySend = true;
                    this.$emit('posted', {
                        comment: comment
                    });
                })
            };

            // RETURN
            return {
                t,
                // HELPERS
                truncate,
                datestring,
                ago,
                getNotificationSourceLink,
                showUserInfo,
                // LOCAL
                getCommentedObjectName,
                markNotificationAsRead,
                deleteNotification,
                toggleReplyBox,
                // STATE
                state,
            };
        },
    }
</script>