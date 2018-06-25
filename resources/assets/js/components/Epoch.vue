<template>
    <div>
        <div class="input-group">
            <div class="input-group-prepend" uib-dropdown>
                <button type="button" class="btn btn-outline-secondary dropdown-toggle" :disabled="disabled" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                    {{ startLabel }}
                </button>
                <ul class="dropdown-menu">
                    <a class="dropdown-item" href="#" v-for="label in labels" @click="setLabel('startLabel', label)">
                        {{ label }}
                    </a>
                </ul>
            </div>
            <input type="number" step="1" min="1" pattern="[0-9]+" class="form-control text-center" :disabled="disabled" aria-label="" @input="onInput('start', $event.target.value)" v-model="start">
            <div class="input-group-prepend input-group-append">
                <span class="input-group-text">-</span>
            </div>
            <input type="number" step="1" min="1" pattern="[0-9]+" class="form-control text-center" :disabled="disabled" aria-label="" @input="onInput('end', $event.target.value)" v-model="end">
            <div class="input-group-append">
                <button type="button" class="btn btn-outline-secondary dropdown-toggle" :disabled="disabled" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                    {{ endLabel }}
                </button>
                <ul uib-dropdown-menu class="dropdown-menu">
                    <a class="dropdown-item" href="#" v-for="label in labels" @click="setLabel('endLabel', label)">
                        {{ label }}
                    </a>
                </ul>
            </div>
        </div>
        <multiselect class="pt-2"
            label="concept_url"
            track-by="id"
            @input="onInput('epoch', epoch)"
            v-model="epoch"
            :allowEmpty="true"
            :closeOnSelect="true"
            :customLabel="translateLabel"
            :disabled="disabled"
            :hideSelected="true"
            :multiple="false"
            :options="epochs">
        </multiselect>
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
            epochs: {
                type: Array,
                default: _ => new Array(),
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
            setLabel(field, value) {
                this[field] = value;
                this.onInput(field, value);
            },
            translateLabel(element, prop) {
                const value = element[prop];
                if(!value) return element;
                return this.$translateConcept(value);
            },
        },
        data () {
            return {
                labels: ['BC', 'AD'],
                startLabel: this.value.startLabel,
                start: this.value.start,
                endLabel: this.value.endLabel,
                end: this.value.end,
                epoch: this.value.epoch,
            }
        }
    }
</script>
