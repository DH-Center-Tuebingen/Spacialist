<template>
    <div class="h-100 d-flex flex-column">
        <h3>
            {{ t('global.preference', 2) }}
            <button type="button" class="btn btn-outline-success" @click="savePreferences()">
                <i class="fas fa-fw fa-save"></i>
                {{ t('global.save') }}
            </button>
        </h3>
        <div class="table-responsive scroll-x-hidden">
            <table class="table table-light table-striped table-hover mb-0" v-if="state.prefsLoaded" v-dcan="'edit_preferences'">
                <thead class="sticky-top">
                    <tr class="text-nowrap">
                        <th>{{ t('global.preference') }}</th>
                        <th style="width: 99%;" class="text-end">{{ t('global.value') }}</th>
                        <th>{{ t('global.allow_override') }}</th>
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
                                :data="state.preferences['prefs.gui-language'].value"
                                @changed="e => trackChanges('prefs.gui-language', e)">
                            </gui-language-preference>
                        </td>
                        <td>
                            <div class="form-check form-switch d-flex justify-content-center">
                                <input class="form-check-input" type="checkbox" v-model="state.preferences['prefs.gui-language'].allow_override" />
                            </div>
                        </td>
                    </tr>
                    <tr>
                        <td>
                            <strong>{{ t('main.preference.key.password_reset_link') }}</strong>
                        </td>
                        <td>
                            <reset-email-preference
                                :data="state.preferences['prefs.enable-password-reset-link'].value"
                                @changed="e => trackChanges('prefs.enable-password-reset-link', e)">
                            </reset-email-preference>
                        </td>
                        <td>
                            <div class="form-check form-switch d-flex justify-content-center">
                                <input class="form-check-input" type="checkbox" v-model="state.preferences['prefs.enable-password-reset-link'].allow_override" />
                            </div>
                        </td>
                    </tr>
                    <tr>
                        <td>
                            <strong>{{ t('main.preference.key.columns.title') }}</strong>
                        </td>
                        <td>
                            <columns-preference
                                :data="state.preferences['prefs.columns'].value"
                                @changed="e => trackChanges('prefs.columns', e)">
                            </columns-preference>
                        </td>
                        <td>
                            <div class="form-check form-switch d-flex justify-content-center">
                                <input class="form-check-input" type="checkbox" v-model="state.preferences['prefs.columns'].allow_override" />
                            </div>
                        </td>
                    </tr>
                    <tr>
                        <td>
                            <strong>{{ t('main.preference.key.tooltips') }}</strong>
                        </td>
                        <td>
                            <tooltips-preference
                                :data="state.preferences['prefs.show-tooltips'].value"
                                @changed="e => trackChanges('prefs.show-tooltips', e)">
                            </tooltips-preference>
                        </td>
                        <td>
                            <div class="form-check form-switch d-flex justify-content-center">
                                <input class="form-check-input" type="checkbox" v-model="state.preferences['prefs.show-tooltips'].allow_override" />
                            </div>
                        </td>
                    </tr>
                    <tr>
                        <td>
                            <strong>{{ t('main.preference.key.tag_root') }}</strong>
                        </td>
                        <td>
                            <tags-preference
                                :data="state.preferences['prefs.tag-root'].value"
                                @changed="e => trackChanges('prefs.tag-root', e)">
                            </tags-preference>
                        </td>
                        <td>
                            <div class="form-check form-switch d-flex justify-content-center">
                                <input class="form-check-input" type="checkbox" v-model="state.preferences['prefs.tag-root'].allow_override" />
                            </div>
                        </td>
                    </tr>
                    <tr>
                        <td>
                            <strong>{{ t('main.preference.key.extensions') }}</strong>
                        </td>
                        <td>
                            <extensions-preference
                                :data="state.preferences['prefs.load-extensions'].value"
                                @changed="e => trackChanges('prefs.load-extensions', e)">
                            </extensions-preference>
                        </td>
                        <td>
                            <div class="form-check form-switch d-flex justify-content-center">
                                <input class="form-check-input" type="checkbox" v-model="state.preferences['prefs.load-extensions'].allow_override" />
                            </div>
                        </td>
                    </tr>
                    <tr>
                        <td>
                            <strong>{{ t('main.preference.key.link_thesaurex') }}</strong>
                        </td>
                        <td>
                            <thesaurus-link-preference
                                :data="state.preferences['prefs.link-to-thesaurex'].value"
                                @changed="e => trackChanges('prefs.link-to-thesaurex', e)">
                            </thesaurus-link-preference>
                        </td>
                        <td>
                            <div class="form-check form-switch d-flex justify-content-center">
                                <input class="form-check-input" type="checkbox" v-model="state.preferences['prefs.link-to-thesaurex'].allow_override" />
                            </div>
                        </td>
                    </tr>
                    <tr>
                        <td>
                            <strong>{{ t('main.preference.key.project.name') }}</strong>
                        </td>
                        <td>
                            <project-name-preference
                                :data="state.preferences['prefs.project-name'].value"
                                @changed="e => trackChanges('prefs.project-name', e)">
                            </project-name-preference>
                        </td>
                        <td>
                            <div class="form-check form-switch d-flex justify-content-center">
                                <input class="form-check-input" type="checkbox" v-model="state.preferences['prefs.project-name'].allow_override" />
                            </div>
                        </td>
                    </tr>
                    <tr>
                        <td>
                            <strong>{{ t('main.preference.key.project.maintainer') }}</strong>
                        </td>
                        <td>
                            <project-maintainer-preference
                                :data="state.preferences['prefs.project-maintainer'].value"
                                @changed="e => trackChanges('prefs.project-maintainer', e)">
                            </project-maintainer-preference>
                        </td>
                        <td>
                            <div class="form-check form-switch d-flex justify-content-center">
                                <input class="form-check-input" type="checkbox" v-model="state.preferences['prefs.project-maintainer'].allow_override" />
                            </div>
                        </td>
                    </tr>
                    <tr v-if="state.preferences['prefs.load-extensions'].value.map">
                        <td>
                            <strong>{{ t('main.preference.key.map.projection') }}</strong>
                        </td>
                        <td>
                            <map-projection-preference
                                :data="state.preferences['prefs.map-projection'].value"
                                @changed="e => trackChanges('prefs.map-projection', e)">
                            </map-projection-preference>
                        </td>
                        <td>
                            <div class="form-check form-switch d-flex justify-content-center">
                                <input class="form-check-input" type="checkbox" v-model="state.preferences['prefs.map-projection'].allow_override" />
                            </div>
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

    import { useI18n } from 'vue-i18n';

    import store from '../bootstrap/store.js';

    import { useToast } from '../plugins/toast.js';

    import { patchPreferences } from '../api.js';

    import {
        can,
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
            const { t } = useI18n();
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
                        const userLang = store.getters.preferenceByKey('prefs.gui-language');
                        const sysLang = state.preferences['prefs.gui-language'];
                        // if user pref language does not differ from sys pref language
                        if(userLang === sysLang) {
                            // update current language in Spacialist
                            updatedLanguage = dd.value;
                        }
                    }
                    entries.push({
                        value: dd.value,
                        allow_override: state.preferences[k].allow_override,
                        label: k,
                    });
                }
                const data = {
                    changes: entries,
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
            };
        },
    }
</script>
