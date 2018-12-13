<template>
    <form role="form">
        <div class="form-group row">
            <label for="x" class="col-form-label col-md-3">X Axis:</label>
            <div class="col-md-9">
                <multiselect
                    id="x"
                    label="label"
                    track-by="key"
                    v-model="xdd"
                    :allowEmpty="true"
                    :closeOnSelect="true"
                    :hideSelected="false"
                    :multiple="false"
                    :options="selections"
                    @input="onInputChanged">
                </multiselect>
            </div>
        </div>
        <div class="form-group row">
            <label for="y" class="col-form-label col-md-3">Y Axis:</label>
            <div class="col-md-9">
                <multiselect
                    id="y"
                    label="label"
                    track-by="key"
                    v-model="ydd"
                    :allowEmpty="true"
                    :closeOnSelect="true"
                    :hideSelected="false"
                    :multiple="false"
                    :options="selections"
                    @input="onInputChanged">
                </multiselect>
            </div>
        </div>
    </form>
</template>

<script>
    export default {
        props: {
            options: {
                required: true,
                type: Object
            },
            selections: {
                required: true,
                type: Array
            },
            onSelection: {
                required: true,
                type: Function
            }
        },
        beforeMount() {
            const defaults = {
                x: [],
                y: [],
                type: 'bar'
            };
            for(let k in defaults) {
                Vue.set(this.options, k, defaults[k]);
            }
        },
        methods: {
            onInputChanged(value, id) {
                this.options[id] = this.onSelection(value);
            }
        },
        data() {
            return {
                xdd: {},
                ydd: {}
            }
        }
    }
</script>
