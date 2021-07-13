<template>
    <div class="d-flex flex-column h-100">
        <button type="button" class="btn btn-primary" @click="loadPlugins()">
            Load
        </button>
        <h4>
            {{ t('global.settings.plugins') }}
        </h4>
        <div class="row">
            <div class="col-4 mb-3" v-for="plugin in state.plugins" :key="plugin.name">
                <div class="card">
                    <div class="card-body">
                        <h5 class="card-title">
                            {{ plugin['name'] }}
                            <small class="badge bg-secondary">
                                v{{ plugin['version'] }}
                            </small>
                        </h5>
                        <h6 class="card-subtitle mb-2 text-muted">
                            {{ plugin['licence'] }}
                        </h6>
                        <p class="card-text">
                            {{ plugin['description'] }}
                        </p>
                        <h6 class="mb-0">
                            Authors
                        </h6>
                        <ul class="list-group list-group-flush">
                            <li class="list-group-item" v-for="(author, i) in plugin['authors']" :key="i">
                                {{ author }}
                            </li>
                        </ul>
                        <a href="#" class="btn btn-outline-success">
                            Install
                        </a>
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
        getPlugins,
    } from '../api.js';

    export default {
        setup(props) {
            const { t } = useI18n();

            // FUNCTIONS
            const loadPlugins = _ => {
                getPlugins().then(data => {
                    console.log(data);
                    state.plugins = data.plugins;
                });
            }

            // DATA
            const state = reactive({
                plugins: [],
            });

            // RETURN
            return {
                t,
                // HELPERS
                // LOCAL
                loadPlugins,
                // PROPS
                // STATE
                state,
            };
        },
    }
</script>
