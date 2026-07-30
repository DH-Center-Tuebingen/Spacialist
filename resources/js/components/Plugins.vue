<template>
    <div class="d-flex flex-column h-100">
        <header class="mb-3">
            <h4>
                {{ t('main.plugins.title', 2) }}
                <div class="float-end">
                    <file-upload
                        ref="uploadButton"
                        class="btn btn-sm btn-outline-primary clickable"
                        accept="application/zip"
                        extensions="zip"
                        :custom-action="uploadZip"
                        :directory="false"
                        :disabled="!can('preferences_create')"
                        :multiple="false"
                        :drop="true"
                        @input-file="inputFile"
                    >
                        <span>
                            <i class="fas fa-fw fa-file-import" /> {{ t('main.plugins.upload') }}
                        </span>
                    </file-upload>
                    <button
                        class="btn btn-sm btn-outline-secondary ms-2"
                        @click="pluginStore.refresh()"
                    >
                        <i class="fas fa-fw fa-sync" /> {{ t('global.refresh') }}
                    </button>
                </div>
            </h4>
        </header>
        <div class="container-fluid flex-fill overflow-y-auto">
            <LoadingContainer
                class="row g-3 "
                :loading="loading"
            >
                <Transition name="zoom">
                    <div
                        v-if="fileDragged"
                        class="position-fixed top-0 start-0 w-100 h-100 d-flex m-0"
                        style="z-index: 20;"
                    >
                        <div
                            class="file-drop-frame m-2 d-flex flex-fill justify-content-center align-items-center text-body-tertiary bg-light bg-opacity-75 border border-1 rounded"
                            style="border-style: dashed !important;"
                        >
                            <span class="d-flex justify-content-center align-items-center gap-3 p-1 px-4 rounded">
                                <i class="fas fa-fw fa-file-import fa-2x" />

                                <span
                                    class="fw-bold"
                                    style="font-size: 1.5rem;"
                                >
                                    {{ t("global.file_drop_here") }}
                                </span>
                            </span>
                        </div>
                    </div>
                </Transition>
                <Plugin
                    v-for="plugin in pluginStore.pluginsSortedByTitle"
                    :key="plugin.name"
                    :value="plugin"
                    class="col col-12 col-md-6 col-xl-4 col-xxl-3"
                />
                <alert
                    v-if="(!pluginStore.pluginsSortedByTitle || pluginStore.pluginsSortedByTitle == 0)"
                    :message="t('main.plugins.not_found')"
                    :type="'info'"
                    :noicon="false"
                />
            </LoadingContainer>
        </div>
    </div>
</template>

<script>
    import {
        computed,
        onMounted,
        reactive,
        nextTick,
        ref,
    } from 'vue';


    import { useI18n } from 'vue-i18n';

    import usePluginStore from '../bootstrap/stores/plugin';
    import { useToast } from '@/plugins/toast.js';

    import {
        can,
    } from '@/helpers/helpers.js';

    import Plugin from './plugins/Plugin.vue';
    import { getPluginTitle } from '../helpers/plugins';
    import LoadingContainer from './structure/LoadingContainer.vue';
    import { useLoad } from '../composables/load';

    export default {
        components: {
            Plugin,
            LoadingContainer,
        },
        setup(props) {
            const { t } = useI18n();
            const pluginStore = usePluginStore();
            const { execAsync, error, loading } = useLoad()

            onMounted(() => {
                // Ensure plugins are loaded
                refreshPlugins();
            });

            const refreshPlugins = async _ => {
                await execAsync(pluginStore.refresh);
            };

            const inputFile = (newFile, oldFile) => {
                if(!can('preferences_create')) return;

                // Enable automatic upload
                if(!!newFile && (Boolean(newFile) !== Boolean(oldFile) || oldFile.error !== newFile.error)) {
                    if(!newFile.active) {
                        newFile.active = true;
                    }
                }
            };
            const uploadZip = async (file, component) => {
                const result = await execAsync(async () => pluginStore.upload(file.file));
                console.log("File uploaded", result)
                // Currently we must reload the page when the plugin is
                // updated, to remove the old script from the browser and 
                // that the new script can run without collisions.
                // TODO: This should be improved with a more sophisticated frontend
                // system in a future release.
                // if(result && result.updated) {
                //     const label = t('main.plugins.toasts.update.message', {
                //         name: getPluginTitle(plugin),
                //         vo: vo,
                //         v: plugin.version,
                //     });
                //     const title = t('main.plugins.toasts.update.title');
                //     toast.$toast(label, title, {
                //         channel: 'success',
                //         duration: 3000,
                //     });
                //     setTimeout(() => window.location.reload(), 3000)
                // }
            };

            const uploadButton = ref(null);

            const fileDragged = computed(() => uploadButton.value?.dropActive || false);

            // RETURN
            return {
                t,
                // HELPERS
                can,
                // LOCAL
                inputFile,
                uploadZip,
                uploadButton,
                // PROPS
                pluginStore,
                fileDragged,
                loading,
                error,
            };
        },
    };
</script>
