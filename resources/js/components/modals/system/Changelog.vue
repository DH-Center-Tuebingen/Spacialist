<template>
    <vue-final-modal
        class="modal-container modal"
        content-class="sp-modal-content"
        name="user-info-modal"
    >
        <div class="sp-modal-content">
            <div class="modal-header">
                <h5 class="modal-title">
                    {{
                        t('main.plugins.modal.changelog_title')
                    }}
                    <small>
                        {{ plugin.name }}
                    </small>
                </h5>
                <button
                    type="button"
                    class="btn-close"
                    aria-label="Close"
                    data-bs-dismiss="modal"
                    @click="closeModal()"
                />
            </div>
            <div class="modal-body overflow-hidden d-flex flex-row">
                <div class="rounded-4 ps-4 pe-0 py-3 bg-primary bg-opacity-10 overflow-hidden col d-flex flex-row">
                    <md-viewer
                        class="font-monospace col scroll-y-auto pe-4"
                        :source="plugin.changelog"
                    />
                </div>
            </div>
            <div class="modal-footer">
                <button
                    type="button"
                    class="btn btn-outline-secondary"
                    data-bs-dismiss="modal"
                    @click="closeModal()"
                >
                    <i class="fas fa-fw fa-times" /> {{ t('global.close') }}
                </button>
            </div>
        </div>
    </vue-final-modal>
</template>
  
<script>
    import {
        reactive,
        toRefs,
    } from 'vue';
    import { useI18n } from 'vue-i18n';

    export default {
        props: {
        plugin: {
                required: true,
                type: Object,
            },
        },
        emits: ['closing'],
        setup(props, context) {
            const { t } = useI18n();
            const {
                plugin
            } = toRefs(props);

            // FUNCTIONS
            const closeModal = _ => {
                context.emit('closing', false);
            }

            // DATA
            const state = reactive({
            });

            // RETURN
            return {
                t,
                // HELPERS
                // LOCAL
                closeModal,
                // STATE
                state,
            }
        },
    }
</script>
