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
                                <input type="text" :readonly="!preferences['prefs.gui-language'].allow_override" :class="[preferences['prefs.gui-language'].allow_override ? 'form-control' : 'form-control-plaintext']" v-model="preferences['prefs.gui-language'].value" />
                            </div>
                        </div>
                    </form>
                </td>
                <td>
                    <button type="button" class="btn btn-success">
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
                                <input id="left-column" type="number" :readonly="!preferences['prefs.columns'].allow_override" :class="[preferences['prefs.columns'].allow_override ? 'form-control' : 'form-control-plaintext']" v-model="preferences['prefs.columns'].value.left" />
                            </div>
                        </div>
                        <div class="form-group row">
                            <label for="center-column" class="col-md-2 col-form-label">Center Column:</label>
                            <div class="col-md-10">
                                <input id="center-column" type="number" :readonly="!preferences['prefs.columns'].allow_override" :class="[preferences['prefs.columns'].allow_override ? 'form-control' : 'form-control-plaintext']" v-model="preferences['prefs.columns'].value.center" />
                            </div>
                        </div>
                        <div class="form-group row">
                            <label for="right-column" class="col-md-2 col-form-label">Right-Hand Column:</label>
                            <div class="col-md-10">
                                <input id="right-column" type="number" :readonly="!preferences['prefs.columns'].allow_override" :class="[preferences['prefs.columns'].allow_override ? 'form-control' : 'form-control-plaintext']" v-model="preferences['prefs.columns'].value.right" />
                            </div>
                        </div>
                    </form>
                </td>
                <td>
                    <button type="button" class="btn btn-success">
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
                                    <input class="form-check-input" id="show-tooltips" type="checkbox" :disabled="!preferences['prefs.show-tooltips'].allow_override" v-model="preferences['prefs.show-tooltips'].value" />
                                </div>
                            </div>
                        </div>
                    </form>
                </td>
                <td>
                    <button type="button" class="btn btn-success">
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
                                <input type="text" :readonly="!preferences['prefs.tag-root'].allow_override" :class="[preferences['prefs.tag-root'].allow_override ? 'form-control' : 'form-control-plaintext']" v-model="preferences['prefs.tag-root'].value" />
                            </div>
                        </div>
                    </form>
                </td>
                <td>
                    <button type="button" class="btn btn-success">
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
                        <div class="form-group row" v-for="(extension, key) in preferences['prefs.load-extensions'].value">
                            <div class="col-md-2"></div>
                            <div class="col-md-10">
                                <div class="form-check">
                                    <input type="checkbox" class="form-check-input" :id="'extension-'+key" :checked="extension" :disabled="!preferences['prefs.load-extensions'].allow_override" />
                                    <label class="form-check-label" :for="'extension-'+key">
                                        {{ key }}
                                    </label>
                                </div>
                            </div>
                        </div>
                    </form>
                </td>
                <td>
                    <button type="button" class="btn btn-success">
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
                                <input type="text" :readonly="!preferences['prefs.link-to-thesaurex'].allow_override" :class="[preferences['prefs.link-to-thesaurex'].allow_override ? 'form-control' : 'form-control-plaintext']" v-model="preferences['prefs.link-to-thesaurex'].value" />
                            </div>
                        </div>
                    </form>
                </td>
                <td>
                    <button type="button" class="btn btn-success">
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
                                <input type="text" :readonly="!preferences['prefs.project-name'].allow_override" :class="[preferences['prefs.project-name'].allow_override ? 'form-control' : 'form-control-plaintext']" v-model="preferences['prefs.project-name'].value" />
                            </div>
                        </div>
                    </form>
                </td>
                <td>
                    <button type="button" class="btn btn-success">
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
        },
        data() {
            return {
            }
        },
    }
</script>
