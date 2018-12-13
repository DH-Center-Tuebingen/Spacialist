<template>
    <div>
        <div class="input-group">
            <div class="input-group-prepend" uib-dropdown>
                <button type="button" class="btn btn-outline-secondary dropdown-toggle" :disabled="disabled" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                    <span v-if="startLabel">
                        {{ $t(`main.entity.attributes.${startLabel}`) }}
                    </span>
                    <span v-else>
                    </span>
                </button>
                <ul class="dropdown-menu">
                    <a class="dropdown-item" href="#" v-for="label in labels" @click.prevent="setLabel('startLabel', label)">
                        {{ $t(`main.entity.attributes.${label}`) }}
                    </a>
                </ul>
            </div>
            <input type="number" step="1" min="0" pattern="[0-9]+" class="form-control text-center" :disabled="disabled" aria-label="" @input="onInput('start', $event.target.value)" v-model="start">
            <div class="input-group-prepend input-group-append">
                <span class="input-group-text">-</span>
            </div>
            <input type="number" step="1" min="0" pattern="[0-9]+" class="form-control text-center" :disabled="disabled" aria-label="" @input="onInput('end', $event.target.value)" v-model="end">
            <div class="input-group-append">
                <button type="button" class="btn btn-outline-secondary dropdown-toggle" :disabled="disabled" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                    <span v-if="endLabel">
                        {{ $t(`main.entity.attributes.${endLabel}`) }}
                    </span>
                    <span v-else>
                    </span>
                </button>
                <ul uib-dropdown-menu class="dropdown-menu">
                    <a class="dropdown-item" href="#" v-for="label in labels" @click.prevent="setLabel('endLabel', label)">
                        {{ $t(`main.entity.attributes.${label}`) }}
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
            :options="epochs"
            :placeholder="$t('global.select.placehoder')"
            :select-label="$t('global.select.select')"
            :deselect-label="$t('global.select.deselect')">
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
                return this.$translateLabel(element, prop);
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
