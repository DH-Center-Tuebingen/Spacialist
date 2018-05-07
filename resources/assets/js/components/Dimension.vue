<template>
    <div class="input-group">
        <input type="number" class="form-control text-center" min="0" max="9999" step="0.01" @input="onInput('B', $event.target.value)"/>
        <div class="input-group-append input-group-prepend">
            <span class="input-group-text">&times;</span>
        </div>
        <input type="number" class="form-control text-center" min="0" max="9999" step="0.01" @input="onInput('H', $event.target.value)"/>
        <div class="input-group-append input-group-prepend">
            <span class="input-group-text">&times;</span>
        </div>
        <input type="number" class="form-control text-center" min="0" max="9999" step="0.01" @input="onInput('T', $event.target.value)"/>
        <div class="input-group-append">
            <button class="btn btn-outline-secondary dropdown-toggle" type="button"     data-toggle="dropdown" aria-haspopup="true"     aria-expanded="false"></button>
            <div class="dropdown-menu">
                <a class="dropdown-item" href="#" v-for="unit in dimensionUnits">
                    {{ unit }}
                </a>
            </div>
        </div>
    </div>
</template>

<script>
//TODO: dimension units are not saved, load initial values from database
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
                type: null,
                default: null
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
            }
        },
        data () {
            return {
                dimensionUnits: ['nm', 'µm', 'mm', 'cm', 'dm', 'm', 'km'],
            }
        }
    }
</script>
