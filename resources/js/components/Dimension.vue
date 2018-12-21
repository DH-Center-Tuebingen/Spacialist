<template>
    <div class="input-group">
        <input type="number" class="form-control text-center" :disabled="disabled" min="0" max="9999" step="0.01" @input="onInput('B', $event.target.value)" v-model="B"/>
        <div class="input-group-append input-group-prepend">
            <span class="input-group-text">&times;</span>
        </div>
        <input type="number" class="form-control text-center" :disabled="disabled" min="0" max="9999" step="0.01" @input="onInput('H', $event.target.value)" v-model="H"/>
        <div class="input-group-append input-group-prepend">
            <span class="input-group-text">&times;</span>
        </div>
        <input type="number" class="form-control text-center" :disabled="disabled" min="0" max="9999" step="0.01" @input="onInput('T', $event.target.value)" v-model="T"/>
        <div class="input-group-append">
            <button class="btn btn-outline-secondary dropdown-toggle" :disabled="disabled" type="button" data-toggle="dropdown" aria-haspopup="true"     aria-expanded="false">{{unit}}</button>
            <div class="dropdown-menu">
                <a class="dropdown-item" href="#" v-for="unit in dimensionUnits" @click="setUnit(unit)">
                    {{ unit }}
                </a>
            </div>
        </div>
    </div>
</template>

<script>
    export default {
        $_veeValidate: {
            // value getter
            value () {
                return this.$el.value;
            },
            // name getter
            name () {
                return this.name;
            }
        },
        props: {
            name: String,
            value: {
                type: Object,
                default: _ => new Object(),
            },
            disabled: {
                type: Boolean,
            },
            onChange: {
                type: Function,
                required: true,
            }
        },
        mounted () {
            this.$el.value = this.value;
        },
        methods: {
            onInput(field, value) {
                this.$emit('input', value);
                this.onChange(field, value);
            },
            setUnit(unit) {
                this.onInput('unit', unit);
                this.unit = unit;
            }
        },
        data () {
            return {
                dimensionUnits: ['nm', 'µm', 'mm', 'cm', 'dm', 'm', 'km'],
                B: this.value.B,
                H: this.value.H,
                T: this.value.T,
                unit: this.value.unit
            }
        }
    }
</script>
