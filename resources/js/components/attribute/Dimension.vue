<template>
    <div class="input-group">
        <input type="number" class="form-control text-center" :disabled="disabled" min="0" max="9999" step="0.01" @input="onInput('B', $event.target.value)" v-model="state.B"/>
            <span class="input-group-text">&times;</span>
        <input type="number" class="form-control text-center" :disabled="disabled" min="0" max="9999" step="0.01" @input="onInput('H', $event.target.value)" v-model="state.H"/>
            <span class="input-group-text">&times;</span>
        <input type="number" class="form-control text-center" :disabled="disabled" min="0" max="9999" step="0.01" @input="onInput('T', $event.target.value)" v-model="state.T"/>
        <div>
            <button class="btn btn-outline-secondary  dropdown-toggle" :disabled="disabled" type="button" data-bs-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                {{ state.unit }}
            </button>
            <div class="dropdown-menu">
                <a class="dropdown-item" href="#" v-for="(unit, i) in dimensionUnits" @click.prevent="setUnit(unit)" :key="i">
                    {{ unit }}
                </a>
            </div>
        </div>
    </div>
</template>

<script>
    import {
        reactive,
        toRefs,
    } from 'vue';

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
        emits: ['input'],
        setup(props, context) {
            const {
                value,
                disabled,
            } = toRefs(props);

            // FUNCTIONS
            const onInput = (field, value) => {
                context.emit('input', value);
                // props.onChange(field, value);
            };
            const setUnit = (unit) => {
                onInput('unit', unit);
                state.unit = unit;
            };

            // DATA
            const dimensionUnits = ['nm', 'µm', 'mm', 'cm', 'dm', 'm', 'km'];
            const state = reactive({
                B: value.value.B,
                H: value.value.H,
                T: value.value.T,
                unit: value.value.unit,
            });

            // RETURN
            return {
                // HELPERS
                // LOCAL
                disabled,
                dimensionUnits,
                onInput,
                setUnit,
                // STATE
                state,
            }
        },
    }
</script>
