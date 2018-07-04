<template>
    <form role="form">
        <div class="form-group row">
            <label for="a" class="col-form-label col-md-3">X Axis (A):</label>
            <div class="col-md-9">
                <multiselect
                    id="a"
                    label="label"
                    track-by="key"
                    v-model="add"
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
            <label for="b" class="col-form-label col-md-3">Y Axis (B):</label>
            <div class="col-md-9">
                <multiselect
                    id="b"
                    label="label"
                    track-by="key"
                    v-model="bdd"
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
            <label for="c" class="col-form-label col-md-3">Z Axis (C):</label>
            <div class="col-md-9">
                <multiselect
                    id="c"
                    label="label"
                    track-by="key"
                    v-model="cdd"
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
            <label for="title" class="col-form-label col-md-3">Title:</label>
            <div class="col-md-9">
                <input type="text" id="title" class="form-control" v-model="layout.annotations[0].text" />
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
            layout: {
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
            // Options
            const defaults = {
                a: [1, 2, 3, 4],
                b: [10, 15, 13, 17],
                c: [23, 32, 32, 1],
                text: ['1', '2', '3', '4'],
                marker: {
                    size: 6
                },
                mode: 'markers',
                type: 'scatterternary'
            };
            for(let k in defaults) {
                Vue.set(this.options, k, defaults[k]);
            }

            // Layout
            Vue.set(this.layout, 'ternary', {
                sum: 100,
                aaxis: {
                    title: 'A',
                    showline: true,
                    showgrid: true
                },
                baxis: {
                    title: 'B',
                    showline: true,
                    showgrid: true
                },
                caxis: {
                    title: 'C',
                    showline: true,
                    showgrid: true
                }
            });
            Vue.set(this.layout, 'annotations', [{
                showarrow: false,
                text: '',
                x: 0.5,
                y: 1.2,
                font: {
                    size: 16
                }
            }]);
        },
        methods: {
            onInputChanged(value, id) {
                this.options[id] = this.onSelection(value);
            }
        },
        data() {
            return {
                add: {},
                bdd: {},
                cdd: {}
            }
        }
    }
</script>
