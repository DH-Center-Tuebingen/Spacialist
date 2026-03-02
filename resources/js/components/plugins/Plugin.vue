<template>
    <div
        :key="value.name"
        class="col"
    >
        <div
            class="card h-100"
            :style="computedStyle"
        >
            <div class="card-body">
                <header class="d-flex justify-content-between gap-2 mb-1">
                    <h5 class="card-title mb-2">
                        {{ getPluginTitle(value) }}
                    </h5>
                    <div class="toolbar d-flex align-items-center gap-1">
                        <div>
                            <span class="badge bg-dark">
                                v{{ value.version }}
                            </span>
                            <span class="badge bg-primary ms-1">
                                {{ value.metadata?.licence?.toUpperCase ? value.metadata.licence.toUpperCase() : '–' }}
                            </span>
                        </div>

                        <div
                            class="user-select-none"
                            style="z-index: 10;"
                        >
                            <span
                                :id="`plugin-${value.id}-dropdown`"
                                class="clickable text-body align-middle"
                                data-bs-toggle="dropdown"
                                role="button"
                                aria-haspopup="true"
                                aria-expanded="false"
                            >
                                <i class="fas fa-fw fa-ellipsis-vertical" />
                            </span>
                            <div
                                :id="`plugin-settings-${value.id}-contextmenu`"
                                class="dropdown-menu dropdown-menu-end"
                                @click.stop.prevent
                                :aria-labelledby="`plugin-${value.id}-dropdown`"
                            >
                                <a
                                    href="#"
                                    class="dropdown-item"
                                    @click="pluiginStore.publishScript(value)"
                                >
                                    <span class="ms-2">
                                        {{ t('main.plugins.refresh-script') }}
                                    </span>
                                </a>
                                <a
                                    href="#"
                                    class="dropdown-item"
                                    @click="pluiginStore.refreshInfo(value)"
                                >
                                    <span class="ms-2">
                                        {{ t('main.global.refresh') }}
                                    </span>
                                </a>
                            </div>
                        </div>
                    </div>
                </header>

                <ul class="nav nav-pills">
                    <li
                        v-for="section in sections"
                        :key="section"
                        class="nav-item user-select-none mb-1"
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
                <div
                    class="overflow-auto"
                    style="max-height: 420px;"
                >
                    <MigrationTab
                        v-if="page === 'migrations'"
                        :value="value"
                    />
                    <InformationTab
                        v-else-if="page === 'info'"
                        :value="value"
                    />
                    <ChangelogTab
                        v-else-if="page === 'changelog'"
                        :value="value"
                    />
                    <div v-else>
                        404
                    </div>
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
                        @click="pluiginStore.update(value.id)"
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
    import { computed, ref } from 'vue';
    import { useI18n } from 'vue-i18n';

    import {
        showChangelogModal,
    } from '@/helpers/modal.js';

    import usePluginStore from '@/bootstrap/stores/plugin.js';
    import { isInstalled as isPluginInstalled } from '@/helpers/plugins.js';

    import ChangelogTab from '@/components/plugins/tab/Changelog.vue';
    import MigrationTab from '@/components/plugins/tab/Migration/Migration.vue';
    import InformationTab from './tab/Information.vue';
    import { getPluginTitle } from '../../helpers/plugins';

    export default {
        components: {
            ChangelogTab,
            InformationTab,
            MigrationTab,
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
            const installing = ref(false);

            const sections = ['info', 'changelog', 'migrations'];

            const pluiginStore = usePluginStore();
            // FUNCTIONS
            const isInstalled = _ => {
                return isPluginInstalled(props.value);
            };
            const updateAvailable = _ => {
                return !!props.value.update_available;
            };
            const showChangelog = _ => {
                showChangelogModal();
            };
            const install = async _ => {
                await pluiginStore.install(props.value.id);
            };
            const uninstall = async _ => {
                await pluiginStore.uninstall(props.value.id);
            };
            const remove = _ => {
                pluiginStore.remove(props.value.id);
            };

            const toggleActiveState = async (active) => {
                installing.value = true;
                if(isInstalled() !== active) {
                    await install();
                } else {
                    await uninstall();
                }
                installing.value = false;
            };

            const computedStyle = computed(() => {
                if(!isInstalled()) {
                    return {
                        filter: 'brightness(97%)',
                    };
                }
                return {};
            });

            return {
                computedStyle,
                installing,
                install,
                isInstalled,
                getPluginTitle,
                page,
                remove,
                sections,
                showChangelog,
                t,
                toggleActiveState,
                uninstall,
                updateAvailable,
                pluiginStore,
            };
        }
    };
</script>