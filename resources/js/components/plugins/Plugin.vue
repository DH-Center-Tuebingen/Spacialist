<template>
    <div
        :key="value.name"
        class="col"
    >
        <div
            class="card h-100"
            :style="computedStyle"
        >
            <div class="card-body d-flex flex-column pb-1">
                <header class="d-flex justify-content-between gap-2 mb-2">
                    <h5 class="card-title mb-0">
                        {{ getPluginTitle(value) }}
                    </h5>
                    <div class="toolbar d-flex align-items-center gap-1">
                        <div class="d-flex align-items-center gap-1">
                            <button
                                type="button"
                                class="badge btn btn-sm btn-secondary border-0"
                                :title="t('main.plugins.changelog_info')"
                                @click="showChangelog()"
                            >
                                <i class="fas fa-fw fa-file-pen" />
                            </button>
                            <span class="badge bg-secondary">
                                v{{ value.version }}
                            </span>
                            <span
                                class="badge bg-primary"
                                :title="t('global.licence')"
                            >
                                <i class="fa far fa-file-lines me-1" />
                                {{ licence }}
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
                                        {{ t('global.refresh') }}
                                    </span>
                                </a>
                                <div class="dropdown-divider"></div>
                                <a
                                    type="button"
                                    class="dropdown-item text-danger"
                                    @click="remove()"
                                >
                                    <!-- :class="{disabled: isInstalled(), 'opacity-50': isInstalled()}" -->
                                    <i class="fas fa-fw fa-trash" />
                                    {{ t('global.remove') }}
                                </a>
                            </div>
                        </div>
                    </div>
                </header>
                <div
                    class="overflow-auto flex-fill"
                    style="max-height: 420px;"
                >
                    <div class="card-text text-secondary d-flex flex-column gap-2 h-100">
                        <MarkdownText
                            class="flex-fill"
                            :value="description"
                        />
                        <footer class="d-flex justify-content-between align-items-center">
                            <div class="form-check form-switch">
                                <input
                                    ref="installationSwitch"
                                    class="form-check-input"
                                    type="checkbox"
                                    role="switch"
                                    id="switchCheckChecked"
                                    :checked="isInstalled()"
                                    @change="toggleActiveState"
                                >
                            </div>

                            <span class="opacity-50">
                                <i class="fa-regular fa-circle-user"></i> {{ authors }}
                            </span>
                        </footer>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>

<script>
    import { computed, ref, useTemplateRef } from 'vue';
    import { useI18n } from 'vue-i18n';
    import { useToast } from '@/plugins/toast.js';

    import {
        showChangelogModal,
    } from '@/helpers/modal.js';

    import usePluginStore from '@/bootstrap/stores/plugin.js';
    import { isInstalled as isPluginInstalled } from '@/helpers/plugins.js';
    import { getPluginTitle } from '@/helpers/plugins';

    import MarkdownText from '@/components/mde/MarkdownText.vue';



    export default {
        components: {
            MarkdownText,
        },
        props: {
            value: {
                type: Object,
                required: true,
            },
        },
        setup(props) {
            const { t } = useI18n();
            const toast = useToast();

            const page = ref('info');
            const installing = ref(false);
            const installationSwitch = useTemplateRef('installationSwitch');

            const pluiginStore = usePluginStore();
            // FUNCTIONS
            const isInstalled = _ => {
                return isPluginInstalled(props.value);
            };

            const showChangelog = _ => {
                showChangelogModal(props.value);
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

            const toggleActiveState = async () => {
                installing.value = true;
                try {
                    if(!isInstalled()) {
                        await install();
                    } else {
                        await uninstall();
                    }
                } catch(e) {
                    const error = e.response?.data?.error || e.message || 'Unknown error';
                    
                    // Somehow Vue will not reset th
                    installationSwitch.value.checked = isInstalled();

                    toast.$toast(error, t('global.error.altoastert_title'), {
                        channel: 'danger',
                        duration: 10000,
                    });
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

            const licence = computed(() => {
                let licence = props.value.metadata?.licence || "";
                console.log('Raw licence', licence, licence.length);
                licence = licence.trim();
                licence = licence.toUpperCase();
                return licence || '–';
            });

            const description = computed(() => {
                return props.value?.metadata?.description || '–';
            });

            const authors = computed(() => {
                const authors = props.value?.metadata?.authors ?? [];
                if(authors.length === 0) {
                    return '–';
                }
                return authors.join(', ');
            });

            return {
                authors,
                computedStyle,
                description,
                installing,
                install,
                isInstalled,
                getPluginTitle,
                licence,
                page,
                remove,
                showChangelog,
                t,
                toggleActiveState,
                uninstall,
                pluiginStore,
            };
        }
    };
</script>