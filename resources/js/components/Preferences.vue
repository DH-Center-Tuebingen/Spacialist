<template>
    <div class="h-100 d-flex flex-column">
        <h3>
            {{ t('global.preference', 2) }}
            <button type="button" class="btn btn-outline-success">
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
                        <th>{{ t('global.allow-override') }}</th>
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
                                :data="state.preferences['prefs.gui-language'].value">
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
                                :data="state.preferences['prefs.enable-password-reset-link'].value">
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
                                :data="state.preferences['prefs.columns'].value">
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
                                :data="state.preferences['prefs.show-tooltips'].value">
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
                            <strong>{{ t('main.preference.key.tag-root') }}</strong>
                        </td>
                        <td>
                            <tags-preference
                                :data="state.preferences['prefs.tag-root'].value">
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
                                :data="state.preferences['prefs.load-extensions'].value">
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
                            <strong>{{ t('main.preference.key.link-thesaurex') }}</strong>
                        </td>
                        <td>
                            <thesaurus-link-preference
                                :data="state.preferences['prefs.link-to-thesaurex'].value">
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
                                :data="state.preferences['prefs.project-name'].value">
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
                                :data="state.preferences['prefs.project-maintainer'].value">
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
                                :data="state.preferences['prefs.map-projection'].value">
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

    import { useI18n } from 'vue-i18n';

    import store from '../bootstrap/store.js';

    import {
        can,
    } from '../helpers/helpers.js';

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

            // FUNCTIONS
            const savePreference = pref => {
                return;
                // TODO
                if(!this.$can('edit_preferences')) return;
                let data = {};
                data.label = pref.label;
                data.value = pref.value;
                if(typeof data.value === 'object') data.value = JSON.stringify(data.value);
                data.allow_override = pref.allow_override;
                $http.patch(`preference/${pref.id}`, data).then(response => {
                    const label = this.$t(`main.preference.labels.${pref.label}`);
                    this.$showToast(
                        this.$t('main.preference.toasts.updated.title'),
                        this.$t('main.preference.toasts.updated.msg', {
                            name: label
                        }),
                        'success'
                    );
                    this.$setPreference(pref.label, pref.value);
                });
            };

            // DATA
            const state = reactive({
                preferences: computed(_ => store.getters.systemPreferences),
                prefsLoaded: computed(_ => !!state.preferences),
            });

            // RETURN
            return {
                t,
                // HELPERS
                can,
                // LOCAL
                savePreference,
                // PROPS
                // STATE
                state,
            };
        },
    }
</script>
