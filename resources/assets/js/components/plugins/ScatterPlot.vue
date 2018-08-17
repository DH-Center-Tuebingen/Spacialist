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
        <div class="form-group row">
            <label for="name" class="col-form-label col-md-3">Name:</label>
            <div class="col-md-9">
                <input type="text" id="name" class="form-control" v-model="options.name" />
            </div>
        </div>
        <div class="form-group row">
            <label for="size" class="col-form-label col-md-3">Size:</label>
            <div class="d-flex flex-row col-md-9">
                <input class="custom-range" type="range" step="1" min="1" max="250" value="1" v-model="options.marker.size" />
                <span class="ml-3">{{ options.marker.size }}px</span>
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
                name: '',
                marker: {
                    size: 24
                },
                mode: 'markers',
                type: 'scatter'
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
