<template>
    <div class="h-100 d-flex flex-column">
        <h3 class="d-flex flex-row gap-2 align-items-center">
            {{ t('global.preference', 2) }}
            <small class="text-muted">
                {{ getUser().name }}
            </small>
            <button
                type="button"
                class="btn btn-outline-success btn-sm"
                @click="savePreferences()"
            >
                <i class="fas fa-fw fa-save" />
            <button
                type="button"
                class="btn btn-outline-success btn-sm"
                @click="savePreferences()"
            >
                <i class="fas fa-fw fa-save" />
                {{ t('global.save') }}
            </button>
        </h3>
        <div class="table-responsive scroll-x-hidden">
            <table
                v-if="state.prefsLoaded"
                v-dcan="'preferences_read'"
                class="table table-light table-striped table-hover mb-0"
            >
            <table
                v-if="state.prefsLoaded"
                v-dcan="'preferences_read'"
                class="table table-light table-striped table-hover mb-0"
            >
                <thead class="sticky-top">
                    <tr class="text-nowrap">
                        <th>{{ t('global.preference') }}</th>
                        <th
                            style="width: 99%;"
                            class="text-end"
                        >
                            {{ t('global.value') }}
                        </th>
                        <th
                            style="width: 99%;"
                            class="text-end"
                        >
                            {{ t('global.value') }}
                        </th>
                    </tr>
                </thead>
                <tbody>
                    <tr
                        v-for="preferencesBlock of preferencesConfig"
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
                                v-if="preferencesBlock.data === 'v-model'"
                                v-model="state.preferences[preferencesBlock.label]"
                                @changed="value => trackChanges(preferencesBlock.label, value)"
                            />
                            <component 
                                :is="preferencesBlock.component"
                                v-else
                                :data="state.preferences[preferencesBlock.label]"
                                @changed="e => trackChanges(preferencesBlock.label, e)"
                            /> 
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>
</template>

<script>
    /* eslint-disable vue/no-unused-components */
    import {
        computed,
        reactive,
    } from 'vue';

    import {
        useRoute,
    } from 'vue-router';

    import { useI18n } from 'vue-i18n';

    import store from '@/bootstrap/store.js';

    import { useToast } from '@/plugins/toast.js';

    import { patchPreferences } from '@/api.js';

    import {
        can,
        getUser,
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
    import MapProjection from '@/components/preferences/MapProjection.vue';

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
            'map-projection-preference': MapProjection,
        },
        setup(props, context) {
            const { t, locale } = useI18n();
            const route = useRoute();
            const toast = useToast();

            // FUNCTIONS
            const trackChanges = (label, data) => {
                if(!state.dirtyData[label]) {
                    state.dirtyData[label] = {
                        label: label,
                        value: data,
                    };
                }else{
                    state.dirtyData[label].value = data;
                }
            };
            const savePreferences = _ => {
                if(!state.hasDirtyData) return;

                let updatedLanguage = null;

                //TODO: Can't we just access state.dirtyData['prefs.gui-language'] directly?
                for(let label in state.dirtyData) {
                    const dirtyData = state.dirtyData[label];
                    if(label == 'prefs.gui-language') {
                        updatedLanguage = dirtyData.value;
                    }

                    /*
                     * Somehow in the UserPreferences a manual conversion of the
                     * map projection is required. This is not necessary in the
                     * Preferences component.
                     * 
                     * Otherwise there will be an 'array to text conversion' error -SO
                     */
                    if(label == 'prefs.map-projection') {
                        dirtyData.value = JSON.stringify(dirtyData.value);
                    }
                }
                const data = {
                    changes: Object.values(state.dirtyData),
                };
                patchPreferences(data, route.params.id).then(data => {
                    // Update language if value has changed
                    if(!!updatedLanguage) {
                        locale.value = updatedLanguage;
                    }
                    state.dirtyData = {};
    
                    const label = t('main.preference.toasts.updated.msg');
                    toast.$toast(label, '', {
                        duration: 2500,
                        autohide: true,
                        channel: 'success',
                        icon: true,
                        simple: true,
                    });
                });
            };

            // DATA
            const state = reactive({
                dirtyData: {},
                hasDirtyData: computed(_ => Object.keys(state.dirtyData).length > 0),
                preferences: computed(_ => store.getters.preferences),
                overrides: computed(_ => {
                    const sysPrefs = store.getters.systemPreferences;
                    const overrideList = {};
                    for(let k in sysPrefs) {
                        overrideList[k] = sysPrefs[k].allow_override;
                    }
                    return overrideList;
                }),
                prefsLoaded: computed(_ => !!state.preferences),
                browserLanguage: navigator.language ? navigator.language.split('-')[0] : 'en',
            });

            const preferencesConfig = [
                {
                    title: 'main.preference.key.language',
                    label:  'prefs.gui-language',
                    component: 'gui-language-preference',
                    data: 'v-model'
                },
                {
                    title: 'main.preference.key.color.title',
                    label:  'prefs.color',
                    component: 'color-preference',
                },
                {
                    title: 'main.preference.key.password_reset_link',
                    label:  'prefs.enable-password-reset-link',
                    component: 'reset-email-preference',
                },
                {
                    title: 'main.preference.key.columns.title',
                    label:  'prefs.columns',
                    component: 'columns-preference',
                },
                {
                    title: 'main.preference.key.tooltips',
                    label:  'prefs.show-tooltips',
                    component: 'tooltips-preference',
                },
                {
                    title: 'main.preference.key.tag_root',
                    label:  'prefs.tag-root',
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
                {
                    title: 'main.preference.key.map.projection',
                    label: 'prefs.map-projection',
                    component: 'map-projection-preference',
                }
            ]

            // RETURN
            return {
                t,
                // HELPERS
                can,
                getUser,
                // LOCAL
                trackChanges,
                savePreferences,
                // PROPS
                // STATE
                state,
                preferencesConfig,
            };
        },
    }
</script>
