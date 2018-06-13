<template>
    <table class="table table-striped table-hover">
        <thead class="thead-light">
            <tr>
                <th>Preference</th>
                <th>Value</th>
                <th>Save</th>
            </tr>
        </thead>
        <tbody>
            <tr>
                <td>
                    <strong>Language</strong>
                </td>
                <td>
                    <form>
                        <div class="form-group row">
                            <label class="col-md-2 col-form-label"></label>
                            <div class="col-md-10">
                                <input type="text" :readonly="!localPreferences['prefs.gui-language'].allow_override" :class="[localPreferences['prefs.gui-language'].allow_override ? 'form-control' : 'form-control-plaintext']" v-model="localPreferences['prefs.gui-language'].value" />
                            </div>
                        </div>
                    </form>
                </td>
                <td>
                    <button type="button" class="btn btn-success" @click="savePreference(localPreferences['prefs.gui-language'])" :disabled="!localPreferences['prefs.gui-language'].allow_override">
                        <i class="fas fa-fw fa-check"></i>
                    </button>
                </td>
            </tr>
            <tr>
                <td>
                    <strong>Columns in Main View</strong>
                </td>
                <td>
                    <form>
                        <div class="form-group row">
                            <label for="left-column" class="col-md-2 col-form-label">Left-Hand Column:</label>
                            <div class="col-md-10">
                                <input id="left-column" type="number" :readonly="!localPreferences['prefs.columns'].allow_override" :class="[localPreferences['prefs.columns'].allow_override ? 'form-control' : 'form-control-plaintext']" v-model="localPreferences['prefs.columns'].value.left" />
                            </div>
                        </div>
                        <div class="form-group row">
                            <label for="center-column" class="col-md-2 col-form-label">Center Column:</label>
                            <div class="col-md-10">
                                <input id="center-column" type="number" :readonly="!localPreferences['prefs.columns'].allow_override" :class="[localPreferences['prefs.columns'].allow_override ? 'form-control' : 'form-control-plaintext']" v-model="localPreferences['prefs.columns'].value.center" />
                            </div>
                        </div>
                        <div class="form-group row">
                            <label for="right-column" class="col-md-2 col-form-label">Right-Hand Column:</label>
                            <div class="col-md-10">
                                <input id="right-column" type="number" :readonly="!localPreferences['prefs.columns'].allow_override" :class="[localPreferences['prefs.columns'].allow_override ? 'form-control' : 'form-control-plaintext']" v-model="localPreferences['prefs.columns'].value.right" />
                            </div>
                        </div>
                    </form>
                </td>
                <td>
                    <button type="button" class="btn btn-success" @click="savePreference(localPreferences['prefs.columns'])" :disabled="!localPreferences['prefs.columns'].allow_override">
                        <i class="fas fa-fw fa-check"></i>
                    </button>
                </td>
            </tr>
            <tr>
                <td>
                    <strong>Show Tooltips</strong>
                </td>
                <td>
                    <form>
                        <div class="form-group row">
                            <div class="col-md-2"></div>
                            <div class="col-md-10">
                                <div class="form-check">
                                    <input class="form-check-input" id="show-tooltips" type="checkbox" :disabled="!localPreferences['prefs.show-tooltips'].allow_override" v-model="localPreferences['prefs.show-tooltips'].value" />
                                </div>
                            </div>
                        </div>
                    </form>
                </td>
                <td>
                    <button type="button" class="btn btn-success" @click="savePreference(localPreferences['prefs.show-tooltips'])" :disabled="!localPreferences['prefs.show-tooltips'].allow_override">
                        <i class="fas fa-fw fa-check"></i>
                    </button>
                </td>
            </tr>
            <tr>
                <td>
                    <strong>Thesaurus-Element for Tags</strong>
                </td>
                <td>
                    <form>
                        <div class="form-group row">
                            <label class="col-md-2 col-form-label"></label>
                            <div class="col-md-10">
                                <input type="text" :readonly="!localPreferences['prefs.tag-root'].allow_override" :class="[localPreferences['prefs.tag-root'].allow_override ? 'form-control' : 'form-control-plaintext']" v-model="localPreferences['prefs.tag-root'].value" />
                            </div>
                        </div>
                    </form>
                </td>
                <td>
                    <button type="button" class="btn btn-success" @click="savePreference(localPreferences['prefs.tag-root'])" :disabled="!localPreferences['prefs.tag-root'].allow_override">
                        <i class="fas fa-fw fa-check"></i>
                    </button>
                </td>
            </tr>
            <tr>
                <td>
                    <strong>Loaded Extensions</strong>
                </td>
                <td>
                    <form>
                        <div class="form-group row" v-for="(extension, key) in localPreferences['prefs.load-extensions'].value">
                            <div class="col-md-2"></div>
                            <div class="col-md-10">
                                <div class="form-check">
                                    <input type="checkbox" class="form-check-input" :id="'extension-'+key" :checked="extension" :disabled="!localPreferences['prefs.load-extensions'].allow_override" />
                                    <label class="form-check-label" :for="'extension-'+key">
                                        {{ key }}
                                    </label>
                                </div>
                            </div>
                        </div>
                    </form>
                </td>
                <td>
                    <button type="button" class="btn btn-success" @click="savePreference(localPreferences['prefs.load-extensions'])" :disabled="!localPreferences['prefs.load-extensions'].allow_override">
                        <i class="fas fa-fw fa-check"></i>
                    </button>
                </td>
            </tr>
            <tr>
                <td>
                    <strong>Show Link To ThesauRex</strong>
                </td>
                <td>
                    <form>
                        <div class="form-group row">
                            <label class="col-md-2 col-form-label"></label>
                            <div class="col-md-10">
                                <input type="text" :readonly="!localPreferences['prefs.link-to-thesaurex'].allow_override" :class="[localPreferences['prefs.link-to-thesaurex'].allow_override ? 'form-control' : 'form-control-plaintext']" v-model="localPreferences['prefs.link-to-thesaurex'].value" />
                            </div>
                        </div>
                    </form>
                </td>
                <td>
                    <button type="button" class="btn btn-success" @click="savePreference(localPreferences['prefs.link-to-thesaurex'])" :disabled="!localPreferences['prefs.link-to-thesaurex'].allow_override">
                        <i class="fas fa-fw fa-check"></i>
                    </button>
                </td>
            </tr>
            <tr>
                <td>
                    <strong>Project Name</strong>
                </td>
                <td>
                    <form>
                        <div class="form-group row">
                            <label class="col-md-2 col-form-label"></label>
                            <div class="col-md-10">
                                <input type="text" :readonly="!localPreferences['prefs.project-name'].allow_override" :class="[localPreferences['prefs.project-name'].allow_override ? 'form-control' : 'form-control-plaintext']" v-model="localPreferences['prefs.project-name'].value" />
                            </div>
                        </div>
                    </form>
                </td>
                <td>
                    <button type="button" class="btn btn-success" @click="savePreference(localPreferences['prefs.project-name'])" :disabled="!localPreferences['prefs.project-name'].allow_override">
                        <i class="fas fa-fw fa-check"></i>
                    </button>
                </td>
            </tr>
            <tr>
                <td>
                    <strong>Project Maintainer</strong>
                </td>
                <td>
                    <form>
                        <div class="form-group row">
                            <label class="col-md-2 col-form-label">Name:</label>
                            <div class="col-md-10">
                                <input type="text" :readonly="!localPreferences['prefs.project-maintainer'].allow_override" :class="[localPreferences['prefs.project-maintainer'].allow_override ? 'form-control' : 'form-control-plaintext']" v-model="localPreferences['prefs.project-maintainer'].value.name" />
                            </div>
                        </div>
                        <div class="form-group row">
                            <label class="col-md-2 col-form-label">E-Mail-Address:</label>
                            <div class="col-md-10">
                                <input type="text" :readonly="!localPreferences['prefs.project-maintainer'].allow_override" :class="[localPreferences['prefs.project-maintainer'].allow_override ? 'form-control' : 'form-control-plaintext']" v-model="localPreferences['prefs.project-maintainer'].value.email" />
                            </div>
                        </div>
                        <div class="form-group row">
                            <label class="col-md-2 col-form-label">Description:</label>
                            <div class="col-md-10">
                                <input type="text" :readonly="!localPreferences['prefs.project-maintainer'].allow_override" :class="[localPreferences['prefs.project-maintainer'].allow_override ? 'form-control' : 'form-control-plaintext']" v-model="localPreferences['prefs.project-maintainer'].value.description" />
                            </div>
                        </div>
                        <div class="form-group row">
                            <label class="col-md-2 col-form-label" for="public">Public?</label>
                            <div class="col-md-10">
                                <div class="form-check">
                                    <input type="checkbox" class="form-check-input" id="public" :disabled="!localPreferences['prefs.project-maintainer'].allow_override" v-model="localPreferences['prefs.project-maintainer'].value.public" />
                                </div>
                            </div>
                        </div>
                    </form>
                </td>
                <td>
                    <button type="button" class="btn btn-success" @click="savePreference(localPreferences['prefs.project-maintainer'])" :disabled="!localPreferences['prefs.project-maintainer'].allow_override">
                        <i class="fas fa-fw fa-check"></i>
                    </button>
                </td>
            </tr>
            <tr v-if="localPreferences['prefs.load-extensions'].value.map">
                <td>
                    <strong>Map Projection</strong>
                </td>
                <td>
                    <form>
                        <div class="form-group row">
                            <label class="col-md-2 col-form-label">EPSG-Code:</label>
                            <div class="col-md-10">
                                <input type="text" :readonly="!localPreferences['prefs.map-projection'].allow_override" :class="[localPreferences['prefs.map-projection'].allow_override ? 'form-control' : 'form-control-plaintext']" v-model="localPreferences['prefs.map-projection'].value.epsg" />
                            </div>
                        </div>
                    </form>
                </td>
                <td>
                    <button type="button" class="btn btn-success" @click="savePreference(localPreferences['prefs.map-projection'])" :disabled="!localPreferences['prefs.map-projection'].allow_override">
                        <i class="fas fa-fw fa-check"></i>
                    </button>
                </td>
            </tr>
        </tbody>
    </table>
</template>

<script>
    export default {
        props: ['preferences', 'userId'],
        mounted() {},
        methods: {
            savePreference(pref) {
                const vm = this;
                let data = {};
                data.label = pref.label;
                data.value = pref.value;
                if(typeof data.value === 'object') data.value = JSON.stringify(data.value);
                data.user_id = vm.userId;
                vm.$http.patch('/api/preference/' + pref.id, data).then(function(response) {
                    const label = pref.label; // TODO translation
                    vm.$showToast('Preference updated', `${label} successfully updated.`, 'success');
                }).catch(function(error) {
                    if(error.response) {
                        const r = error.response;
                        vm.$showErrorModal(r.data, r.status, r.headers);
                    } else if(error.request) {
                        vm.$showErrorModal(error.request);
                    } else {
                        vm.$showErrorModal(error.message);
                    }
                });
            }
        },
        data() {
            return {
                localPreferences: Object.assign({}, this.preferences)
            }
        },
    }
</script>
