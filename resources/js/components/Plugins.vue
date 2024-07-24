<template>
    <div class="d-flex flex-column h-100">
        <header class="d-flex align-items-center justify-content-between mb-4">
            <h1 class="mb-0 fw-bold text-muted">
                {{ t('main.plugins.title') }}
            </h1>
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
        </header>
        <div class="row row-cols-3 g-3">
            <div
                v-for="plugin in state.sortedPlugins"
                :key="plugin.name"
                class="col"
            >
                <PluginCard 
                    :plugin="plugin"
                    @install="install"
                    @uninstall="uninstall"
                    @update="update"
                    @remove="remove"
                    @fix="fix"
                />
            </div>
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
        reactive,
    } from 'vue';

    import { useI18n } from 'vue-i18n';
    import store from '@/bootstrap/store.js';

    import { useToast } from '@/plugins/toast.js';

    import {
        uploadPlugin,
        installPlugin,
        uninstallPlugin,
        updatePlugin,
        removePlugin,
    } from '@/api.js';

    import {
        can,
    } from '@/helpers/helpers.js';

    import {
        appendScript,
        removeScript,
    } from '@/helpers/plugins.js';

    import PluginCard from './plugins/PluginCard.vue';

    export default {
        components: {
            PluginCard,
        },
        setup(props) {
            const { t } = useI18n();
            const toast = useToast();

            const install = plugin => {
                installPlugin(plugin.id).then(data => {
                    appendScript(data.install_location);
                });
            };
            const uninstall = plugin => {
                uninstallPlugin(plugin.id).then(data => {
                    removeScript(data.uninstall_location);
                });
            };
            const update = plugin => {
                const vo = plugin.version;
                updatePlugin(plugin.id).then(_ => {
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
            const remove = plugin => {
                removePlugin(plugin.id).then(data => {
                    removeScript(data.uninstall_location);
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
                return uploadPlugin(file.file).then(_ => {
                    state.files = [];

                });
            };

            // DATA
            const state = reactive({
                plugins: computed(_ => store.getters.plugins),
                sortedPlugins: computed(_ => Object.values(state.plugins).sort((a, b) => a.metadata.title > b.metadata.title)),
                files: [],
            });

            // RETURN
            return {
                t,
                // HELPERS
                can,
                // LOCAL
                install,
                uninstall,
                update,
                remove,
                inputFile,
                uploadZip,
                // PROPS
                // STATE
                state,
            };
        },
    };
</script>
