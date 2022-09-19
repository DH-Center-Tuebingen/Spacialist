<template>
    <vue-final-modal
        classes="modal-container modal"
        content-class="sp-modal-content sp-modal-content-sm"
        v-model="state.show"
        name="save-screencast-modal">
        <div class="modal-header">
            <h5 class="modal-title">
                {{
                    t('main.app.screencast.title')
                }}
            </h5>
            <button type="button" class="btn-close" aria-label="Close" data-bs-dismiss="modal" @click="closeModal()">
            </button>
        </div>
        <div class="modal-body">
            <form id="storeScreencastModal" name="storeScreencastModal" role="form">
                <p class="alert alert-info">
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
                </p>
            </form>
        </div>
        <div class="modal-footer">
            <button type="button" class="btn btn-outline-success" @click="storeLocal()">
                <i class="fas fa-fw fa-file-download"></i> {{ t('main.app.screencast.actions.local.button') }}
            </button>
            <button type="button" class="btn btn-outline-success" v-if="state.filePluginInstalled" @click="storeOnServer()">
                <i class="fas fa-fw fa-file-upload"></i> {{ t('main.app.screencast.actions.server.button') }}
            </button>
            <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal" @click="closeModal()">
                <i class="fas fa-fw fa-times"></i> {{ t('global.close') }}
            </button>
        </div>
    </vue-final-modal>
</template>

<script>
    import {
        onMounted,
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
                state.show = false;
                context.emit('confirm', false);
            };
            const storeOnServer = _ => {
                if(hasPlugin('File')) {
                    SpPS.data.plugins.file.api.uploadFile(state.content, {}, getFilename());
                };
                state.show = false;
                context.emit('confirm', false);
            };
            const closeModal = _ => {
                state.show = false;
                context.emit('closing', false);
            };

            // DATA
            const state = reactive({
                show: false,
                content: new File([data.value.data], data.value.data.name, {
                    type: data.value.data.type
                }),
                duration: data.value.duration,
                filePluginInstalled: hasPlugin('File'),
            });

            // ON MOUNTED
            onMounted(_ => {
                state.show = true;
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
