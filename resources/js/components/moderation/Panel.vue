<template>
    <div class="mt-2">
        <div v-if="!state.isUserModerated">
            <div class="my-2 d-flex flex-row justify-content-between">
                <div class="d-flex flex-row gap-2">
                    <span v-if="value.moderation_state == 'pending-delete'"
                        v-html="t('main.entity.attributes.moderation.deleted_by', {
                            ts: value.updated_at
                        })"
                    />
                    <span v-else
                        v-html="t('main.entity.attributes.moderation.modified_by', {
                            ts: value.updated_at
                        })"
                    />
                    <span>-</span>
                    <a href="#" @click.prevent="showUserInfo(state.editorUser)" class="text-nowrap text-reset text-decoration-none">
                        <user-avatar class="align-middle" :user="state.editorUser" :size="20"></user-avatar>
                        <span class="align-middle ms-2">
                            {{ state.editorUser.name }}
                        </span>
                    </a>
                </div>
                <div class="dropdown">
                    <span id="moderation-action-menu" class="clickable" data-bs-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                        <i class="fas fa-fw fa-ellipsis-h"></i>
                    </span>
                    <div class="dropdown-menu" aria-labelledby="moderation-action-menu">
                        <a class="dropdown-item" href="#" @click.prevent="" v-if="!state.isOriginalData">
                            <i class="fas fa-fw fa-user-edit text-info"></i>
                            {{ t('main.entity.attributes.moderation.edit_moderated_value.title') }}
                            <div class="submenu submenu-end dropdown-menu">
                                <a class="dropdown-item" href="#" @click.prevent="handleEditModeratedValue('accept')" v-if="state.editingMode">
                                    <i class="fas fa-fw fa-user-check text-success"></i>
                                    {{ t('main.entity.attributes.moderation.edit_moderated_value.accept') }}
                                </a>
                                <a class="dropdown-item" href="#" @click.prevent="handleEditModeratedValue('enable')" v-if="!state.editingMode">
                                    <i class="fas fa-fw fa-user-edit text-info"></i>
                                    {{ t('main.entity.attributes.moderation.edit_moderated_value.enable') }}
                                </a>
                                <a class="dropdown-item" href="#" @click.prevent="handleEditModeratedValue('reset')" v-if="state.editingMode">
                                    <i class="fas fa-fw fa-user-times text-warning"></i>
                                    {{ t('main.entity.attributes.moderation.edit_moderated_value.reset') }}
                                </a>
                                <a class="dropdown-item" href="#" @click.prevent="handleEditModeratedValue('cancel')" v-if="state.editingMode">
                                    <i class="fas fa-fw fa-user-times text-danger"></i>
                                    {{ t('main.entity.attributes.moderation.edit_moderated_value.cancel') }}
                                </a>
                            </div>
                        </a>
                        <a class="dropdown-item" href="#" @click.prevent="handleModeration('accept')">
                            <i class="fas fa-fw fa-user-check text-success"></i>
                            {{ t('main.entity.attributes.moderation.accept_changes') }}
                        </a>
                        <a class="dropdown-item" href="#" @click.prevent="handleModeration('deny')">
                            <i class="fas fa-fw fa-user-times text-danger"></i>
                            {{ t('main.entity.attributes.moderation.deny_changes') }}
                        </a>
                    </div>
                </div>
            </div>
            <button type="button" class="btn btn-info" @click="toggleDataDisplay()" v-show="!state.editingMode">
                <i class="fas fa-fw fa-paste me-1"></i>
                <span v-if="state.isOriginalData">
                    {{ t('main.entity.attributes.moderation.view_modified_data') }}
                </span>
                <span v-else>
                    {{ t('main.entity.attributes.moderation.view_original_data') }}
                </span>
            </button>
        </div>
        <p v-else class="alert alert-warning m-0">
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

    import store from '@/bootstrap/store.js';

    import {
        getUserBy,
    } from '@/helpers/helpers.js';

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
                entity: computed(_ => store.getters.entity),
                isUserModerated: computed(_ => store.getters.isModerated),
                editorUser: computed(_ => getUserBy(value.value.user_id)),
            });

            // RETURN
            return {
                t,
                // HELPERS
                getUserBy,
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
