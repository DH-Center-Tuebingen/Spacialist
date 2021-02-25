<template>
  <vue-final-modal
    classes="modal-container"
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
        <div class="alert alert-info" role="alert" v-html="t('global.delete-name.desc', {name: state.data.title})"></div>
        <div class="alert alert-danger" role="alert">
            {{
                t('main.bibliography.modal.delete.alert', {
                    name: state.data.title,
                    cnt: state.data.count,
                }, state.data.count)
            }}
        </div>
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
                data: props.data,
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
