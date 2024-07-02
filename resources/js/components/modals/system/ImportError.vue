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
                    :icontext="'Error Message'"
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

                    const message = data?.value?.message || 'Unknown error';
                    const on_index = data?.value?.data?.on_index || null;
                    const on = data?.value?.data?.on || '"unknown"';
                    const on_value = data?.value?.data?.on_value || null;
                    const count = data?.value?.data?.count || '"unknown"';
                    const entry = data?.value?.data?.entry || '"unknown"';

                    let msg = `<div class='lead'>
                        ${message}
                    </div>
                    <hr/>`;
                    if(on_index && on_value) {
                        msg += `<div>
                            Error while importing entry in line ${count} (<span class='fst-italic'>${entry}</span>) while parsing column ${on_index} (<span class='fst-italic'>${on}</span>) with invalid value <span class='fw-bold'>${on_value}</span>
                        </div>`;
                    } else {
                        msg += `<div>
                            Error while importing entry in line ${count} (<span class='fst-italic'>${entry}</span>): <span class='fw-bold'>${on}</span>
                        </div>`;
                    }
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
