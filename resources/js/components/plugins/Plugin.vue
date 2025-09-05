<template>
    <div
        :key="value.name"
        class="col"
    >
        <div
            class="card h-100"
            :class="{
                ['opacity-75']: !isInstalled()
            }"
        >
            <div class="card-body">
                <header class="d-flex justify-content-between gap-2 mb-1">
                    <h5 class="card-title mb-2">
                        {{ value.metadata.title }}
                    </h5>
                    <div>
                        <span class="badge bg-dark">
                            v{{ value.version }}
                        </span>
                        <span class="badge bg-primary ms-1">
                            {{ value.metadata.licence }}
                        </span>
                    </div>
                </header>

                <ul class="nav nav-pills">
                    <li
                        v-for="section in sections"
                        :key="section"
                        class="nav-item user-select-none"
                    >
                        <a
                            aria-current="page"
                            class="nav-link px-3 py-1"
                            :class="{
                                'active': page === section
                            }"
                            @click="() => page = section"
                        >{{ t(`main.plugins.section.${section}`) }}</a>
                    </li>
                </ul>
                <div class="my-3">
                    <MigrationTab
                        v-if="page === 'migrations'"
                        :value="value"
                    />
                    <InformationTab
                        v-else-if="page === 'info'"
                        :value="value"
                    />
                </div>
            </div>


            <footer class="card-footer d-flex flex-wrap gap-1">
                <button
                    v-if="isInstalled()"
                    type="button"
                    class="btn btn-sm btn-outline-warning"
                    @click="uninstall()"
                >
                    <i class="fas fa-fw fa-times" />
                    {{ t('main.plugins.deactivate') }}
                </button>
                <button
                    v-else
                    type="button"
                    class="btn btn-sm btn-outline-success"
                    @click="install()"
                >
                    <i class="fas fa-fw fa-plus" />
                    {{ t('main.plugins.activate') }}
                </button>
                <div
                    v-if="updateAvailable()"
                    class="btn-group"
                    role="group"
                >
                    <button
                        type="button"
                        class="btn btn-sm btn-outline-primary"
                        @click="update()"
                    >
                        <i class="fas fa-fw fa-download" />
                        <!-- eslint-disable-next-line vue/no-v-html -->
                        <span v-html="t('main.plugins.update_to', { version: value.update_available })" />
                    </button>
                    <button
                        type="button"
                        class="btn btn-sm btn-outline-primary"
                        :title="t('main.plugins.changelog_info')"
                        @click="showChangelog()"
                    >
                        <i class="fas fa-fw fa-file-pen" />
                    </button>
                </div>
                <button
                    type="button"
                    class="btn btn-sm btn-outline-danger"
                    @click="remove()"
                >
                    <i class="fas fa-fw fa-trash" />
                    {{ t('main.plugins.remove') }}
                </button>
            </footer>   
        </div>
    </div>
</template>

<script>
    import { ref } from 'vue';
    import { useI18n } from 'vue-i18n';

    import {
        showChangelogModal,
    } from '@/helpers/modal.js';

    import useSystemStore from '@/bootstrap/stores/system.js';

    import MigrationTab from '@/components/plugins/tab/Migration.vue';
    import InformationTab from './tab/Information.vue';

    export default {
        components: {
            MigrationTab,
            InformationTab,
        },
        props: {
            value: {
                type: Object,
                required: true,
            },
        },
        setup(props) {
            const { t } = useI18n();
            const page = ref('info');

            const sections = ['info', 'changelog', 'migrations'];

            const systemStore = useSystemStore();
            // FUNCTIONS
            const isInstalled = _ => {
                return !!props.value.installed_at;
            };
            const updateAvailable = _ => {
                return !!props.value.update_available;
            };
            const showChangelog = _ => {
                showChangelogModal();
            };
            const install = _ => {
                systemStore.installPlugin(props.value.id);
            };
            const uninstall = _ => {
                systemStore.uninstallPlugin(props.value.id);
            };
            const remove = _ => {
                systemStore.removePlugin(props.value.id);
            };

            return {
                t,
                isInstalled,
                updateAvailable,
                showChangelog,
                install,
                uninstall,
                remove,
                page,
                sections,
            };
        }
    };
</script>

<style lang='scss' scoped></style>