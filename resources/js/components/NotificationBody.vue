<template>
    <div class="d-flex flex-row px-3" :class="{'bg-light-dark': odd, 'bg-light': !odd, 'py-3': !smallText, 'py-2': smallText}">
            <div class="me-3" :class="{'opacity-50': read}" v-if="showAvatar">
                <a href="#" @click.prevent="$showUserInfo(sender)">
                    <user-avatar :user="sender" :size="avatar"></user-avatar>
                </a>
            </div>
            <div class="d-flex flex-column flex-grow-1" :class="{'small': smallText}">
                <div class="d-flex flex-row justify-content-between">
                    <span class="font-weight-bold text-primary">
                        {{ $t('global.notifications.body.title') }}
                    </span>
                    <div class="d-flex flex-row">
                        <div v-if="isSystem">
                            <span class="badge badge-warning me-2">
                                {{ $t('global.notifications.body.type.system') }}
                            </span>
                        </div>
                        <a href="#" class="text-muted" @click.prevent.stop="markNotificationAsRead(notf.id)" v-if="!read">
                            <i class="fas fa-xs fa-check"></i>
                        </a>
                        <a v-if="!isSystem" href="#" class="text-muted ms-2" @click.prevent.stop="deleteNotification(notf.id)">
                            <i class="fas fa-xs fa-times"></i>
                        </a>
                    </div>
                </div>
                <div class="text-muted" :class="{'opacity-50': read}">
                    <div v-if="notf.type == 'App\\Notifications\\CommentPosted'">
                        <a href="#" @click.prevent="$showUserInfo(sender)" class="text-body">
                            <span class="font-weight-bold">{{ sender.nickname }}</span>
                        </a>
                        <span v-html="$t('global.notifications.body.user_left_comment_on', {
                            name: `${notf.data.resource.id} (${notf.data.resource.type})`
                        })"></span>
                        <div>
                            <router-link :to="$getNotificationSourceLink(notf)" class="">
                                Kommentarbereich öffnen
                            </router-link>
                        </div>
                    </div>
                    <p class="mb-0 px-2 py-1 mt-1 rounded" :class="{'bg-light-dark': !odd, 'bg-light': odd}">
                        {{ notf.data.content | truncate(100) }}
                    </p>
                </div>
                <div class="text-info" :class="smallClass">
                    <span :title="notf.created_at | datestring" :class="{'opacity-50': read}">
                        {{ notf.created_at | ago }}
                    </span>
                    <a href="#" class="text-body" @click.prevent="toggleReplyBox">
                        {{ $t('global.notifications.body.reply') }}
                        <span v-show="showReplyBox">
                            <i class="fas fa-fw fa-caret-up"></i>
                        </span>
                        <span v-show="!showReplyBox">
                            <i class="fas fa-fw fa-caret-down"></i>
                        </span>
                    </a>
                </div>
                <div v-show="showReplyBox" :class="smallClass">
                    <p class="alert alert-success px-2 py-1 mb-0" v-show="replySend">
                        <i class="fas fa-fw fa-check"></i>
                        {{ $t('global.notifications.body.reply_sent') }}
                    </p>
                    <form role="form" @submit.prevent="postReply()" v-show="!replySend">
                        <div class="form-group mb-0">
                            <textarea class="form-control resize-none" :style="smallStyle" id="reply-message" name="reply-message" rows="1" :placeholder="$t('global.notifications.body.mention_info')" v-model="replyMessage"></textarea>
                        </div>
                        <div class="text-end" :class="smallClass">
                            <button type="submit" class="btn btn-sm btn-outline-success" :disabled="!replyMessage" @click.prevent="postReply()">
                                <i class="fas fa-fw fa-reply"></i>
                                <span v-html="$t('global.notifications.body.reply_to_user', {name: sender.nickname})"></span>
                            </button>
                            <button type="button" class="btn btn-sm btn-outline-success ms-2" :disabled="!replyMessage" @click.prevent="postReply('to_chat')">
                                <i class="fas fa-fw fa-comment"></i>
                                {{ $t('global.notifications.body.reply_to_chat') }}
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
</template>

<script>
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
        methods: {
            markNotificationAsRead(id) {
                this.$emit('read', {
                    id: this.notf.id
                });
            },
            deleteNotification(id) {
                this.$emit('delete', {
                    id: this.notf.id
                });
            },
            toggleReplyBox() {
                this.showReplyBox = !this.showReplyBox;
            },
            postReply(action) {
                if(!this.replyMessage) return;

                const isReply = action === 'to_chat' ? null : this.notf.data.comment;
                this.$postComment(this.replyMessage, this.notf.data.resource, isReply, null, comment => {
                    this.replyMessage = '';
                    this.replySend = true;
                    this.$emit('posted', {
                        comment: comment
                    });
                })
            }
        },
        data() {
            return {
                showReplyBox: false,
                replySend: false,
                replyMessage: '',
            }
        },
        computed: {
            showAvatar() {
                return this.avatar > 0;
            },
            read() {
                return !!this.notf.read_at;
            },
            sender() {
                return this.$getUserBy(this.notf.data.user_id);
            },
            isSystem() {
                return this.notf.data.metadata.persistence === 'system';
            },
            smallStyle() {
                return this.smallText ? 'font-size: 0.7rem;' : '';
            },
            smallClass() {
                if(this.smallText) {
                    return {
                        'mt-1': true,
                    }
                } else {
                    return {
                        'mt-2': true,
                    }
                }
            }
        }
    }
</script>