<template>
    <!-- eslint-disable vue/no-unused-components -->
    <div class="h-100 row">
        <div class="col-2 h-100 border-end">
            <h6 class="fw-bold">
                {{ t('main.preference.categories.user') }}
            </h6>
            <nav class="nav nav-underline flex-column px-2">
                <router-link
                    v-for="(cat, k) in state.categories.user"
                    :key="`user-pref-subcat-${k}`"
                    :to="{ name: 'userpreferences', hash: `#${k}` }"
                    :class="setNavClasses(k, true)"
                >
                    <span v-if="cat.custom">
                        {{ t(cat.title) }}
                    </span>
                    <span v-else>
                        {{ t(`main.preference.categories.sub.${k}`) }}
                    </span>
                </router-link>
            </nav>
            <hr>
            <h6 class="fw-bold">
                {{ t('main.preference.categories.system') }}
            </h6>
            <nav class="nav nav-underline flex-column px-2">
                <router-link
                    v-for="(cat, k) in state.categories.system"
                    :key="`sys-pref-subcat-${k}`"
                    :to="{ name: 'preferences', hash: `#${k}` }"
                    :class="setNavClasses(k)"
                >
                    <span v-if="cat.custom">
                        {{ t(cat.title) }}
                    </span>
                    <span v-else>
                        {{ t(`main.preference.categories.sub.${k}`) }}
                    </span>
                </router-link>
            </nav>
        </div>
        <div class="col-10 d-flex flex-column h-100 overflow-scroll">
            <h3 class="d-flex flex-row gap-2 align-items-center">
                {{ t('global.preference', 2) }}
                <span v-if="state.category == 'system'">
                    - System
                </span>
                <span v-else>
                    - User
                </span>
                <button
                    type="button"
                    class="btn btn-outline-success btn-sm"
                    @click="savePreferences()"
                >
                    <i class="fas fa-fw fa-save" />
                    {{ t('global.save') }}
                </button>
            </h3>
            <div
                v-if="state.categoryPreferences && state.categoryPreferences.length > 0"
                class="table-responsive flex-grow-1 overflow-x-hidden"
            >
                <table
                    v-dcan="'preferences_write'"
                    class="table table-light table-striped table-hover mb-0"
                >
                    <tbody>
                        <tr
                            v-for="preferencesBlock in state.categoryPreferences"
                            :key="preferencesBlock.label"
                        >
                            <td>
                                <strong>
                                    {{ t(preferencesBlock.title) }}
                                </strong>
                            </td>
                            <td>
                                <component
                                    :is="preferencesBlock.component"
                                    :model-value="(preferencesBlock.data === 'v-model') ? state.preferences[preferencesBlock.label] : null"
                                    :data="(preferencesBlock.data === undefined) ? state.preferences[preferencesBlock.label] : null"
                                    @update:model-value="value => updateValue(preferencesBlock, value)"
                                    @changed="e => trackChanges(preferencesBlock.label, e)"
                                />
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
            <alert
                v-else
                :message="t('main.preference.categories.is_empty')"
                :type="'note'"
                :noicon="false"
                class="w-50"
            />
        </div>
    </div>
</template>

<script>
    import {
        computed,
        reactive,
        watch,
    } from 'vue';

    import { useI18n } from 'vue-i18n';
    import store from '@/bootstrap/store.js';
    import { useToast } from '@/plugins/toast.js';
    import { patchPreferences } from '@/api.js';

    import {
        useRoute,
    } from 'vue-router';

    import {
        can,
    } from '@/helpers/helpers.js';

    import GuiLanguage from '@/components/preferences/GuiLanguage.vue';
    import Color from '@/components/preferences/Color.vue';
    import ResetEmail from '@/components/preferences/ResetEmail.vue';
    import Columns from '@/components/preferences/Columns.vue';
    import ShowTooltips from '@/components/preferences/ShowTooltips.vue';
    import Tags from '@/components/preferences/Tags.vue';
    import ThesaurusLink from '@/components/preferences/ThesaurusLink.vue';
    import ProjectName from '@/components/preferences/ProjectName.vue';
    import ProjectMaintainer from '@/components/preferences/ProjectMaintainer.vue';

    export default {
        components: {
            'gui-language-preference': GuiLanguage,
            'color-preference': Color,
            'reset-email-preference': ResetEmail,
            'columns-preference': Columns,
            'tooltips-preference': ShowTooltips,
            'tags-preference': Tags,
            'thesaurus-link-preference': ThesaurusLink,
            'project-name-preference': ProjectName,
            'project-maintainer-preference': ProjectMaintainer,
        },
        setup(props, context) {
            const { t, locale } = useI18n();
            const toast = useToast();
            const currentRoute = useRoute();

            // FUNCTIONS
            const setCategories = route => {
                if(route.path.startsWith('/preferences/system')) {
                    state.category = 'system';
                } else {
                    state.category = 'user';
                }
                if(route.hash) {
                    state.subcategory = route.hash.substring(1);
                }
            };

            const setNavClasses = (subcategory, isUser = false) => {
                const classes = ['nav-link', 'text-reset', 'py-0', 'px-2'];
                if(isUser == (state.category == 'user') && state.subcategory == subcategory) {
                    classes.push('active', 'text-muted');
                }
                return classes;
            };

            const trackChanges = (label, data) => {
                const c = state.category;
                if(!state.dirtyData[c]) {
                    state.dirtyData[c] = {};
                }
                if(!state.dirtyData[c][label]) {

                    // The endpoint expect all values to be set. 
                    // If any of those change they will be overwritten.
                    state.dirtyData[c][label] = {
                        label: label,
                        value: data,
                    };
                } else {
                    state.dirtyData[c][label].value = data;
                }
            };

            const updateValue = (preferencesBlock, data) => {
                if(preferencesBlock.data === 'v-model') {
                    state.preferences[preferencesBlock.label] = data;
                }
            };

            const savePreferences = _ => {
                if(!state.hasDirtyData) return;

                let updatedLanguage = null;
                if(state.dirtyData?.user?.['prefs.gui-language'] && state.dirtyData.user['prefs.gui-language'].value) {
                    updatedLanguage = state.dirtyData.user['prefs.gui-language'].value;
                }
                const changes = [];
                for(let k in state.dirtyData.user) {
                    const curr = state.dirtyData.user[k];
                    curr.user = true;
                    changes.push(curr);
                }
                for(let k in state.dirtyData.system) {
                    const curr = state.dirtyData.system[k];
                    changes.push(curr);
                }
                const data = {
                    changes: changes,
                };

                patchPreferences(data).then(_ => {
                    // Update language if value has changed
                    if(!!updatedLanguage) {
                        locale.value = updatedLanguage;
                    }
                    state.dirtyData = {};

                    const label = t('main.preference.toasts.updated.msg');
                    toast.$toast(label, '', {
                        channel: 'success',
                        simple: true,
                    });
                });
            };

            const setProgramPreferences = (categories, name) => {
                for(let k in preferencesConfig[name]) {
                    const subcategory = preferencesConfig[name][k];
                    categories[name][k] = {
                        preferences: subcategory.preferences.slice(),
                    };
                }
            };

            const setPluginPreferences = (categories, name) => {
                for(let k in state.pluginPreferences[name]) {
                    const subcategory = state.pluginPreferences[name][k];
                    if(!categories[name][k]) {
                        categories[name][k] = {
                            preferences: subcategory.preferences.slice(),
                        };
                        if(subcategory.custom) {
                            categories[name][k].custom = true;
                            categories[name][k].title = subcategory.title;
                        }
                    } else {
                        categories[name][k].preferences = categories[name][k].preferences.concat(subcategory.preferences.slice());
                    }
                }
            };

            // DATA
            const preferencesConfig = {
                user: {
                    general: {
                        preferences: [],
                    },
                    layout: {
                        preferences: [
                            {
                                title: 'main.preference.key.language',
                                label: 'prefs.gui-language',
                                component: 'gui-language-preference',
                                data: 'v-model'
                            },
                            {
                                title: 'main.preference.key.color.title',
                                label: 'prefs.color',
                                component: 'color-preference',
                            },
                        ],
                    },
                    interface: {
                        preferences: [
                            {
                                title: 'main.preference.key.columns.title',
                                label: 'prefs.columns',
                                component: 'columns-preference',
                            },
                            {
                                title: 'main.preference.key.tooltips',
                                label: 'prefs.show-tooltips',
                                component: 'tooltips-preference',
                            },
                        ],
                    },
                },
                system: {
                    general: {
                        preferences: [
                            {
                                title: 'main.preference.key.password_reset',
                                label: 'prefs.enable-password-reset-link',
                                component: 'reset-email-preference',
                            },
                            {
                                title: 'main.preference.key.tag_root',
                                label: 'prefs.tag-root',
                                component: 'tags-preference',
                            },
                            {
                                title: 'main.preference.key.link_thesaurex',
                                label: 'prefs.link-to-thesaurex',
                                component: 'thesaurus-link-preference',
                            },
                            {
                                title: 'main.preference.key.project.name',
                                label: 'prefs.project-name',
                                component: 'project-name-preference',
                                data: 'v-model'
                            },
                            {
                                title: 'main.preference.key.project.maintainer',
                                label: 'prefs.project-maintainer',
                                component: 'project-maintainer-preference',
                            },
                        ],
                    },
                    layout: {
                        preferences: [
                            {
                                title: 'main.preference.key.language',
                                label: 'prefs.gui-language',
                                component: 'gui-language-preference',
                                data: 'v-model'
                            },
                            {
                                title: 'main.preference.key.color.title',
                                label: 'prefs.color',
                                component: 'color-preference',
                            },
                        ],
                    },
                    interface: {
                        preferences: [
                            {
                                title: 'main.preference.key.columns.title',
                                label: 'prefs.columns',
                                component: 'columns-preference',
                            },
                            {
                                title: 'main.preference.key.tooltips',
                                label: 'prefs.show-tooltips',
                                component: 'tooltips-preference',
                            },
                        ],
                    },
                },
            };
            const state = reactive({
                category: 'system',
                subcategory: 'general',
                dirtyData: {},
                hasDirtyData: computed(_ => Object.keys(state.dirtyData).length > 0),
                systemPreferences: computed(_ => {
                    const sysPrefs = store.getters.systemPreferences;
                    return sysPrefs;
                }),
                userPreferences: computed(_ => {
                    const userPrefs = store.getters.preferences;
                    return userPrefs;
                }),
                preferences: computed(_ => {
                    if(state.category == 'system') {
                        return state.systemPreferences;
                    } else {
                        return state.userPreferences;
                    }
                }),
                pluginPreferences: computed(_ => store.getters.pluginPreferences),
                categories: computed(_ => {
                    const categories = {
                        user: {},
                        system: {},
                    };

                    for(let category in categories){
                        setProgramPreferences(categories, category);
                        setPluginPreferences(categories, category);
                    }

                    return categories;
                }),
                categoryPreferences: computed(_ => {
                    return state.categories[state.category][state.subcategory].preferences;
                }),
            });

            setCategories(currentRoute);

            watch(currentRoute, (newValue, oldValue) => {
                setCategories(newValue);
            });

            // RETURN
            return {
                t,
                // HELPERS
                can,
                // LOCAL
                setNavClasses,
                trackChanges,
                updateValue,
                savePreferences,
                // PROPS
                // STATE
                state,
            };
        },
    };
</script>
