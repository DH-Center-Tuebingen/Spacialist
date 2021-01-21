<template>
    <div class="container d-flex flex-column overflow-hidden h-100">
        <h2>
            {{ $t('global.notifications.title') }}
        </h2>
        <ul class="nav nav-tabs nav-fill" id="message-tab-list" role="tablist">
            <li class="nav-item" role="presentation">
                <a class="nav-link active" id="all-tab" data-bs-toggle="tab" href="#all" role="tab" aria-controls="all" aria-selected="true">
                    {{ $t('global.notifications.tab_all') }}
                    <span class="badge badge-secondary">{{ notifications.length }}</span>
                </a>
            </li>
            <li class="nav-item" role="presentation">
                <a class="nav-link" id="unread-tab" data-bs-toggle="tab" href="#unread" role="tab" aria-controls="unread" aria-selected="false">
                    {{ $t('global.notifications.tab_unread') }}
                    <span class="badge badge-secondary">{{ unreadNotifications.length }}</span>
                </a>
            </li>
            <li class="nav-item" role="presentation">
                <a class="nav-link" id="read-tab" data-bs-toggle="tab" href="#read" role="tab" aria-controls="read" aria-selected="false">
                    {{ $t('global.notifications.tab_read') }}
                    <span class="badge badge-secondary">{{ readNotifications.length }}</span>
                </a>
            </li>
            <li class="nav-item" role="presentation">
                <a class="nav-link" id="system-tab" data-bs-toggle="tab" href="#system" role="tab" aria-controls="system" aria-selected="false">
                    {{ $t('global.notifications.tab_system') }}
                    <span class="badge badge-secondary">{{ systemNotifications.length }}</span>
                </a>
            </li>
        </ul>
        <div class="tab-content mt-3 col overflow-hidden" id="message-tab-panel">
            <div class="tab-pane fade show active h-100" id="all" role="tabpanel" aria-labelledby="all-tab">
                <div class="d-flex flex-column h-100">
                    <div class="text-end">
                        <button type="button" class="btn btn-sm btn-success" :disabled="!notifications.length" @click="markListAsRead(notifications)">
                            <i class="fas fa-fw fa-check"></i>
                            {{ $t('global.notifications.mark_all_as_read') }}
                        </button>
                        <button type="button" class="btn btn-sm btn-danger" :disabled="!notifications.length" @click="deleteAll(notifications)">
                            <i class="fas fa-fw fa-times"></i>
                            {{ $t('global.notifications.delete_all') }}
                        </button>
                    </div>
                    <div class="scroll-y-auto mt-2">
                        
                        <notification-body
                            v-for="(n, idx) in notifications"
                            :key="`${n.id}_${idx}`"
                            :avatar="48"
                            :notf="n"
                            :odd="!!(idx % 2)"
                            @read="markAsRead"
                            @delete="deleteNotification">
                        </notification-body>
                        <p class="py-2 px-3 mb-0 bg-light-dark text-dark rounded" v-if="!notifications.length">
                            <i class="fas fa-fw fa-check text-success"></i>
                            {{ $t('global.notifications.tab_default_empty_list') }}
                        </p>
                    </div>
                </div>
            </div>
            <div class="tab-pane fade h-100" id="unread" role="tabpanel" aria-labelledby="unread-tab">
                <div class="d-flex flex-column h-100">
                    <div class="text-end">
                        <button type="button" class="btn btn-sm btn-success" :disabled="!unreadNotifications.length" @click="markListAsRead(unreadNotifications)">
                            <i class="fas fa-fw fa-check"></i>
                            {{ $t('global.notifications.mark_all_as_read') }}
                        </button>
                        <button type="button" class="btn btn-sm btn-danger" :disabled="!unreadNotifications.length" @click="deleteAll(unreadNotifications)">
                            <i class="fas fa-fw fa-times"></i>
                            {{ $t('global.notifications.delete_all') }}
                        </button>
                    </div>
                    <div class="scroll-y-auto mt-2">
                        <notification-body
                            v-for="(n, idx) in unreadNotifications"
                            :key="`${n.id}_${idx}`"
                            :avatar="48"
                            :notf="n"
                            :odd="!!(idx % 2)"
                            @read="markAsRead"
                            @delete="deleteNotification">
                        </notification-body>
                        <p class="py-2 px-3 mb-0 bg-light-dark text-dark rounded" v-if="!unreadNotifications.length">
                            <i class="fas fa-fw fa-check text-success"></i>
                            {{ $t('global.notifications.tab_unread_empty_list') }}
                        </p>
                    </div>
                </div>
            </div>
            <div class="tab-pane fade h-100" id="read" role="tabpanel" aria-labelledby="read-tab">
                <div class="d-flex flex-column h-100">
                    <div class="text-end">
                        <button type="button" class="btn btn-sm btn-danger" :disabled="!readNotifications.length" @click="deleteAll(readNotifications)">
                            <i class="fas fa-fw fa-times"></i>
                            {{ $t('global.notifications.delete_all') }}
                        </button>
                    </div>
                    <div class="scroll-y-auto mt-2">
                        <notification-body
                            v-for="(n, idx) in readNotifications"
                            :key="`${n.id}_${idx}`"
                            :avatar="48"
                            :notf="n"
                            :odd="!!(idx % 2)"
                            @read="markAsRead"
                            @delete="deleteNotification">
                        </notification-body>
                        <p class="py-2 px-3 mb-0 bg-light-dark text-dark rounded" v-if="!readNotifications.length">
                            <i class="fas fa-fw fa-check text-success"></i>
                            {{ $t('global.notifications.tab_default_empty_list') }}
                        </p>
                    </div>
                </div>
            </div>
            <div class="tab-pane fade h-100" id="system" role="tabpanel" aria-labelledby="system-tab">
                <div class="d-flex flex-column h-100">
                    <div class="text-end">
                        <button type="button" class="btn btn-sm btn-success" :disabled="!systemNotifications.length" @click="markListAsRead(systemNotifications)">
                            <i class="fas fa-fw fa-check"></i>
                            {{ $t('global.notifications.mark_all_as_read') }}
                        </button>
                    </div>
                    <div class="scroll-y-auto mt-2">
                        
                        <notification-body
                            v-for="(n, idx) in systemNotifications"
                            :key="`${n.id}_${idx}`"
                            :avatar="48"
                            :notf="n"
                            :odd="!!(idx % 2)"
                            @read="markAsRead"
                            @delete="deleteNotification">
                        </notification-body>
                        <p class="py-2 px-3 mb-0 bg-light-dark text-dark rounded" v-if="!systemNotifications.length">
                            <i class="fas fa-fw fa-check text-success"></i>
                            {{ $t('global.notifications.tab_system_empty_list') }}
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>

<script>
    export default {
        mounted() {
        },
        methods: {
            markAsRead(event) {
                this.$markAsRead(event.id);
            },
            markListAsRead(notifications) {
                const ids = notifications.map(elem => elem.id);
                this.$markAllAsRead(ids);
            },
            deleteNotification(event) {
                this.$deleteNotification(event.id);
            },
            deleteAll(notifications) {
                const ids = notifications
                    .filter(elem => elem.data && elem.data.metadata.persistence === 'none')
                    .map(elem => elem.id);
                this.$deleteAllNotifications(ids);
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
            systemNotifications() {
                return this.notifications.filter(n => n.data && n.data.metadata.persistence === 'system');
            },
        }
    }
</script>
