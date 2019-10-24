<template>
</template>

<script>
    import VueTypeahead from 'vue-typeahead';
    import debounce from 'debounce';

    export default {
        extends: VueTypeahead,
        props: {
            placeholder: {
                type: String,
                default: 'global.search'
            },
            onMultiselect: {
                type: Function,
                required: false
            },
            onClear: {
                type: Function,
                required: false
            },
            onSelect: {
                type: Function,
                required: false
            },
            value: {
                type: String,
                required: false
            }
        },
        data () {
            return {
                src: 'search/entity',
                minChars: 2,
                selectFirst: false
            }
        },
        mounted() {
            this.query = this.value;
        },
        computed: {
            debounce () {
                return debounce(this.update, 250)
            }
        },
        methods: {
            fetch() {
                if(!this.$http) {
                    return util.warn('You need to provide a HTTP client', this);
                }

                if(!this.src) {
                    return util.warn('You need to set the `src` property', this);
                }

                const src = this.queryParamName
                    ? this.src
                    : this.src + this.query;

                const params = this.queryParamName
                    ? Object.assign({ [this.queryParamName]: this.query }, this.data)
                    : this.data;

                // FIXME workaround to not run all outdated search requests
                // Clears whole queue, might clear unrelated queued requests
                this.$httpQueue.clear();
                let cancel = new Promise((resolve) => this.cancel = resolve);
                let request = this.$httpQueue.add(() => this.$http.get(src, { params }));

                return Promise.race([cancel, request]);
            },
            clearItem() {
                if(this.onClear) this.onClear();
                this.reset();
            },
            blur() {
                if(this.current !== -1) {
                    this.reset();
                }
            }
        }
    }
</script>
