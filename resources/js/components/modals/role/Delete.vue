<template>
    <vue-final-modal
        class="modal-container modal"
        content-class="sp-modal-content sp-modal-content-sm"
        name="delete-entity-modal"
    >
        <div class="sp-modal-content sp-modal-content-sm">
            <div class="modal-header">
                <h5 class="modal-title">
                    {{ t('global.delete_name.title', {name: role.display_name}) }}
                </h5>
                <button
                    type="button"
                    class="btn-close"
                    aria-label="Close"
                    data-bs-dismiss="modal"
                    @click="closeModal()"
                />
            </div>
            <div class="modal-body">
                <alert
                    :class="{'mb-0': !state.needsAlert}"
                    :message="t('global.delete_name.desc', {name: role.display_name})"
                    :type="'info'"
                    :noicon="true"
                />
                <alert
                    v-if="state.needsAlert"
                    :message="t('main.role.modal.delete.alert', {
                        name: role.display_name,
                        cnt: state.count
                    }, state.count)"
                    :type="'warning'"
                    :noicon="false"
                    :icontext="t('global.note')"
                />
            </div>
            <div class="modal-footer">
                <button
                    type="button"
                    class="btn btn-outline-danger"
                    @click="confirmDelete()"
                >
                    <i class="fas fa-fw fa-check" /> {{ t('global.delete') }}
                </button>
                <button
                    type="button"
                    class="btn btn-outline-secondary"
                    data-bs-dismiss="modal"
                    @click="closeModal()"
                >
                    <i class="fas fa-fw fa-times" /> {{ t('global.cancel') }}
                </button>
            </div>
        </div>
    </vue-final-modal>
</template>

<script>
    import {
        computed,
        reactive,
        toRefs,
    } from 'vue';
    import { useI18n } from 'vue-i18n';

    import useUserStore from '@/bootstrap/stores/user.js';

    export default {
        props: {
            role: {
                type: Object,
                required: true,
            },
        },
        emits: ['cancel', 'confirm'],
        setup(props, context) {
            const { t } = useI18n();
            const userStore = useUserStore();
            const {
                role,
            } = toRefs(props);

            // FUNCTIONS
            const confirmDelete = _ => {
                context.emit('confirm', false);
            };
            const closeModal = _ => {
                context.emit('cancel', false);
            };

            // DATA
            const state = reactive({
                count: computed(_ => userStore.users.filter(u => u.roles.some(ur => ur.id == role.value.id)).length),
                needsAlert: computed(_ => state.count > 0),
            });

            // RETURN
            return {
                t,
                // HELPERS
                // LOCAL
                confirmDelete,
                closeModal,
                // STATE
                state,
            }
        },
    }
</script>