<template>
    <div class="container-fluid d-flex flex-column h-100 overflow-y-auto">
        <h4>
            {{ t('main.plugins.title', 2) }}
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
        </h4>
        <div class="row row-cols-3 g-3">
            <Plugin
                v-for="plugin in state.sortedPlugins"
                :key="plugin.name"
                :value="plugin"
                class="col col-12 col-md-6 col-xl-4"
            />
            <alert
                v-if="(!state.sortedPlugins || state.sortedPlugins.length == 0)"
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
    import useSystemStore from '@/bootstrap/stores/system.js';

    import { useToast } from '@/plugins/toast.js';

    import {
        can,
    } from '@/helpers/helpers.js';

    import Plugin from './plugins/Plugin.vue';
    import { isInstalled } from '../helpers/plugins';

    export default {
        components: {
            Plugin
        },
        setup(props) {
            const { t } = useI18n();
            const systemStore = useSystemStore();

            let toast = null;
            onMounted(() => {
                // Ensure plugins are loaded
                nextTick(() => {
                    toast = useToast();
                });
            });


            const update = plugin => {
                systemStore.patchPlugin(plugin.id).then(_ => {
                    const vo = plugin.version;
                    const label = t('main.plugins.toasts.update.message', {
                        name: plugin.metadata.title,
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
                return systemStore.uploadPlugin(file.file).then(_ => {
                    state.files = [];
                });
            };

            // DATA
            const state = reactive({
                plugins: computed(_ => systemStore.plugins),
                sortedPlugins: computed(_ => {
                    return Object.values(state.plugins).sort((a, b) => {

                        if(isInstalled(a) && !isInstalled(b)) return -1;
                        if(!isInstalled(a) && isInstalled(b)) return 1;

                        return a.metadata.title.localeCompare(b.metadata.title);
                    });
                }),
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
            };
        },
    };
</script>
