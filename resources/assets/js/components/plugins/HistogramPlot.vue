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
        <div class="d-flex flex-row justify-content-between form-group offset-md-3 pl-2 clickable" @click="toggleHorizontal">
            <span class="align-middle">Horizontal</span>
            <label class="cb-toggle mx-0 my-auto align-middle">
                <input type="checkbox" id="apply-changes-toggle" v-model="isHorizontal" />
                <span class="slider slider-rounded slider-primary"></span>
            </label>
        </div>
        <div class="form-group row">
            <label for="type" class="col-form-label col-md-3">Type:</label>
            <div class="col-md-9">
                <multiselect
                    id="type"
                    label="label"
                    track-by="key"
                    v-model="typedd"
                    :allowEmpty="true"
                    :closeOnSelect="true"
                    :hideSelected="false"
                    :multiple="false"
                    :options="types"
                    @input="onTypeSelected">
                </multiselect>
            </div>
        </div>
        <div class="d-flex flex-row justify-content-between form-group offset-md-3 pl-2 clickable" @click="toggleBins">
            <span class="align-middle">Use Auto Bins</span>
            <label class="cb-toggle mx-0 my-auto align-middle">
                <input type="checkbox" id="apply-changes-toggle" v-model="autoBins" />
                <span class="slider slider-rounded slider-primary"></span>
            </label>
        </div>
        <div v-if="!autoBins">
            <div class="form-group row">
                <label for="start" class="col-form-label col-md-3">Start:</label>
                <div class="col-md-9">
                    <input type="number" step="0.00001" id="start" class="form-control" v-model="start" @change="options.xbins.start = start" />
                </div>
            </div>
            <div class="form-group row">
                <label for="end" class="col-form-label col-md-3">End:</label>
                <div class="col-md-9">
                    <input type="number" step="0.00001" id="end" class="form-control" v-model="end" @change="options.xbins.end = end" />
                </div>
            </div>
            <div class="form-group row">
                <label for="size" class="col-form-label col-md-3">Size:</label>
                <div class="col-md-9">
                    <input type="number" min="0" step="0.00001" id="size" class="form-control" v-model="size" @change="options.xbins.size = size" />
                </div>
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
                type: 'histogram'
            };
            for(let k in defaults) {
                Vue.set(this.options, k, defaults[k]);
            }
        },
        methods: {
            toggleHorizontal() {
                this.isHorizontal = !this.isHorizontal;
                if(this.isHorizontal) {
                    Vue.set(this.options, 'y', this.options.x);
                    Vue.delete(this.options, 'x');
                } else {
                    Vue.set(this.options, 'x', this.options.y);
                    Vue.delete(this.options, 'y');
                }
            },
            toggleBins() {
                this.autoBins = !this.autoBins;
                if(this.autoBins) {
                    Vue.delete(this.options, 'xbins');
                } else {
                    Vue.set(this.options, 'xbins', {
                        start: this.start,
                        end: this.end,
                        size: this.size
                    });
                }
            },
            onTypeSelected(value, id) {
                if(!value) {
                    Vue.delete(this.options, 'cumulative');
                    Vue.delete(this.options, 'histnorm');
                } else if(value.key == 'cumulative') {
                    Vue.delete(this.options, 'histnorm');
                    Vue.set(this.options, 'cumulative', {
                        enabled: true
                    });
                } else if(value.key == 'normalized') {
                    Vue.delete(this.options, 'cumulative');
                    Vue.set(this.options, 'histnorm', 'probability');
                }
            },
            onInputChanged(value, id) {
                this.options[id] = this.onSelection(value);
            }
        },
        data() {
            return {
                xdd: {},
                typedd: {},
                types: [
                    {
                        key: 'cumulative',
                        label: 'Cumulative'
                    },
                    {
                        key: 'normalized',
                        label: 'Normalized'
                    }
                ],
                isHorizontal: false,
                autoBins: true,
                start: 0,
                end: 0,
                size: 0
            }
        }
    }
</script>
