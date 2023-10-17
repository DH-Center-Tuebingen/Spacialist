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
                {{ t('global.save') }}
            </button>
        </h3>
        <div class="table-responsive scroll-x-hidden">
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
                                :browser-default="true"
                                @changed="e => trackChanges('prefs.gui-language', e)"
                            />
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
                                @changed="e => trackChanges('prefs.enable-password-reset-link', e)"
                            />
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
                                @changed="e => trackChanges('prefs.columns', e)"
                            />
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
                                @changed="e => trackChanges('prefs.show-tooltips', e)"
                            />
                        </td>
                    </tr>
                    <tr>
                        <td>
                            <strong>{{ t('main.preference.key.tag_root') }}</strong>
                        </td>
                        <td>
                            <tags-preference
                                :data="state.preferences['prefs.tag-root']"
                                :readonly="!state.overrides['prefs.tag-root']"
                                @changed="e => trackChanges('prefs.tag-root', e)"
                            />
                        </td>
                    </tr>
                    <tr>
                        <td>
                            <strong>{{ t('main.preference.key.link_thesaurex') }}</strong>
                        </td>
                        <td>
                            <thesaurus-link-preference
                                :data="state.preferences['prefs.link-to-thesaurex']"
                                :readonly="!state.overrides['prefs.link-to-thesaurex']"
                                @changed="e => trackChanges('prefs.link-to-thesaurex', e)"
                            />
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
                                @changed="e => trackChanges('prefs.project-name', e)"
                            />
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
                                @changed="e => trackChanges('prefs.project-maintainer', e)"
                            />
                        </td>
                    </tr>
                    <tr>
                        <td>
                            <strong>{{ t('main.preference.key.map.projection') }}</strong>
                        </td>
                        <td>
                            <map-projection-preference
                                :data="state.preferences['prefs.map-projection']"
                                :readonly="!state.overrides['prefs.map-projection']"
                                @changed="e => trackChanges('prefs.map-projection', e)"
                            />
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

    import store from '@/bootstrap/store.js';

    import { useToast } from '@/plugins/toast.js';

    import { patchPreferences } from '@/api.js';

    import {
        can,
        getUser,
    } from '@/helpers/helpers.js';

    import GuiLanguage from '@/components/preferences/GuiLanguage.vue';
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
                state.preferences[label] = data.value;
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
