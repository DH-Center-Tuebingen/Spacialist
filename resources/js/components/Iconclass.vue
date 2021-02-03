<template>
    <div>
        <div class="input-group">
            <input type="text" class="form-control" :disabled="disabled" v-model="localValue" @input="onInput(localValue)" />
            <div class="input-group-append">
                <button type="button" name="button" class="btn btn-outline-secondary" @click="loadIconclassInfo(localValue)">
                    <i class="fas fa-fw fa-sync"></i>
                </button>
            </div>
        </div>
        <div class="bg-light mt-2 p-2 border rounded" v-if="infoLoaded">
            <span class="fw-bold">{{ text }}</span>
            <hr class="my-2" />
            <div>
                <span>{{ keywords.join(' &bull; ') }}</span>
            </div>
            <footer class="blockquote-footer mt-2">Info from <cite title="Iconclass"><a :href="`http://iconclass.org/${localValue}.json`">{{ `http://iconclass.org/${localValue}.json` }}</a></cite></footer>
        </div>
        <p class="alert alert-danger my-2" v-if="infoErrored">
            {{ $t('main.entity.attributes.iconclass.doesnt_exist') }}
        </p>
    </div>
</template>

<script>
    export default {
        props: {
            name: String,
            value: {
                required: false,
                type: String,
            },
            disabled: {
                type: Boolean,
            }
        },
        mounted () {
            this.$el.value = this.value;
        },
        methods: {
            onInput(value) {
                this.resetInfoRequest();
                this.$emit('input', value);
            },
            loadIconclassInfo(iconClass) {
                this.resetInfoRequest();
                this.infoRequested = true;
                this.$externalHttp.get(`http://iconclass.org/${iconClass}.json`, {
                    crossdomain: true
                }).then(response => {
                    if(!response.data) {
                        this.requestErrored = true;
                    } else {
                        this.info = response.data;
                        this.requestSuccessful = true;
                    }
                });
            },
            resetInfoRequest() {
                this.info = null;
                this.infoRequested = false;
                this.requestSuccessful = false;
                this.requestErrored = false;
            }
        },
        data () {
            return {
                localValue: this.value,
                info: null,
                infoRequested: false,
                requestSuccessful: false,
                requestErrored: false,
                language: this.$getPreference('prefs.gui-language')
            }
        },
        computed: {
            infoLoaded() {
                return this.infoRequested && this.requestSuccessful;
            },
            infoErrored() {
                return this.infoRequested && this.requestErrored;
            },
            keywords() {
                if(this.infoLoaded && this.info) {
                    return this.info.kw[this.language] ? this.info.kw[this.language] : this.info.kw['en'];
                }
            },
            text() {
                if(this.infoLoaded && this.info) {
                    return this.info.txt[this.language] ? this.info.txt[this.language] : this.info.txt['en'];
                }
            },
        }
    }
</script>
