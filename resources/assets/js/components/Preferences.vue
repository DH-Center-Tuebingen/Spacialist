<template>
    <table class="table table-striped table-hover">
        <thead class="thead-light">
            <tr>
                <th>Preference {{preferences}}</th>
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
                    <input type="text" v-model="preferences['prefs.gui-language'].value" />
                </td>
                <td>
                    <input type="checkbox"  v-model="preferences['prefs.gui-language'].allow_override" />
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
                    <span></span>Left-Hand Column: <input type="number" v-model="preferences['prefs.columns'].value.left" />
                    <br />
                    <span></span>Center Column: <input type="number" v-model="preferences['prefs.columns'].value.center" />
                    <br />
                    <span></span>Right-Hand Column: <input type="number" v-model="preferences['prefs.columns'].value.right" />
                </td>
                <td>
                    <input type="checkbox" v-model="preferences['prefs.columns'].allow_override" />
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
                    <input type="checkbox" v-model="preferences['prefs.show-tooltips'].value" />
                </td>
                <td>
                    <input type="checkbox" v-model="preferences['prefs.show-tooltips'].allow_override" />
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
                    <input type="text" v-model="preferences['prefs.tag-root'].value" />
                </td>
                <td>
                    <input type="checkbox" v-model="preferences['prefs.tag-root'].allow_override" />
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
                    <ul>
                        <li v-for="(extension, key) in preferences['prefs.load-extensions'].value">
                            {{key}} <input type="checkbox" :checked="extension" />
                        </li>
                    </ul>
                </td>
                <td>
                    <input type="checkbox" v-model="preferences['prefs.load-extensions'].allow_override" />
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
                    <input type="text" v-model="preferences['prefs.link-to-thesaurex'].value" />
                </td>
                <td>
                    <input type="checkbox" v-model="preferences['prefs.link-to-thesaurex'].allow_override" />
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
                    <input type="text" v-model="preferences['prefs.project-name'].value" />
                </td>
                <td>
                    <input type="checkbox" v-model="preferences['prefs.project-name'].allow_override" />
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
