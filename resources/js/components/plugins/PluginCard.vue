<template>
    <div class="card h-100">
        <div class="card-body d-flex flex-column gap-3">
            <header class="d-flex flex-column ">
                <div class="d-flex flex-row  justify-content-between align-items-center">
                    <h5 class="card-title fw-bold">
                        {{ plugin.metadata.title }}
                    </h5>

                    <div class="controls d-flex gap-3">
                        <button
                            class="btn"
                            @click="showChangelogModal(plugin)"
                        >
                            v{{ plugin.version }}
                        </button>

                        <div class="d-flex justify-content-end">
                            <button
                                v-if="isInstalled"
                                type="button"
                                class="btn btn-sm btn-outline-warning"
                                @click="$emit('uninstall', plugin)"
                            >
                                <i class="fas fa-fw fa-times" />
                                {{ t('main.plugins.deactivate') }}
                            </button>
                            <template v-else>
                                <button
                                    type="button"
                                    class="btn btn-sm btn-outline-success"
                                    @click="$emit('install', plugin)"
                                >
                                    <i class="fas fa-fw fa-plus" />
                                    {{ t('main.plugins.activate') }}
                                </button>
                                <button
                                    type="button"
                                    class="btn btn-sm btn-outline-danger ms-2"
                                    @click="$emit('remove', plugin)"
                                >
                                    <i class="fas fa-fw fa-trash" />
                                    {{ t('main.plugins.remove') }}
                                </button>
                            </template>
                        </div>
                    </div>
                </div>
                <div class="d-flex flex-row gap-2">
                    <span
                        v-for="(author, i) in plugin.metadata.authors"
                        :key="i"
                        class="text-muted"
                    >
                        {{ author }}
                    </span>
                </div>
            </header>

            <md-viewer :source="plugin.metadata.description" />
        </div>
        <footer
            v-if="updateAvailable"
            class="card-footer bg-primary text-white"
        >
            <div class="d-flex justify-content-center gap-2">
                <span class="d-inline-block">
                    <i class="fas fa-fw fa-refresh" />
                </span>
                <span class="d-inline-block">
                    {{ t('main.plugins.update_available') }}
                    <a
                        href=""
                        class="text-white"
                        @click.prevent="$emit('update', plugin)"
                        v-html="t('main.plugins.update_to', { version: plugin.update_available })"
                    />
                </span>
            </div>
        </footer>
    </div>
</template>

<script>
    import {
        computed,
    } from 'vue';

    import {
        showChangelogModal,
    } from '@/helpers/modal.js';

    import { useI18n } from 'vue-i18n';

    export default {
        props: {
            plugin: {
                type: Object,
                required: true,
            },
        },
        emits: [
            'install',
            'remove',
            'uninstall',
            'update',
        ],
        setup(props) {
            const { t } = useI18n();

            // FUNCTIONS
            const isInstalled = computed(_ => {
                return Boolean(props.plugin.installed_at);
            });
            const updateAvailable = computed(_ => {
                console.log(props.plugin.update_available);
                return Boolean(props.plugin.update_available);
            });

            return {
                t,
                isInstalled,
                updateAvailable,
                showChangelogModal,
            };
        }
    };
</script>