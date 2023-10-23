<template>
    <!-- eslint-disable vue/no-unused-components -->
    <div class="h-100 d-flex flex-column">
        <h3 class="d-flex flex-row gap-2 align-items-center">
            {{ t('global.preference', 2) }}
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
                v-dcan="'preferences_write'"
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
                        <th>{{ t('global.allow_override') }}</th>
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
                                v-model="state.preferences[preferencesBlock.label].value"
                                @changed="e => trackChanges(preferencesBlock.label,'value' , e)"
                            />
                            <component
                                :is="preferencesBlock.component"
                                v-else
                                :data="state.preferences[preferencesBlock.label].value"
                                @changed="e => trackChanges(preferencesBlock.label,'value' , e)"
                            />
                        </td><td>
                            <div class="form-check form-switch d-flex justify-content-center">
                                <Checkbox
                                    v-model="state.preferences[preferencesBlock.label].allow_override"
                                    @changed="value => trackChanges(preferencesBlock.label, 'allow_override', value)"
                                />
                            </div>
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

    import { useI18n } from 'vue-i18n';

    import store from '@/bootstrap/store.js';

    import { useToast } from '@/plugins/toast.js';

    import { patchPreferences } from '@/api.js';

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
    import MapProjection from '@/components/preferences/MapProjection.vue';
    import Checkbox from './forms/Checkbox.vue';



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
            Checkbox,
        },
        setup(props, context) {
            const { t } = useI18n();
            const toast = useToast();

            // FUNCTIONS
            const trackChanges = (label, key, data) => {

                if(!state.dirtyData[label]) {

                    // The endpoint expect all values to be set. 
                    // If any of those change they will be overwritten.
                    state.dirtyData[label] = {
                        label,
                        value: state.preferences[label].value,
                        allow_override: state.preferences[label].allow_override,
                    } 
                }
                
                state.dirtyData[label][key] = data
            };

            const savePreferences = _ => {
                if(!state.hasDirtyData) return;

                let updatedLanguage = null;
                for(let label in state.dirtyData) {
                    const dirtyData = state.dirtyData[label];
                    if(label == 'prefs.gui-language' && dirtyData.value) {
                        const userLang = store.getters.preferenceByKey('prefs.gui-language');
                        const sysLang = state.preferences['prefs.gui-language'];
                        // if user pref language does not differ from sys pref language
                        if(userLang === sysLang) {
                            // update current language in Spacialist
                            updatedLanguage = dirtyData.value;
                        }
                    }
                }
                const data = {
                    changes: Object.values(state.dirtyData),
                };

                patchPreferences(data).then(data => {
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

            // DATA
            const state = reactive({
                dirtyData: {},
                hasDirtyData: computed(_ => Object.keys(state.dirtyData).length > 0),
                preferences: computed(_ => store.getters.systemPreferences),
                prefsLoaded: computed(_ => !!state.preferences),
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
