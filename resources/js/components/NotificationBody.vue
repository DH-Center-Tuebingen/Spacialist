<template>
    <div class="d-flex flex-row py-2 px-3" :class="{'bg-light-dark': odd, 'bg-light': !odd}">
            <div class="mr-5" :class="{'opacity-50': read}">
                <span>
                    {{ sender.nickname }}
                </span>
            </div>
            <div class="d-flex flex-column small flex-grow-1">
                <div class="d-flex flex-row justify-content-between">
                    <span class="font-weight-bold text-primary">
                        <template v-if="notf.type == 'App\\Notifications\\CommentPosted'">
                            New Comment/Reply
                        </template>
                        <template v-else>
                            New Notification
                        </template>
                    </span>
                    <div class="d-flex flex-row">
                        <div v-if="notf.data.persistent">
                            <span class="badge badge-warning mr-2">
                                persistent
                            </span>
                        </div>
                        <a href="#" class="text-muted" @click.prevent.stop="markNotificationAsRead(notf.id)" v-if="!read">
                            <i class="fas fa-xs fa-check"></i>
                        </a>
                        <a v-if="!notf.data.persistent" href="#" class="text-muted ml-2" @click.prevent.stop="deleteNotification(notf.id)">
                            <i class="fas fa-xs fa-times"></i>
                        </a>
                    </div>
                </div>
                <div class="text-muted" :class="{'opacity-50': read}">
                    <div v-if="notf.type == 'App\\Notifications\\CommentPosted'">
                        <span class="font-weight-bold">{{ sender.nickname }}</span> commented on ...
                    </div>
                    <div>
                        {{ notf.data.content | truncate(100) }}
                    </div>
                </div>
                <div class="text-info" :class="{'opacity-50': read}">
                    <span :title="notf.created_at | datestring">
                        {{ notf.created_at | ago }}
                    </span>
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
        },
        computed: {
            read() {
                return !!this.notf.read_at;
            },
            sender() {
                return this.$getUser(this.notf.data.user_id);
            }
        }
    }
</script>