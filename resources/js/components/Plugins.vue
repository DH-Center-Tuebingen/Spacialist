<template>
    <div class="d-flex flex-column h-100">
        <h4>
            {{ t('global.settings.plugins') }}
        </h4>
        <div class="row row-cols-3 g-3">
            <div class="col" v-for="plugin in state.plugins" :key="plugin.name">
                <div class="card h-100" v-if="!plugin.isnomore">
                    <div class="card-body d-flex flex-column">
                        <div class="d-flex flex-row flex-grow-1 mb-3">
                            <div class="">
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
                                    {{ plugin.metadata.description }}
                                </p>
                            </div>
                            <div>
                                <h6 class="mb-0">
                                    Authors
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
                                Uninstall
                            </button>
                            <button type="button" class="btn btn-sm btn-outline-success" v-else @click="install(plugin)">
                                <i class="fas fa-fw fa-plus"></i>
                                Install
                            </button>
                            <button type="button" class="btn btn-sm btn-outline-primary ms-2" v-if="updateAvailable(plugin)" @click="update(plugin)">
                                <i class="fas fa-fw fa-download"></i>
                                Update to <span class="fw-bold">{{ plugin.update_available }}</span>
                            </button>
                            <button type="button" class="btn btn-sm btn-outline-danger ms-2" @click="remove(plugin)">
                                <i class="fas fa-fw fa-trash"></i>
                                Remove
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>

<script>
    import {
        computed,
        reactive,
    } from 'vue';

    import { useI18n } from 'vue-i18n';
    import store from '../bootstrap/store.js';

    import {
        installPlugin,
        uninstallPlugin,
        removePlugin,
    } from '../api.js';

    export default {
        setup(props) {
            const { t } = useI18n();

            // FUNCTIONS
            const isInstalled = plugin => {
                return !!plugin.installed_at;
            };
            const updateAvailable = plugin => {
                return !!plugin.update_available;
            };
            const install = plugin => {
                installPlugin(plugin.id);
                plugin.installed_at = new Date();
            };
            const uninstall = plugin => {
                uninstallPlugin(plugin.id);
                plugin.installed_at = null;
            };
            const update = plugin => {
                // updatePlugin(plugin.name);
                plugin.update_available = null;
            };
            const remove = plugin => {
                removePlugin(plugin.id);
                plugin.isnomore = true;
            };

            // DATA
            const state = reactive({
                plugins: computed(_ => store.getters.plugins),
            });

            // RETURN
            return {
                t,
                // HELPERS
                // LOCAL
                isInstalled,
                updateAvailable,
                install,
                uninstall,
                update,
                remove,
                // PROPS
                // STATE
                state,
            };
        },
    }
</script>
