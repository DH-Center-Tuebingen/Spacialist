<template>
    <div class="container d-flex flex-column overflow-hidden h-100">
        <h2>
            {{ t('global.notifications.title') }}
        </h2>
        <ul
            id="message-tab-list"
            class="nav nav-tabs nav-fill"
            role="tablist"
        >
            <li
                class="nav-item"
                role="presentation"
            >
                <a
                    id="all-tab"
                    class="nav-link active"
                    data-bs-toggle="tab"
                    href="#all"
                    role="tab"
                    aria-controls="all"
                    aria-selected="true"
                >
                    {{ t('global.notifications.tab_all') }}
                    <span class="badge badge-secondary">{{ state.notifications.length }}</span>
                </a>
            </li>
            <li
                class="nav-item"
                role="presentation"
            >
                <a
                    id="unread-tab"
                    class="nav-link"
                    data-bs-toggle="tab"
                    href="#unread"
                    role="tab"
                    aria-controls="unread"
                    aria-selected="false"
                >
                    {{ t('global.notifications.tab_unread') }}
                    <span class="badge badge-secondary">{{ state.unreadNotifications.length }}</span>
                </a>
            </li>
            <li
                class="nav-item"
                role="presentation"
            >
                <a
                    id="read-tab"
                    class="nav-link"
                    data-bs-toggle="tab"
                    href="#read"
                    role="tab"
                    aria-controls="read"
                    aria-selected="false"
                >
                    {{ t('global.notifications.tab_read') }}
                    <span class="badge badge-secondary">{{ state.readNotifications.length }}</span>
                </a>
            </li>
            <li
                class="nav-item"
                role="presentation"
            >
                <a
                    id="system-tab"
                    class="nav-link"
                    data-bs-toggle="tab"
                    href="#system"
                    role="tab"
                    aria-controls="system"
                    aria-selected="false"
                >
                    {{ t('global.notifications.tab_system') }}
                    <span class="badge badge-secondary">{{ state.systemNotifications.length }}</span>
                </a>
            </li>
        </ul>
        <div
            id="message-tab-panel"
            class="tab-content mt-3 col overflow-hidden"
        >
            <div
                id="all"
                class="tab-pane fade show active h-100"
                role="tabpanel"
                aria-labelledby="all-tab"
            >
                <div class="d-flex flex-column h-100">
                    <div class="text-end">
                        <button
                            type="button"
                            class="btn btn-sm btn-outline-success"
                            :disabled="!state.notifications.length"
                            @click="markListAsRead(state.notifications)"
                        >
                            <i class="fas fa-fw fa-check" />
                            {{ t('global.notifications.mark_all_as_read') }}
                        </button>
                        <button
                            type="button"
                            class="btn btn-sm btn-outline-danger ms-1"
                            :disabled="!state.notifications.length"
                            @click="deleteAll(state.notifications)"
                        >
                            <i class="fas fa-fw fa-times" />
                            {{ t('global.notifications.delete_all') }}
                        </button>
                    </div>
                    <div class="overflow-y-auto mt-2">
                        <notification-body
                            v-for="(n, idx) in state.notifications"
                            :key="`${n.id}_${idx}`"
                            :avatar="48"
                            :notf="n"
                            :odd="!!(idx % 2)"
                            @read="markAsRead"
                            @delete="deleteNotification"
                        />
                        <p
                            v-if="!state.notifications.length"
                            class="py-2 px-3 mb-0 bg-light-dark text-dark rounded"
                        >
                            <i class="fas fa-fw fa-check text-success" />
                            {{ t('global.notifications.tab_default_empty_list') }}
                        </p>
                    </div>
                </div>
            </div>
            <div
                id="unread"
                class="tab-pane fade h-100"
                role="tabpanel"
                aria-labelledby="unread-tab"
            >
                <div class="d-flex flex-column h-100">
                    <div class="text-end">
                        <button
                            type="button"
                            class="btn btn-sm btn-outline-success"
                            :disabled="!state.unreadNotifications.length"
                            @click="markListAsRead(state.unreadNotifications)"
                        >
                            <i class="fas fa-fw fa-check" />
                            {{ t('global.notifications.mark_all_as_read') }}
                        </button>
                        <button
                            type="button"
                            class="btn btn-sm btn-outline-danger ms-1"
                            :disabled="!state.unreadNotifications.length"
                            @click="deleteAll(state.unreadNotifications)"
                        >
                            <i class="fas fa-fw fa-times" />
                            {{ t('global.notifications.delete_all') }}
                        </button>
                    </div>
                    <div class="overflow-y-auto mt-2">
                        <notification-body
                            v-for="(n, idx) in state.unreadNotifications"
                            :key="`${n.id}_${idx}`"
                            :avatar="48"
                            :notf="n"
                            :odd="!!(idx % 2)"
                            @read="markAsRead"
                            @delete="deleteNotification"
                        />
                        <p
                            v-if="!state.unreadNotifications.length"
                            class="py-2 px-3 mb-0 bg-light-dark text-dark rounded"
                        >
                            <i class="fas fa-fw fa-check text-success" />
                            {{ t('global.notifications.tab_unread_empty_list') }}
                        </p>
                    </div>
                </div>
            </div>
            <div
                id="read"
                class="tab-pane fade h-100"
                role="tabpanel"
                aria-labelledby="read-tab"
            >
                <div class="d-flex flex-column h-100">
                    <div class="text-end">
                        <button
                            type="button"
                            class="btn btn-sm btn-outline-danger"
                            :disabled="!state.readNotifications.length"
                            @click="deleteAll(state.readNotifications)"
                        >
                            <i class="fas fa-fw fa-times" />
                            {{ t('global.notifications.delete_all') }}
                        </button>
                    </div>
                    <div class="overflow-y-auto mt-2">
                        <notification-body
                            v-for="(n, idx) in state.readNotifications"
                            :key="`${n.id}_${idx}`"
                            :avatar="48"
                            :notf="n"
                            :odd="!!(idx % 2)"
                            @read="markAsRead"
                            @delete="deleteNotification"
                        />
                        <p
                            v-if="!state.readNotifications.length"
                            class="py-2 px-3 mb-0 bg-light-dark text-dark rounded"
                        >
                            <i class="fas fa-fw fa-check text-success" />
                            {{ t('global.notifications.tab_default_empty_list') }}
                        </p>
                    </div>
                </div>
            </div>
            <div
                id="system"
                class="tab-pane fade h-100"
                role="tabpanel"
                aria-labelledby="system-tab"
            >
                <div class="d-flex flex-column h-100">
                    <div class="text-end">
                        <button
                            type="button"
                            class="btn btn-sm btn-outline-success"
                            :disabled="!state.systemNotifications.length"
                            @click="markListAsRead(state.systemNotifications)"
                        >
                            <i class="fas fa-fw fa-check" />
                            {{ t('global.notifications.mark_all_as_read') }}
                        </button>
                    </div>
                    <div class="overflow-y-auto mt-2">
                        <notification-body
                            v-for="(n, idx) in state.systemNotifications"
                            :key="`${n.id}_${idx}`"
                            :avatar="48"
                            :notf="n"
                            :odd="!!(idx % 2)"
                            @read="markAsRead"
                            @delete="deleteNotification"
                        />
                        <p
                            v-if="!state.systemNotifications.length"
                            class="py-2 px-3 mb-0 bg-light-dark text-dark rounded"
                        >
                            <i class="fas fa-fw fa-check text-success" />
                            {{ t('global.notifications.tab_system_empty_list') }}
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>

<script>
    import {
        computed,
        reactive,
    } from 'vue';

    import { useI18n } from 'vue-i18n';

    import {
        userNotifications,
    } from '@/helpers/helpers.js';

    import {
        markAsRead,
        markAllAsRead,
        deleteNotification,
        deleteAllNotifications,
    } from '@/api/notification.js';

    export default {
        setup(props, context) {
            const { t } = useI18n();

            // FETCH
            // DATA
            const state = reactive({
                notifications: computed(_ => userNotifications()),
                unreadNotifications: computed(_ => {
                    return state.notifications.filter(n => !n.read_at);
                }),
                readNotifications: computed(_ => {
                    return state.notifications.filter(n => !!n.read_at);
                }),
                systemNotifications: computed(_ => {
                    return state.notifications.filter(n => n.data && n.data.metadata.persistence === 'system');
                }),
            });
            // FUNCTIONS
            const markListAsRead = notifications => {
                markAllAsRead(notifications.map(elem => elem.id));
            };
            const deleteAll = notifications => {
                const ids = notifications
                    .filter(elem => elem.data && elem.data.metadata.persistence === 'none')
                    .map(elem => elem.id);
                deleteAllNotifications(ids);
            };

            // RETURN
            return {
                t,
                // HELPERS
                markAsRead,
                deleteNotification,
                // LOCAL
                markListAsRead,
                deleteAll,
                // PROPS
                // STATE
                state,
            };
        },
    };
</script>
