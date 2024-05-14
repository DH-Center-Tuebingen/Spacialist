<template>
    <vue-final-modal
        class="modal-container modal"
        content-class="sp-modal-content sp-modal-content-sm"
        name="error-modal"
    >
        <div class="sp-modal-content sp-modal-content-sm">
            <div class="modal-header">
                <h5 class="modal-title">
                    {{
                        t('global.error.occur')
                    }}
                </h5>
                <button
                    type="button"
                    class="btn-close"
                    aria-label="Close"
                    data-bs-dismiss="modal"
                    @click="closeModal()"
                />
            </div>
            <div class="modal-body my-3">
                <alert
                    :message="state.htmlMessage"
                    :type="'error'"
                    :noicon="false"
                    :icontext="t('global.error.alert_title')"
                />
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
                type: Object,
            },
        },
        emits: ['closing'],
        setup(props, context) {
            const { t } = useI18n();

            const {
                data,
            } = toRefs(props);

            // FUNCTIONS
            const closeModal = _ => {
                context.emit('closing', false);
            };

            // DATA
            const state = reactive({
                htmlMessage: computed(_ => {
                    const i18nMsg =
                        data.value.data.on_index && data.value.data.on_value
                        ?
                        t('main.importer.modal.error.msg_detail', {
                            count: data.value.data.count,
                            name: data.value.data.entry,
                            index: data.value.data.on_index,
                            col_name: data.value.data.on_name,
                            datatype: data.value.data.on,
                            value: data.value.data.on_value,
                        })
                        :
                        t('main.importer.modal.error.msg_detail', {
                            count: data.value.data.count,
                            name: data.value.data.entry,
                            value: data.value.data.on,
                        });

                    const msg =
                    `<div class='lead'>
                        ${data.value.message}
                    </div>
                    <hr/>
                    <div>
                        ${i18nMsg}
                    </div>
                    `;
                    return msg;
                })
            });

            // RETURN
            return {
                t,
                // HELPERS
                // PROPS
                // LOCAL
                closeModal,
                // STATE
                state,
            }
        },
    }
</script>
