<template>
    <div class="h-100 d-flex flex-column">
        <h3>
            {{ t('global.preference', 2) }}
            <small class="text-muted">
                {{ getUser().name }}
            </small>
            <button type="button" class="btn btn-outline-success ms-2" @click="savePreferences()">
                <i class="fas fa-fw fa-save"></i>
                {{ t('global.save') }}
            </button>
        </h3>
        <div class="table-responsive scroll-x-hidden">
            <table class="table table-light table-striped table-hover mb-0" v-if="state.prefsLoaded" v-dcan="'edit_preferences'">
                <thead class="sticky-top">
                    <tr class="text-nowrap">
                        <th>{{ t('global.preference') }}</th>
                        <th style="width: 99%;">{{ t('global.value') }}</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td>
                            <strong>
                                {{ t('main.preference.key.language') }}
                            </strong>
                        </td>
                        <td>
                            <gui-language-preference
                                :data="state.preferences['prefs.gui-language']"
                                :readonly="!state.overrides['prefs.gui-language']"
                                @changed="e => trackChanges('prefs.gui-language', e)">
                            </gui-language-preference>
                        </td>
                    </tr>
                    <tr>
                        <td>
                            <strong>{{ t('main.preference.key.password_reset_link') }}</strong>
                        </td>
                        <td>
                            <reset-email-preference
                                :data="state.preferences['prefs.enable-password-reset-link']"
                                :readonly="!state.overrides['prefs.enable-password-reset-link']"
                                @changed="e => trackChanges('prefs.enable-password-reset-link', e)">
                            </reset-email-preference>
                        </td>
                    </tr>
                    <tr>
                        <td>
                            <strong>{{ t('main.preference.key.columns.title') }}</strong>
                        </td>
                        <td>
                            <columns-preference
                                :data="state.preferences['prefs.columns']"
                                :readonly="!state.overrides['prefs.columns']"
                                @changed="e => trackChanges('prefs.columns', e)">
                            </columns-preference>
                        </td>
                    </tr>
                    <tr>
                        <td>
                            <strong>{{ t('main.preference.key.tooltips') }}</strong>
                        </td>
                        <td>
                            <tooltips-preference
                                :data="state.preferences['prefs.show-tooltips']"
                                :readonly="!state.overrides['prefs.show-tooltips']"
                                @changed="e => trackChanges('prefs.show-tooltips', e)">
                            </tooltips-preference>
                        </td>
                    </tr>
                    <tr>
                        <td>
                            <strong>{{ t('main.preference.key.tag-root') }}</strong>
                        </td>
                        <td>
                            <tags-preference
                                :data="state.preferences['prefs.tag-root']"
                                :readonly="!state.overrides['prefs.tag-root']"
                                @changed="e => trackChanges('prefs.tag-root', e)">
                            </tags-preference>
                        </td>
                    </tr>
                    <tr>
                        <td>
                            <strong>{{ t('main.preference.key.extensions') }}</strong>
                        </td>
                        <td>
                            <extensions-preference
                                :data="state.preferences['prefs.load-extensions']"
                                :readonly="!state.overrides['prefs.load-extensions']"
                                @changed="e => trackChanges('prefs.load-extensions', e)">
                            </extensions-preference>
                        </td>
                    </tr>
                    <tr>
                        <td>
                            <strong>{{ t('main.preference.key.link-thesaurex') }}</strong>
                        </td>
                        <td>
                            <thesaurus-link-preference
                                :data="state.preferences['prefs.link-to-thesaurex']"
                                :readonly="!state.overrides['prefs.link-to-thesaurex']"
                                @changed="e => trackChanges('prefs.link-to-thesaurex', e)">
                            </thesaurus-link-preference>
                        </td>
                    </tr>
                    <tr>
                        <td>
                            <strong>{{ t('main.preference.key.project.name') }}</strong>
                        </td>
                        <td>
                            <project-name-preference
                                :data="state.preferences['prefs.project-name']"
                                :readonly="!state.overrides['prefs.project-name']"
                                @changed="e => trackChanges('prefs.project-name', e)">
                            </project-name-preference>
                        </td>
                    </tr>
                    <tr>
                        <td>
                            <strong>{{ t('main.preference.key.project.maintainer') }}</strong>
                        </td>
                        <td>
                            <project-maintainer-preference
                                :data="state.preferences['prefs.project-maintainer']"
                                :readonly="!state.overrides['prefs.project-maintainer']"
                                @changed="e => trackChanges('prefs.project-maintainer', e)">
                            </project-maintainer-preference>
                        </td>
                    </tr>
                    <tr v-if="state.preferences['prefs.load-extensions'].map">
                        <td>
                            <strong>{{ t('main.preference.key.map.projection') }}</strong>
                        </td>
                        <td>
                            <map-projection-preference
                                :data="state.preferences['prefs.map-projection']"
                                :readonly="!state.overrides['prefs.map-projection']"
                                @changed="e => trackChanges('prefs.map-projection', e)">
                            </map-projection-preference>
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>
</template>

<script>
    import {
        computed,
        reactive,
    } from 'vue';

    import {
        useRoute,
    } from 'vue-router';

    import { useI18n } from 'vue-i18n';

    import store from '../bootstrap/store.js';

    import { useToast } from '../plugins/toast.js';

    import { patchPreferences } from '../api.js';

    import {
        can,
        getUser,
    } from '../helpers/helpers.js';

    import GuiLanguage from './preferences/GuiLanguage.vue';
    import ResetEmail from './preferences/ResetEmail.vue';
    import Columns from './preferences/Columns.vue';
    import ShowTooltips from './preferences/ShowTooltips.vue';
    import Tags from './preferences/Tags.vue';
    import Extensions from './preferences/Extensions.vue';
    import ThesaurusLink from './preferences/ThesaurusLink.vue';
    import ProjectName from './preferences/ProjectName.vue';
    import ProjectMaintainer from './preferences/ProjectMaintainer.vue';
    import MapProjection from './preferences/MapProjection.vue';

    export default {
        components: {
            'gui-language-preference': GuiLanguage,
            'reset-email-preference': ResetEmail,
            'columns-preference': Columns,
            'tooltips-preference': ShowTooltips,
            'tags-preference': Tags,
            'extensions-preference': Extensions,
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
                state.dirtyData[label] = {
                    value: data.value,
                };
            };
            const savePreferences = _ => {
                if(!state.hasDirtyData) return;

                let entries = [];
                let updatedLanguage = null;
                for(let k in state.dirtyData) {
                    const dd = state.dirtyData[k];
                    if(k == 'prefs.gui-language') {
                        updatedLanguage = dd.value;
                    }
                    entries.push({
                        value: dd.value,
                        label: k,
                    });
                }
                const data = {
                    changes: entries,
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
            };
        },
    }
</script>
