<template>
    <Modal
        :title="t('global.delete_name.title', { name: state.entity.name })"
        :confirm-text="t('global.delete')"
        @confirm="confirmDelete"
        @close="closeModal"
    >
        <alert
            :class="{ 'mb-0': !state.needsAlert }"
            :message="t('global.delete_name.desc', { name: state.entity.name })"
            :type="'info'"
            :noicon="true"
        />
        <alert
            v-if="state.needsAlert"
            :message="t('main.entity.modals.delete.alert', {
                name: state.entity.name,
                cnt: state.count
            }, state.count)"
            :type="'warning'"
            :noicon="false"
            :icontext="t('global.note')"
        />
    </Modal>
</template>

<script>
    import {
        computed,
        reactive,
        toRefs,
    } from 'vue';
    import { useI18n } from 'vue-i18n';

    import store from '@/bootstrap/store.js';
    import Modal from '../Modal.vue';

    export default {
        components: {
            Modal,
        },
        props: {
            entityId: {
                type: Number,
                required: true,
            },
        },
        emits: ['closing', 'confirm'],
        setup(props, context) {
            const { t } = useI18n();
            const {
                entityId,
            } = toRefs(props);

            // FUNCTIONS
            const confirmDelete = _ => {
                context.emit('confirm', false);
            };
            const closeModal = _ => {
                context.emit('closing', false);
            };

            // DATA
            const state = reactive({
                entity: computed(_ => store.getters.entities[entityId.value] || {}),
                count: computed(_ => state.entity.children ? state.entity.children.length : 0),
                needsAlert: computed(_ => state.count > 0),
            });

            // RETURN
            return {
                t,
                // HELPERS
                // PROPS
                // LOCAL
                confirmDelete,
                closeModal,
                // STATE
                state,
            };
        },
    };
</script>