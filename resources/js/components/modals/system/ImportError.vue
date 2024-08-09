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

    import {useI18n} from 'vue-i18n';

    export default {
        props: {
            data: {
                required: true,
                type: Object,
            },
        },
        emits: ['closing'],
        setup(props, context) {
            const {t} = useI18n();

            // FUNCTIONS
            const closeModal = _ => {
                context.emit('closing', false);
            };

            // DATA
            const state = reactive({
                htmlMessage: computed(_ => {
                    const data = props.data;
                    const isRowError = data?.data?.on_index && data?.data?.on_value;

                    const onIndex = data?.data?.on_index || 'N/A';
                    const onValue = data?.data?.on_value || 'N/A';
                    const onName = data?.data?.on_name || 'N/A';
                    const on = data?.data?.on || 'N/A';
                    const count = data?.data?.count || 'N/A';
                    const entry = data?.data?.entry || 'N/A';


                    const i18nMsg =
                        isRowError
                            ?
                            t('main.importer.modal.error.msg_detail', {
                                count: count,
                                name: entry,
                                index: onIndex,
                                col_name: onName,
                                datatype: on,
                                value: onValue,
                            })
                            :
                            t('main.importer.modal.error.msg_detail', {
                                count: count,
                                name: entry,
                                value: on,
                            });

                    const msg =
                        `<div class='lead'>
                        ${data.message}
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
            };
        },
    };
</script>
