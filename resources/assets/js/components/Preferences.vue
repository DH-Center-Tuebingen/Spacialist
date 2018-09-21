<template>
    <table class="table table-striped table-hover" v-if="initFinished" v-can="'edit_preferences'">
        <thead class="thead-light">
            <tr>
                <th>{{ $t('global.preference') }}</th>
                <th>{{ $t('global.value') }}</th>
                <th>{{ $t('global.allow-override') }}</th>
                <th>{{ $t('global.save') }}</th>
            </tr>
        </thead>
        <tbody>
            <tr>
                <td>
                    <strong>{{ $t('main.preference.key.language') }}</strong>
                </td>
                <td>
                    <form>
                        <div class="form-group row">
                            <label class="col-md-2 col-form-label"></label>
                            <div class="col-md-10">
                                <input class="form-control" type="text" v-model="preferences['prefs.gui-language'].value" />
                            </div>
                        </div>
                    </form>
                </td>
                <td>
                    <input type="checkbox"  v-model="preferences['prefs.gui-language'].allow_override" />
                </td>
                <td>
                    <button type="button" class="btn btn-success" :disabled="!$can('edit_preferences')" @click.prevent="savePreference(preferences['prefs.gui-language'])">
                        <i class="fas fa-fw fa-check"></i>
                    </button>
                </td>
            </tr>
            <tr>
                <td>
                    <strong>{{ $t('main.preference.key.columns.title') }}</strong>
                    <p class="alert alert-info mt-3">
                        {{ $t('main.preference.info.columns') }}
                    </p>
                </td>
                <td>
                    <form>
                        <div class="form-group row">
                            <label for="left-column" class="col-md-2 col-form-label">{{ $t('main.preference.key.columns.left') }}:</label>
                            <div class="col-md-10">
                                <input class="form-control" id="left-column" type="number" min="0" :max="getMax('left')" v-model="preferences['prefs.columns'].value.left" />
                            </div>
                        </div>
                        <div class="form-group row">
                            <label for="center-column" class="col-md-2 col-form-label">{{ $t('main.preference.key.columns.center') }}:</label>
                            <div class="col-md-10">
                                <input class="form-control" id="left-column" type="number" min="0" :max="getMax('center')" v-model="preferences['prefs.columns'].value.center" />
                            </div>
                        </div>
                        <div class="form-group row">
                            <label for="right-column" class="col-md-2 col-form-label">{{ $t('main.preference.key.columns.right') }}:</label>
                            <div class="col-md-10">
                                <input class="form-control" id="left-column" type="number" min="0" :max="getMax('right')" v-model="preferences['prefs.columns'].value.right" />
                            </div>
                        </div>
                    </form>
                </td>
                <td>
                    <input type="checkbox" v-model="preferences['prefs.columns'].allow_override" />
                </td>
                <td>
                    <button type="button" class="btn btn-success" :disabled="!$can('edit_preferences')" @click.prevent="savePreference(preferences['prefs.columns'])">
                        <i class="fas fa-fw fa-check"></i>
                    </button>
                </td>
            </tr>
            <tr>
                <td>
                    <strong>{{ $t('main.preference.key.tooltips') }}</strong>
                </td>
                <td>
                    <form>
                        <div class="form-group row">
                            <div class="col-md-2"></div>
                            <div class="col-md-10">
                                <div class="form-check">
                                    <input class="form-check-input" id="show-tooltips" type="checkbox" v-model="preferences['prefs.show-tooltips'].value" />
                                </div>
                            </div>
                        </div>
                    </form>
                </td>
                <td>
                    <input type="checkbox" v-model="preferences['prefs.show-tooltips'].allow_override" />
                </td>
                <td>
                    <button type="button" class="btn btn-success" :disabled="!$can('edit_preferences')" @click.prevent="savePreference(preferences['prefs.show-tooltips'])">
                        <i class="fas fa-fw fa-check"></i>
                    </button>
                </td>
            </tr>
            <tr>
                <td>
                    <strong>{{ $t('main.preference.key.tag-root') }}</strong>
                </td>
                <td>
                    <form>
                        <div class="form-group row">
                            <label class="col-md-2 col-form-label"></label>
                            <div class="col-md-10">
                                <input class="form-control" type="text" v-model="preferences['prefs.tag-root'].value" />
                            </div>
                        </div>
                    </form>
                </td>
                <td>
                    <input type="checkbox" v-model="preferences['prefs.tag-root'].allow_override" />
                </td>
                <td>
                    <button type="button" class="btn btn-success" :disabled="!$can('edit_preferences')" @click.prevent="savePreference(preferences['prefs.tag-root'])">
                        <i class="fas fa-fw fa-check"></i>
                    </button>
                </td>
            </tr>
            <tr>
                <td>
                    <strong>{{ $t('main.preference.key.extensions') }}</strong>
                </td>
                <td>
                    <form>
                        <div class="form-group row" v-for="(extension, key) in preferences['prefs.load-extensions'].value">
                            <div class="col-md-2"></div>
                            <div class="col-md-10">
                                <div class="form-check">
                                    <input type="checkbox" class="form-check-input" :id="'extension-'+key" v-model="preferences['prefs.load-extensions'].value[key]" />
                                    <label class="form-check-label" :for="'extension-'+key">
                                        {{ key }}
                                    </label>
                                </div>
                            </div>
                        </div>
                    </form>
                </td>
                <td>
                    <input type="checkbox" v-model="preferences['prefs.load-extensions'].allow_override" />
                </td>
                <td>
                    <button type="button" class="btn btn-success" :disabled="!$can('edit_preferences')" @click.prevent="savePreference(preferences['prefs.load-extensions'])">
                        <i class="fas fa-fw fa-check"></i>
                    </button>
                </td>
            </tr>
            <tr>
                <td>
                    <strong>{{ $t('main.preference.key.link-thesaurex') }}</strong>
                </td>
                <td>
                    <form>
                        <div class="form-group row">
                            <label class="col-md-2 col-form-label"></label>
                            <div class="col-md-10">
                                <input class="form-control" type="text" v-model="preferences['prefs.link-to-thesaurex'].value" />
                            </div>
                        </div>
                    </form>
                </td>
                <td>
                    <input type="checkbox" v-model="preferences['prefs.link-to-thesaurex'].allow_override" />
                </td>
                <td>
                    <button type="button" class="btn btn-success" :disabled="!$can('edit_preferences')" @click.prevent="savePreference(preferences['prefs.link-to-thesaurex'])">
                        <i class="fas fa-fw fa-check"></i>
                    </button>
                </td>
            </tr>
            <tr>
                <td>
                    <strong>{{ $t('main.preference.key.project.name') }}</strong>
                </td>
                <td>
                    <form>
                        <div class="form-group row">
                            <label class="col-md-2 col-form-label"></label>
                            <div class="col-md-10">
                                <input class="form-control" type="text" v-model="preferences['prefs.project-name'].value" />
                            </div>
                        </div>
                    </form>
                </td>
                <td>
                    <input type="checkbox" v-model="preferences['prefs.project-name'].allow_override" />
                </td>
                <td>
                    <button type="button" class="btn btn-success" :disabled="!$can('edit_preferences')" @click.prevent="savePreference(preferences['prefs.project-name'])">
                        <i class="fas fa-fw fa-check"></i>
                    </button>
                </td>
            </tr>
            <tr>
                <td>
                    <strong>{{ $t('main.preference.key.project.maintainer') }}</strong>
                </td>
                <td>
                    <form>
                        <div class="form-group row">
                            <label class="col-md-2 col-form-label">{{ $t('global.name') }}:</label>
                            <div class="col-md-10">
                                <input class="form-control" type="text" v-model="preferences['prefs.project-maintainer'].value.name" />
                            </div>
                        </div>
                        <div class="form-group row">
                            <label class="col-md-2 col-form-label">{{ $t('global.email') }}:</label>
                            <div class="col-md-10">
                                <input class="form-control" type="text" v-model="preferences['prefs.project-maintainer'].value.email" />
                            </div>
                        </div>
                        <div class="form-group row">
                            <label class="col-md-2 col-form-label">{{ $t('global.description') }}:</label>
                            <div class="col-md-10">
                                <input class="form-control" type="text" v-model="preferences['prefs.project-maintainer'].value.description" />
                            </div>
                        </div>
                        <div class="form-group row">
                            <label class="col-md-2 col-form-label" for="public">{{ $t('main.preference.key.project.public') }}:</label>
                            <div class="col-md-10">
                                <div class="form-check">
                                    <input type="checkbox" class="form-check-input" id="public" v-model="preferences['prefs.project-maintainer'].value.public" />
                                </div>
                            </div>
                        </div>
                    </form>
                </td>
                <td>
                    <input type="checkbox" v-model="preferences['prefs.project-maintainer'].allow_override" />
                </td>
                <td>
                    <button type="button" class="btn btn-success" :disabled="!$can('edit_preferences')" @click.prevent="savePreference(preferences['prefs.project-maintainer'])">
                        <i class="fas fa-fw fa-check"></i>
                    </button>
                </td>
            </tr>
            <tr v-if="preferences['prefs.load-extensions'].value.map">
                <td>
                    <strong>{{ $t('main.preference.key.map.projection') }}</strong>
                </td>
                <td>
                    <form>
                        <div class="form-group row">
                            <label class="col-md-2 col-form-label">{{ $t('main.preference.key.map.epsg') }}:</label>
                            <div class="col-md-10">
                                <input class="form-control" type="text" v-model="preferences['prefs.map-projection'].value.epsg" />
                            </div>
                        </div>
                    </form>
                </td>
                <td>
                    <input type="checkbox" v-model="preferences['prefs.map-projection'].allow_override" />
                </td>
                <td>
                    <button type="button" class="btn btn-success" :disabled="!$can('edit_preferences')" @click.prevent="savePreference(preferences['prefs.map-projection'])">
                        <i class="fas fa-fw fa-check"></i>
                    </button>
                </td>
            </tr>
        </tbody>
    </table>
</template>

<script>
    export default {
        beforeRouteEnter(to, from, next) {
            if(!Vue.prototype.$can('edit_preferences')) {
                next(vm => vm.init({}));
            }
            $httpQueue.add(() => $http.get('preference').then(response => {
                next(vm => vm.init(response.data));
            }));
        },
        mounted() {},
        methods: {
            init(preferences) {
                this.initFinished = false;
                this.preferences = preferences;
                this.initFinished = true;
            },
            getMax(column) {
                let columns = ['left', 'center', 'right'];
                const index = columns.findIndex(c => c == column);
                // if column is not in columns,
                // it is invalid
                if(index == -1) {
                    return;
                }
                columns.splice(index, 1);
                // Max width is 12 (grid size) - size of all other columns
                let max = 12;
                columns.forEach(c => {
                    max -= this.preferences['prefs.columns'].value[c];
                });
                return max;
            },
            savePreference(pref) {
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
                });
            }
        },
        data() {
            return {
                preferences: {},
                initFinished: false
            }
        },
    }
</script>
