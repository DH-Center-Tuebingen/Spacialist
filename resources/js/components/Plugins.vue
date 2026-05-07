<template>
    <div class="container-fluid d-flex flex-column h-100 overflow-y-auto">
        <h4>
            {{ t('main.plugins.title', 2) }}
            <div class="float-end">
                <file-upload
                    ref="upload"
                    v-model="state.files"
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
        <div class="row row-cols-3 g-3">
            <Plugin
                v-for="plugin in pluginStore.pluginsSortedByTitle"
                :key="plugin.name"
                :value="plugin"
                class="col col-12 col-md-6 col-xl-4"
            />
            <alert
                v-if="(!pluginStore.pluginsSortedByTitle || pluginStore.pluginsSortedByTitle == 0)"
                :message="t('main.plugins.not_found')"
                :type="'info'"
                :noicon="false"
            />
        </div>
    </div>
</template>

<script>
    import {
        computed,
        onMounted,
        reactive,
        nextTick,
    } from 'vue';


    import { useI18n } from 'vue-i18n';

    import usePluginStore from '../bootstrap/stores/plugin';
    import { useToast } from '@/plugins/toast.js';

    import {
        can,
    } from '@/helpers/helpers.js';

    import Plugin from './plugins/Plugin.vue';
    import { getPluginTitle } from '../helpers/plugins';

    export default {
        components: {
            Plugin
        },
        setup(props) {
            const { t } = useI18n();
            const pluginStore = usePluginStore();

            let toast = null;
            onMounted(() => {
                // Ensure plugins are loaded
                nextTick(() => {
                    toast = useToast();
                });
            });

            const update = plugin => {
                pluginStore.update(plugin.id).then(_ => {
                    const vo = plugin.version;
                    const label = t('main.plugins.toasts.update.message', {
                        name: getPluginTitle(plugin),
                        vo: vo,
                        v: plugin.version,
                    });
                    const title = t('main.plugins.toasts.update.title');
                    toast.$toast(label, title, {
                        channel: 'success',
                        duration: 10000,
                    });
                });
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
            const uploadZip = (file, component) => {
                return pluginStore.upload(file.file).then(_ => {
                    state.files = [];
                });
            };

            // DATA
            const state = reactive({
                files: [],
            });

            const columnClasses = [

            ];


            // RETURN
            return {
                t,
                // HELPERS
                can,
                // LOCAL
                inputFile,
                uploadZip,
                // PROPS
                // STATE
                state,
                columnClasses,
                pluginStore,
            };
        },
    };
</script>
