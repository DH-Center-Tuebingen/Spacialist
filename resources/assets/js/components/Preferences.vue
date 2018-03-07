<template>
    <table class="table table-striped table-hover">
        <thead class="thead-light">
            <tr>
                <th>Preference</th>
                <th>Value</th>
                <th>Allow Override?</th>
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
                                <input class="form-control" type="text" v-model="localPreferences['prefs.gui-language'].value" />
                            </div>
                        </div>
                    </form>
                </td>
                <td>
                    <input type="checkbox"  v-model="localPreferences['prefs.gui-language'].allow_override" />
                </td>
                <td>
                    <button type="button" class="btn btn-success" @click="savePreference(localPreferences['prefs.gui-language'])">
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
                                <input class="form-control" id="left-column" type="number" v-model="localPreferences['prefs.columns'].value.left" />
                            </div>
                        </div>
                        <div class="form-group row">
                            <label for="center-column" class="col-md-2 col-form-label">Center Column:</label>
                            <div class="col-md-10">
                                <input class="form-control" id="left-column" type="number" v-model="localPreferences['prefs.columns'].value.center" />
                            </div>
                        </div>
                        <div class="form-group row">
                            <label for="right-column" class="col-md-2 col-form-label">Right-Hand Column:</label>
                            <div class="col-md-10">
                                <input class="form-control" id="left-column" type="number" v-model="localPreferences['prefs.columns'].value.right" />
                            </div>
                        </div>
                    </form>
                </td>
                <td>
                    <input type="checkbox" v-model="localPreferences['prefs.columns'].allow_override" />
                </td>
                <td>
                    <button type="button" class="btn btn-success" @click="savePreference(localPreferences['prefs.columns'])">
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
                                    <input class="form-check-input" id="show-tooltips" type="checkbox" v-model="localPreferences['prefs.show-tooltips'].value" />
                                </div>
                            </div>
                        </div>
                    </form>
                </td>
                <td>
                    <input type="checkbox" v-model="localPreferences['prefs.show-tooltips'].allow_override" />
                </td>
                <td>
                    <button type="button" class="btn btn-success" @click="savePreference(localPreferences['prefs.show-tooltips'])">
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
                                <input class="form-control" type="text" v-model="localPreferences['prefs.tag-root'].value" />
                            </div>
                        </div>
                    </form>
                </td>
                <td>
                    <input type="checkbox" v-model="localPreferences['prefs.tag-root'].allow_override" />
                </td>
                <td>
                    <button type="button" class="btn btn-success" @click="savePreference(localPreferences['prefs.tag-root'])">
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
                                    <input type="checkbox" class="form-check-input" :id="'extension-'+key" :checked="extension" />
                                    <label class="form-check-label" :for="'extension-'+key">
                                        {{ key }}
                                    </label>
                                </div>
                            </div>
                        </div>
                    </form>
                </td>
                <td>
                    <input type="checkbox" v-model="localPreferences['prefs.load-extensions'].allow_override" />
                </td>
                <td>
                    <button type="button" class="btn btn-success" @click="savePreference(localPreferences['prefs.load-extensions'])">
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
                                <input class="form-control" type="text" v-model="localPreferences['prefs.link-to-thesaurex'].value" />
                            </div>
                        </div>
                    </form>
                </td>
                <td>
                    <input type="checkbox" v-model="localPreferences['prefs.link-to-thesaurex'].allow_override" />
                </td>
                <td>
                    <button type="button" class="btn btn-success" @click="savePreference(localPreferences['prefs.link-to-thesaurex'])">
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
                                <input class="form-control" type="text" v-model="localPreferences['prefs.project-name'].value" />
                            </div>
                        </div>
                    </form>
                </td>
                <td>
                    <input type="checkbox" v-model="localPreferences['prefs.project-name'].allow_override" />
                </td>
                <td>
                    <button type="button" class="btn btn-success" @click="savePreference(localPreferences['prefs.project-name'])">
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
                                <input class="form-control" type="text" v-model="localPreferences['prefs.project-maintainer'].value.name" />
                            </div>
                        </div>
                        <div class="form-group row">
                            <label class="col-md-2 col-form-label">E-Mail-Address:</label>
                            <div class="col-md-10">
                                <input class="form-control" type="text" v-model="localPreferences['prefs.project-maintainer'].value.email" />
                            </div>
                        </div>
                        <div class="form-group row">
                            <label class="col-md-2 col-form-label">Description:</label>
                            <div class="col-md-10">
                                <input class="form-control" type="text" v-model="localPreferences['prefs.project-maintainer'].value.description" />
                            </div>
                        </div>
                        <div class="form-group row">
                            <label class="col-md-2 col-form-label" for="public">Public?</label>
                            <div class="col-md-10">
                                <div class="form-check">
                                    <input type="checkbox" class="form-check-input" id="public" v-model="localPreferences['prefs.project-maintainer'].value.public" />
                                </div>
                            </div>
                        </div>
                    </form>
                </td>
                <td>
                    <input type="checkbox" v-model="localPreferences['prefs.project-maintainer'].allow_override" />
                </td>
                <td>
                    <button type="button" class="btn btn-success" @click="savePreference(localPreferences['prefs.project-maintainer'])">
                        <i class="fas fa-fw fa-check"></i>
                    </button>
                </td>
            </tr>
        </tbody>
    </table>
</template>

<script>
    export default {
        props: ['preferences'],
        mounted() {},
        methods: {
            savePreference(pref) {
                let data = {};
                data.label = pref.label;
                data.value = pref.value;
                if(typeof data.value === 'object') data.value = JSON.stringify(data.value);
                data.allow_override = pref.allow_override;
                this.$http.patch('/api/preference/' + pref.id, data).then(function(response) {
                    console.log(response.data);
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
