<template>
    <div class="input-group">
        <input type="number" class="form-control text-center" :disabled="disabled" min="0" max="9999" step="0.01" @input="onInput('B', $event.target.value)" v-model="B"/>
            <span class="input-group-text">&times;</span>
        <input type="number" class="form-control text-center" :disabled="disabled" min="0" max="9999" step="0.01" @input="onInput('H', $event.target.value)" v-model="H"/>
            <span class="input-group-text">&times;</span>
        <input type="number" class="form-control text-center" :disabled="disabled" min="0" max="9999" step="0.01" @input="onInput('T', $event.target.value)" v-model="T"/>
        <div>
            <button class="btn btn-outline-secondary  dropdown-toggle" :disabled="disabled" type="button" data-bs-toggle="dropdown" aria-haspopup="true"     aria-expanded="false">{{unit}}</button>
            <div class="dropdown-menu">
                <a class="dropdown-item" href="#" v-for="(unit, i) in dimensionUnits" @click.prevent="setUnit(unit)" :key="i">
                    {{ unit }}
                </a>
            </div>
        </div>
    </div>
</template>

<script>
    export default {
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
