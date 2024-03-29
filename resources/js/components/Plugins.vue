<template>
    <div class="d-flex flex-column h-100">
        <h4>
            {{ t('main.plugins.title') }}
            <file-upload
                class="btn btn-sm btn-outline-primary clickable"
                accept="application/zip"
                extensions="zip"
                ref="upload"
                v-model="state.files"
                :custom-action="uploadZip"
                :directory="false"
                :disabled="!can('preferences_create')"
                :multiple="false"
                :drop="true"
                @input-file="inputFile">
                    <span>
                        <i class="fas fa-fw fa-file-import"></i> {{ t('main.plugins.upload') }}
                    </span>
            </file-upload>
        </h4>
        <div class="row row-cols-3 g-3">
            <div class="col" v-for="plugin in state.sortedPlugins" :key="plugin.name">
                <div class="card h-100">
                    <div class="card-body d-flex flex-column">
                        <div class="d-flex flex-row flex-grow-1 mb-3 justify-content-between">
                            <div class="pe-2">
                                <h5 class="card-title mb-1">
                                    {{ plugin.metadata.title }}
                                </h5>
                                <h6 class="card-subtitle mb-2 text-muted">
                                    <span class="badge bg-dark">
                                        v{{ plugin.version }}
                                    </span>
                                    <span class="badge bg-primary ms-1">
                                        {{ plugin.metadata.licence }}
                                    </span>
                                </h6>
                                <p class="card-text border-start border-warning border-4 ps-2">
                                    <md-viewer :source="plugin.metadata.description" />
                                </p>
                            </div>
                            <div class="border-start ps-2">
                                <h6 class="mb-0 text-end">
                                    {{ t('main.plugins.authors') }}
                                </h6>
                                <ul class="list-group list-group-flush">
                                    <li class="list-group-item" v-for="(author, i) in plugin.metadata.authors" :key="i">
                                        {{ author }}
                                    </li>
                                </ul>
                            </div>
                        </div>
                        <div class="">
                            <button type="button" class="btn btn-sm btn-outline-warning" v-if="isInstalled(plugin)" @click="uninstall(plugin)">
                                <i class="fas fa-fw fa-times"></i>
                                {{ t('main.plugins.deactivate') }}
                            </button>
                            <button type="button" class="btn btn-sm btn-outline-success" v-else @click="install(plugin)">
                                <i class="fas fa-fw fa-plus"></i>
                                {{ t('main.plugins.activate') }}
                            </button>
                            <div class="btn-group" role="group" v-if="updateAvailable(plugin)">
                                <button type="button" class="btn btn-sm btn-outline-primary ms-2" @click="update(plugin)">
                                    <i class="fas fa-fw fa-download"></i>
                                    <span v-html="t('main.plugins.update_to', {version: plugin.update_available})"/>
                                </button>
                                <button type="button" class="btn btn-sm btn-outline-primary" :title="t('main.plugins.changelog_info')" @click="showChangelog(plugin)">
                                    <i class="fas fa-fw fa-file-pen"></i>
                                </button>
                            </div>
                            <button type="button" class="btn btn-sm btn-outline-danger ms-2" @click="remove(plugin)">
                                <i class="fas fa-fw fa-trash"></i>
                                {{ t('main.plugins.remove') }}
                            </button>
                        </div>
                    </div>
                </div>
            </div>
            <alert v-if="(!state.sortedPlugins || state.sortedPlugins.length == 0)"
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
        showChangelogModal,
    } from '@/helpers/modal.js';

    import {
        appendScript,
        removeScript,
    } from '@/helpers/plugins.js';

    export default {
        setup(props) {
            const { t } = useI18n();
            const toast = useToast();

            // FUNCTIONS
            const isInstalled = plugin => {
                return !!plugin.installed_at;
            };
            const updateAvailable = plugin => {
                return !!plugin.update_available;
            };
            const showChangelog = plugin => {
                showChangelogModal(plugin);
            };
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
                        newFile.active = true
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
                isInstalled,
                updateAvailable,
                showChangelog,
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
    }
</script>
