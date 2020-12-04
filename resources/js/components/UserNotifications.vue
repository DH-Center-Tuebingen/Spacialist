<template>
    <div class="container d-flex flex-column overflow-hidden h-100">
        <h2>Notifications</h2>
        <ul class="nav nav-tabs nav-fill" id="message-tab-list" role="tablist">
            <li class="nav-item" role="presentation">
                <a class="nav-link active" id="all-tab" data-toggle="tab" href="#all" role="tab" aria-controls="all" aria-selected="true">
                    All
                    <span class="badge badge-secondary">{{ notifications.length }}</span>
                </a>
            </li>
            <li class="nav-item" role="presentation">
                <a class="nav-link" id="unread-tab" data-toggle="tab" href="#unread" role="tab" aria-controls="unread" aria-selected="false">
                    Unread
                    <span class="badge badge-secondary">{{ unreadNotifications.length }}</span>
                </a>
            </li>
            <li class="nav-item" role="presentation">
                <a class="nav-link" id="read-tab" data-toggle="tab" href="#read" role="tab" aria-controls="read" aria-selected="false">
                    Read
                    <span class="badge badge-secondary">{{ readNotifications.length }}</span>
                </a>
            </li>
            <li class="nav-item" role="presentation">
                <a class="nav-link" id="persistent-tab" data-toggle="tab" href="#persistent" role="tab" aria-controls="persistent" aria-selected="false">
                    Persistent
                    <span class="badge badge-secondary">{{ persistentNotifications.length }}</span>
                </a>
            </li>
            <li class="nav-item" role="presentation">
                <a class="nav-link" id="non-persistent-tab" data-toggle="tab" href="#non-persistent" role="tab" aria-controls="non-persistent" aria-selected="false">
                    Non-Persistent
                    <span class="badge badge-secondary">{{ nonPersistentNotifications.length }}</span>
                </a>
            </li>
        </ul>
        <div class="tab-content mt-3 col overflow-hidden" id="message-tab-panel">
            <div class="tab-pane fade show active h-100" id="all" role="tabpanel" aria-labelledby="all-tab">
                <div class="d-flex flex-column h-100">
                    <div class="text-right">
                        <button type="button" class="btn btn-sm btn-success" :disabled="!notifications.length" @click="markAsRead(notifications)">
                            <i class="fas fa-fw fa-check"></i> Mark all as read
                        </button>
                        <button type="button" class="btn btn-sm btn-danger" :disabled="!notifications.length" @click="deleteAll(notifications)">
                            <i class="fas fa-fw fa-times"></i> Delete all
                        </button>
                    </div>
                    <div class="scroll-y-auto mt-2">
                        <notification-body :notf="n" :odd="!!(idx % 2)" v-for="(n, idx) in notifications" :key="`${n.id}_${idx}`"></notification-body>
                        <p class="py-2 px-3 mb-0 bg-light text-dark" v-if="!notifications.length">
                            You are up-to-date. No notifications for you!
                        </p>
                    </div>
                </div>
            </div>
            <div class="tab-pane fade h-100" id="unread" role="tabpanel" aria-labelledby="unread-tab">
                <div class="d-flex flex-column h-100">
                    <div class="text-right">
                        <button type="button" class="btn btn-sm btn-success" :disabled="!unreadNotifications.length" @click="markAsRead(unreadNotifications)">
                            <i class="fas fa-fw fa-check"></i> Mark all as read
                        </button>
                        <button type="button" class="btn btn-sm btn-danger" :disabled="!unreadNotifications.length" @click="deleteAll(unreadNotifications)">
                            <i class="fas fa-fw fa-times"></i> Delete all
                        </button>
                    </div>
                    <div class="scroll-y-auto mt-2">
                        <notification-body :notf="n" :odd="!!(idx % 2)" v-for="(n, idx) in unreadNotifications" :key="`${n.id}_${idx}`"></notification-body>
                        <p class="py-2 px-3 mb-0 bg-light-dark text-dark" v-if="!unreadNotifications.length">
                            You are up-to-date. No new notifications for you!
                        </p>
                    </div>
                </div>
            </div>
            <div class="tab-pane fade h-100" id="read" role="tabpanel" aria-labelledby="read-tab">
                <div class="d-flex flex-column h-100">
                    <div class="text-right">
                        <button type="button" class="btn btn-sm btn-danger" :disabled="!readNotifications.length" @click="deleteAll(readNotifications)">
                            <i class="fas fa-fw fa-times"></i> Delete all
                        </button>
                    </div>
                    <div class="scroll-y-auto mt-2">
                        <notification-body :notf="n" :odd="!!(idx % 2)" v-for="(n, idx) in readNotifications" :key="`${n.id}_${idx}`"></notification-body>
                        <p class="py-2 px-3 mb-0 bg-light text-dark" v-if="!readNotifications.length">
                            You are up-to-date. No notifications for you!
                        </p>
                    </div>
                </div>
            </div>
            <div class="tab-pane fade h-100" id="persistent" role="tabpanel" aria-labelledby="persistent-tab">
                <div class="d-flex flex-column h-100">
                    <div class="text-right">
                        <button type="button" class="btn btn-sm btn-success" :disabled="!persistentNotifications.length" @click="markAsRead(persistentNotifications)">
                            <i class="fas fa-fw fa-check"></i> Mark all as read
                        </button>
                    </div>
                    <div class="scroll-y-auto mt-2">
                    <notification-body :notf="n" :odd="!!(idx % 2)" v-for="(n, idx) in persistentNotifications" :key="`${n.id}_${idx}`"></notification-body>
                    <p class="py-2 px-3 mb-0 bg-light text-dark" v-if="!persistentNotifications.length">
                        No persistent notifications. No action to take for you!
                    </p>
                    </div>
                </div>
            </div>
            <div class="tab-pane fade h-100" id="non-persistent" role="tabpanel" aria-labelledby="non-persistent-tab">
                <div class="d-flex flex-column h-100">
                    <div class="text-right">
                        <button type="button" class="btn btn-sm btn-success" :disabled="!nonPersistentNotifications.length" @click="markAsRead(nonPersistentNotifications)">
                            <i class="fas fa-fw fa-check"></i> Mark all as read
                        </button>
                        <button type="button" class="btn btn-sm btn-danger" :disabled="!nonPersistentNotifications.length" @click="deleteAll(nonPersistentNotifications)">
                            <i class="fas fa-fw fa-times"></i> Delete all
                        </button>
                    </div>
                    <div class="scroll-y-auto mt-2">
                        <notification-body :notf="n" :odd="!!(idx % 2)" v-for="(n, idx) in nonPersistentNotifications" :key="`${n.id}_${idx}`"></notification-body>
                        <p class="py-2 px-3 mb-0 bg-light text-dark" v-if="!nonPersistentNotifications.length">
                            You are up-to-date. No notifications for you!
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>

<script>
    import NotificationBody from './NotificationBody.vue';

    export default {
        mounted() {
        },
        components: {
            'notification-body': NotificationBody,
        },
        methods: {
            markAsRead(notifications) {
                for(let i=0; i<notifications.length; i++) {
                    notifications[i].read_at = new Date();
                }
            },
            deleteAll(ns) {
                const ids = ns.map(n => n.id);
                for(let i=0; i<ids.length; i++) {
                    const idx = this.notifications.findIndex(n => n.id == ids[i] && (n.data && !n.data.persistent));
                    if(idx >= 0) {
                        this.notifications.splice(idx, 1);
                    }
                }
            }
        },
        data() {
            return {
            }
        },
        computed: {
            notifications() {
                return this.$userNotifications();
            },
            unreadNotifications() {
                return this.notifications.filter(n => !n.read_at);
            },
            readNotifications() {
                return this.notifications.filter(n => !!n.read_at);
            },
            persistentNotifications() {
                return this.notifications.filter(n => n.data && n.data.persistent);
            },
            nonPersistentNotifications() {
                return this.notifications.filter(n => !n.data || !n.data.persistent);
            }
        }
    }
</script>
