<template>
    <vue-final-modal
        classes="modal-container"
        content-class="sp-modal-content sp-modal-content-sm"
        v-model="state.show"
        name="error-modal">
        <div class="modal-header">
            <h5 class="modal-title">
                {{
                    t('global.error.occur')
                }}
            </h5>
            <button type="button" class="btn-close" aria-label="Close" data-bs-dismiss="modal" @click="closeModal()">
            </button>
        </div>
        <div class="modal-body my-3">
            <alert
                :message="state.htmlMessage"
                :type="'error'"
                :noicon="false"
                :icontext="'Error Message'" />
        </div>
        <div class="modal-footer">
            <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal" @click="closeModal()">
                <i class="fas fa-fw fa-times"></i> {{ t('global.close') }}
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
                state.show = false;
                context.emit('closing', false);
            };

            // DATA
            const state = reactive({
                show: false,
                htmlMessage: computed(_ => {
                    let msg = `<div class='lead'>
                        ${data.value.message}
                    </div>
                    <hr/>`;
                    if(data.value.data.on_index && data.value.data.on_value) {
                        msg += `<div>
                            Error while importing entry in line ${data.value.data.count} (<span class='fst-italic'>${data.value.data.entry}</span>) while parsing column ${data.value.data.on_index} (<span class='fst-italic'>${data.value.data.on}</span>) with invalid value <span class='fw-bold'>${data.value.data.on_value}</span>
                        </div>`;
                    } else {
                        msg += `<div>
                            Error while importing entry in line ${data.value.data.count} (<span class='fst-italic'>${data.value.data.entry}</span>): <span class='fw-bold'>${data.value.data.on}</span>
                        </div>`;
                    }
                    return msg;
                })
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
                closeModal,
                // STATE
                state,
            }
        },
    }
</script>
