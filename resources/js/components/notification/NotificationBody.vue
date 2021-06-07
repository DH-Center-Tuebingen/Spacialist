<template>
    <div class="d-flex flex-row px-3" :class="{'bg-light-dark': odd, 'bg-light': !odd, 'py-3': !smallText, 'py-2': smallText}">
            <div class="me-3" :class="{'opacity-50': state.read}" v-if="state.showAvatar">
                <a href="#" @click.prevent.stop="showUserInfo(state.sender)">
                    <user-avatar :user="state.sender" :size="avatar"></user-avatar>
                </a>
            </div>
            <div class="d-flex flex-column flex-grow-1" :class="{'small': smallText}">
                <div class="d-flex flex-row justify-content-between">
                    <span v-if="state.read">
                        {{ t('global.notifications.body.title_read') }}
                    </span>
                    <span class="fw-bold" v-else>
                        {{ t('global.notifications.body.title_new') }}
                    </span>
                    <div class="d-flex flex-row">
                        <div v-if="state.isSystem">
                            <span class="badge bg-warning me-2">
                                {{ t('global.notifications.body.type.system') }}
                            </span>
                        </div>
                        <a href="#" class="text-muted" @click.prevent.stop="markNotificationAsRead(notf.id)" v-if="!state.read">
                            <i class="fas fa-xs fa-check"></i>
                        </a>
                        <a v-if="!state.isSystem" href="#" class="text-muted ms-2" @click.prevent.stop="deleteNotification(notf.id)">
                            <i class="fas fa-xs fa-times"></i>
                        </a>
                    </div>
                </div>
                <div class="text-muted" :class="{'opacity-50': state.read}">
                    <div v-if="notf.type == 'App\\Notifications\\CommentPosted'">
                        <a href="#" @click.prevent.stop="showUserInfo(state.sender)" class="text-body text-decoration-none me-1">
                            <span class="fst-italic">{{ state.sender.nickname }}</span>
                        </a>
                        <span v-html="t('global.notifications.body.user_left_comment_on', {
                            name: `${notf.data.resource.id} (${notf.data.resource.type})`
                        })"></span>
                        <div>
                            <router-link :to="getNotificationSourceLink(notf)" class="">
                                {{ t('global.notifications.body.goto_comments') }}
                            </router-link>
                        </div>
                    </div>
                    <p class="mb-0 px-2 py-1 mt-1 rounded" :class="{'bg-light-dark': !odd, 'bg-light': odd}">
                        {{ truncate(notf.data.content, 100) }}
                    </p>
                </div>
                <div class="text-info d-flex flex-row justify-content-between" :class="state.smallClass">
                    <a href="#" class="text-body ms-1" @click.prevent.stop="toggleReplyBox()">
                        {{ t('global.notifications.body.reply') }}
                        <span v-show="state.showReplyBox">
                            <i class="fas fa-fw fa-caret-up"></i>
                        </span>
                        <span v-show="!state.showReplyBox">
                            <i class="fas fa-fw fa-caret-down"></i>
                        </span>
                    </a>
                    <span :title="datestring(notf.created_at)" :class="{'opacity-50': state.read}">
                        {{ ago(notf.created_at) }}
                    </span>
                </div>
                <div v-show="state.showReplyBox" :class="state.smallClass">
                    <p class="alert alert-success px-2 py-1 mb-0" v-show="state.replySend">
                        <i class="fas fa-fw fa-check"></i>
                        {{ t('global.notifications.body.reply_sent') }}
                    </p>
                    <form role="form" @submit.prevent="postReply()" v-show="!state.replySend">
                        <div class="form-group mb-0">
                            <textarea class="form-control resize-none" :style="state.smallStyle" id="reply-message" name="reply-message" rows="1" :placeholder="t('global.notifications.body.mention_info')" v-model="state.replyMessage"></textarea>
                        </div>
                        <div class="text-end" :class="state.smallClass">
                            <button type="submit" class="btn btn-sm btn-outline-success" :disabled="!state.replyMessage" @click.prevent="postReply()">
                                <i class="fas fa-fw fa-reply"></i>
                                <span v-html="t('global.notifications.body.reply_to_user', {name: state.sender.nickname})"></span>
                            </button>
                            <button type="button" class="btn btn-sm btn-outline-success ms-2" :disabled="!state.replyMessage" @click.prevent="postReply('to_chat')">
                                <i class="fas fa-fw fa-comment"></i>
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
    } from '../../helpers/filters.js';
    import {
        getUserBy,
        getNotificationSourceLink,
    } from '../../helpers/helpers.js';
    import {
        showUserInfo,
    } from '../../helpers/modal.js';

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
            });

            // FUNCTIONS
            const markNotificationAsRead = id => {
                context.emit('read', {
                    id: notf.value.id
                });
            };
            const deleteNotification = id => {
                context.emit('delete', {
                    id: notf.value.id
                });
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
                markNotificationAsRead,
                deleteNotification,
                toggleReplyBox,
                // PROPS
                notf,
                odd,
                avatar,
                smallText,
                // STATE
                state,
            };
        },
    }
</script>