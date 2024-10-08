<template>
    <vue-final-modal
        class="modal-container modal"
        content-class="sp-modal-content sp-modal-content-sm"
        name="save-screencast-modal"
    >
        <div class="sp-modal-content sp-modal-content-sm">
            <div class="modal-header">
                <h5 class="modal-title">
                    {{
                        t('main.app.screencast.title')
                    }}
                </h5>
                <button
                    v-if="true"
                    type="button"
                    class="btn-close"
                    aria-label="Close"
                    data-bs-dismiss="modal"
                    @click="closeModal()"
                />
            </div>
            <div class="modal-body">
                <form
                    id="storeScreencastModal"
                    name="storeScreencastModal"
                    role="form"
                >
                    <div class="alert alert-info">
                        {{ t('main.app.screencast.info') }}
                        <dl class="row mb-0">
                            <dt class="col-6 text-right">
                                {{ t('global.duration') }}
                            </dt>
                            <dd class="col-6">
                                {{ time(state.duration) }}
                            </dd>
                            <dt class="col-6 text-right">
                                {{ t('global.size') }}
                            </dt>
                            <dd class="col-6">
                                {{ bytes(state.content.size) }}
                            </dd>
                            <dt class="col-6 text-right">
                                {{ t('global.type') }}
                            </dt>
                            <dd class="col-md-6">
                                {{ state.content.type }}
                            </dd>
                        </dl>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button
                    type="button"
                    class="btn btn-outline-success"
                    @click="storeLocal()"
                >
                    <i class="fas fa-fw fa-file-download" /> {{ t('main.app.screencast.actions.local.button') }}
                </button>
                <button
                    v-if="state.filePluginInstalled"
                    type="button"
                    class="btn btn-outline-success"
                    @click="storeOnServer()"
                >
                    <i class="fas fa-fw fa-file-upload" /> {{ t('main.app.screencast.actions.server.button') }}
                </button>
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

    import {
        bytes,
        time,
    } from '@/helpers/filters.js';

    import {
      createDownloadLink,
      getTs,
      hasPlugin,
    } from '@/helpers/helpers.js';

    export default {
        props: {
            data: {
                required: true,
                type: Object,
            },
        },
        emits: ['closing', 'confirm'],
        setup(props, context) {
            const {
                data,
            } = toRefs(props);
            const { t } = useI18n();

            // FUNCTIONS
            const getFilename = _ => `spacialist-screencapture-${getTs()}.webm`;
            const storeLocal = _ => {
                createDownloadLink(data.value.data, getFilename(), false, 'video/webm');
                context.emit('confirm', false);
            };
            const storeOnServer = _ => {
                if(hasPlugin('File')) {
                    SpPS.data.plugins.file.api.uploadFile(state.content, {}, getFilename());
                }
                context.emit('confirm', false);
            };
            const closeModal = _ => {
                context.emit('closing', false);
            };

            // DATA
            const state = reactive({
                content: new File([data.value.data], data.value.data.name, {
                    type: data.value.data.type
                }),
                duration: data.value.duration,
                filePluginInstalled: hasPlugin('File'),
            });

            // RETURN
            return {
                t,
                // HELPERS
                bytes,
                time,
                // PROPS
                // LOCAL
                storeLocal,
                storeOnServer,
                closeModal,
                // STATE
                state,
            }
        },
    }
</script>
