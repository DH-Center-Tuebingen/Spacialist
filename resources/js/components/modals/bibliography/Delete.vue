<template>
  <vue-final-modal
    classes="modal-container modal"
    content-class="sp-modal-content sp-modal-content-sm"
    :lock-scroll="false"
    v-model="state.show"
    name="delete-bibliograpy-item-modal">
    <div class="modal-header">
        <h5 class="modal-title">
            {{ t('main.bibliography.modal.delete.title') }}
        </h5>
        <button type="button" class="btn-close" aria-label="Close" @click="closeModal()">
        </button>
    </div>
    <div class="modal-body">
        <alert
            :class="{'mb-0': !state.needsAlert}"
            :message="t('global.delete_name.desc', {name: state.title})"
            :type="'info'"
            :noicon="true" />
        <alert
            v-if="state.needsAlert"
            :message="t('main.bibliography.modal.delete.alert', {
                    name: state.title,
                    cnt: state.count,
                }, state.count)"
            :type="'warning'"
            :noicon="false"
            :icontext="t('global.note')" />
    </div>
    <div class="modal-footer">
        <button type="button" class="btn btn-outline-danger" @click="confirmDelete()">
            <i class="fas fa-fw fa-trash"></i> {{ t('global.delete') }}
        </button>
        <button type="button" class="btn btn-outline-secondary" @click="closeModal()">
            <i class="fas fa-fw fa-ban"></i> {{ t('global.cancel') }}
        </button>
    </div>
  </vue-final-modal>
</template>

<script>
    import {
        computed,
        onMounted,
        reactive,
        toRefs,
    } from 'vue';
    import { useI18n } from 'vue-i18n';

    export default {
        props: {
            data: {
                required: true,
                type: Object
            },
        },
        emits: ['delete', 'closing'],
        setup(props, context) {
            const { t } = useI18n();
            const {
                data,
            } = toRefs(props);

            // FUNCTIONS
            const confirmDelete = _ => {
                state.show = false;
                context.emit('delete', false);
            };
            const closeModal = _ => {
                state.show = false;
                context.emit('closing', false);
            };

            // DATA
            const state = reactive({
                show: false,
                title: computed(_ => data.value.title),
                count: computed(_ => data.value.count),
                needsAlert: computed(_ => state.count > 0),
            });

            // ON MOUNTED
            onMounted(_ => {
                state.show = true;
            });

            // RETURN
            return {
                t,
                // HELPERS
                // PROPS
                // LOCAL
                confirmDelete,
                closeModal,
                //STATE
                state,
            }
        },
    }
</script>
