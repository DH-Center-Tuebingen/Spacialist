<template>
    <div class="mt-2">
        <div v-if="!state.isUserModerated">
            <div class="my-2 d-flex flex-row justify-content-between">
                <div class="d-flex flex-row gap-2">
                    <template v-if="value.moderation_state == 'pending-delete'">
                        <span>
                            <i class="fas fa-fw fa-trash" />
                            {{
                                t('main.entity.attributes.moderation.deleted_by', {
                                    ts: value.updated_at
                                })
                            }}
                        </span>
                    </template>
                    <template v-else>
                        <span>
                            <i class="fas fa-fw fa-edit" />
                            {{
                                t('main.entity.attributes.moderation.modified_by', {
                                    ts: value.updated_at
                                })
                            }}
                        </span>
                    </template>
                    <span>-</span>
                    <a
                        href="#"
                        class="text-nowrap text-reset text-decoration-none"
                        @click.prevent="showUserInfo(state.editorUser)"
                    >
                        <user-avatar
                            class="align-middle"
                            :user="state.editorUser"
                            :size="20"
                        />
                        <span class="align-middle ms-2">
                            {{ state.editorUser.name }}
                        </span>
                    </a>
                </div>
                <div class="dropdown">
                    <span
                        id="moderation-action-menu"
                        class="clickable"
                        data-bs-toggle="dropdown"
                        aria-haspopup="true"
                        aria-expanded="false"
                    >
                        <i class="fas fa-fw fa-ellipsis-h" />
                    </span>
                    <div
                        class="dropdown-menu"
                        aria-labelledby="moderation-action-menu"
                    >
                        <a
                            v-if="!state.isOriginalData"
                            class="dropdown-item"
                            href="#"
                            @click.prevent=""
                        >
                            <i class="fas fa-fw fa-user-edit text-info" />
                            {{ t('main.entity.attributes.moderation.edit_moderated_value.title') }}
                            <div class="submenu submenu-end dropdown-menu">
                                <a
                                    v-if="state.editingMode"
                                    class="dropdown-item"
                                    href="#"
                                    @click.prevent="handleEditModeratedValue('accept')"
                                >
                                    <i class="fas fa-fw fa-user-check text-success" />
                                    {{ t('main.entity.attributes.moderation.edit_moderated_value.accept') }}
                                </a>
                                <a
                                    v-if="!state.editingMode"
                                    class="dropdown-item"
                                    href="#"
                                    @click.prevent="handleEditModeratedValue('enable')"
                                >
                                    <i class="fas fa-fw fa-user-edit text-info" />
                                    {{ t('main.entity.attributes.moderation.edit_moderated_value.enable') }}
                                </a>
                                <a
                                    v-if="state.editingMode"
                                    class="dropdown-item"
                                    href="#"
                                    @click.prevent="handleEditModeratedValue('reset')"
                                >
                                    <i class="fas fa-fw fa-user-times text-warning" />
                                    {{ t('main.entity.attributes.moderation.edit_moderated_value.reset') }}
                                </a>
                                <a
                                    v-if="state.editingMode"
                                    class="dropdown-item"
                                    href="#"
                                    @click.prevent="handleEditModeratedValue('cancel')"
                                >
                                    <i class="fas fa-fw fa-user-times text-danger" />
                                    {{ t('main.entity.attributes.moderation.edit_moderated_value.cancel') }}
                                </a>
                            </div>
                        </a>
                        <a
                            class="dropdown-item"
                            href="#"
                            @click.prevent="handleModeration('accept')"
                        >
                            <i class="fas fa-fw fa-user-check text-success" />
                            {{ t('main.entity.attributes.moderation.accept_changes') }}
                        </a>
                        <a
                            class="dropdown-item"
                            href="#"
                            @click.prevent="handleModeration('deny')"
                        >
                            <i class="fas fa-fw fa-user-times text-danger" />
                            {{ t('main.entity.attributes.moderation.deny_changes') }}
                        </a>
                    </div>
                </div>
            </div>
            <button
                v-show="!state.editingMode"
                type="button"
                class="btn btn-info"
                @click="toggleDataDisplay()"
            >
                <i class="fas fa-fw fa-paste me-1" />
                <span v-if="state.isOriginalData">
                    {{ t('main.entity.attributes.moderation.view_modified_data') }}
                </span>
                <span v-else>
                    {{ t('main.entity.attributes.moderation.view_original_data') }}
                </span>
            </button>
        </div>
        <p
            v-else
            class="alert alert-warning m-0"
        >
            {{ t('main.entity.attributes.moderation.locked_state_info') }}
        </p>
    </div>
</template>

<script>
    import {
        computed,
        onMounted,
        reactive,
        toRefs,
    } from 'vue';

    import { useI18n } from 'vue-i18n';

    import useEntityStore from '@/bootstrap/stores/entity.js';
    import useUserStore from '@/bootstrap/stores/user.js';

    import {
        showUserInfo,
    } from '@/helpers/modal.js';

    export default {
        props: {
            element: {
                required: true,
                type: Object,
            },
            value: {
                required: true,
                type: Object
            }
        },
        emits: ['toggle-data', 'edit', 'moderate'],
        setup(props, context) {
            const { t } = useI18n();
            const entityStore = useEntityStore();
            const userStore = useUserStore();
            const {
                element,
                value,
            } = toRefs(props);

            const handleModeration = action => {
                context.emit('moderate', {
                    action: action,
                    entity_id: state.entity.id,
                    active: state.isOriginalData ? 'original' : 'moderation',
                });
            };
            const handleEditModeratedValue = action => {
                if(action == 'enable') {
                    state.editingMode = true;
                } else if(action == 'reset') {
                    //
                } else {
                    state.editingMode = false;
                }
                context.emit('edit', {
                    action: action,
                    entity_id: state.entity.id,
                });
            };
            const toggleDataDisplay = _ => {
                if(state.editingMode) {
                    return;
                }
                state.isOriginalData = !state.isOriginalData;
                context.emit('toggle-data');
            };

            const state = reactive({
                isOriginalData: false,
                editingMode: false,
                entity: computed(_ => entityStore.selectedEntity),
                isUserModerated: computed(_ => userStore.getUserModerated),
                editorUser: computed(_ => userStore.getUserBy(value.value.user_id)),
            });

            // RETURN
            return {
                t,
                // HELPERS
                showUserInfo,
                // LOCAL
                handleModeration,
                handleEditModeratedValue,
                toggleDataDisplay,
                // PROPS
                element,
                value,
                // STATE
                state,
            };
        },
    }
</script>
