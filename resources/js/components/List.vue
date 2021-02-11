<template>
    <div>
        <div class="input-group">
            <div class="input-group-prepend">
                <button type="button" class="btn btn-outline-secondary" :disabled="disabled" @click="toggleList()">
                    <div v-show="!state.expanded">
                        <i class="fas fa-fw fa-caret-up"></i>
                        <span v-if="state.entries.length">
                            ({{ state.entries.length }})
                        </span>
                    </div>
                    <div v-show="state.expanded">
                        <i class="fas fa-fw fa-caret-down"></i>
                    </div>
                </button>
            </div>
            <input type="text" class="form-control" :disabled="disabled" v-model="state.input" />
            <div class="input-group-append">
                <button type="button" class="btn btn-outline-success" @click="addListEntry()">
                    <i class="fas fa-fw fa-plus"></i>
                </button>
            </div>
        </div>
        <ol class="mt-2 mb-0" v-if="state.expanded && state.entries.length">
            <li v-for="(l, i) in state.entries" :key="i">
                <span v-html="createAnchorFromUrl(l)"></span>
                <a href="#" class="text-danger" @click.prevent="removeListEntry(i)">
                    <i class="fas fa-fw fa-trash"></i>
                </a>
            </li>
        </ol>
    </div>
</template>

<script>
    import {
        computed,
        reactive,
        toRefs,
    } from 'vue';

    import { useI18n } from 'vue-i18n';

    import {
        createAnchorFromUrl,
    } from '../helpers/helpers.js';

    export default {
        props: {
            name: String,
            entries: {
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
        setup(props, context) {
            const { t } = useI18n();
            const {
                name,
                entries,
                disabled,
                onChange,
            } = toRefs(props);
            // FETCH

            // FUNCTIONS
            const onInput = value => {
                // this.$emit('input', value);
                // this.onChange(value);
            };
            const addListEntry = _ => {
                if(!state.entries.value) {
                    state.entries.value = [];
                }
                state.entries.push(state.input);
                state.input = "";
                onInput(state.entries);
            };
            const removeListEntry = index => {
                state.entries.splice(index, 1);
                onInput(state.entries);
            };
            const toggleList = _ => {
                state.expanded = !state.expanded;
            };

            // DATA
            const state = reactive({
                input: "",
                expanded: false,
                entries: entries.value.slice(),
            });

            // RETURN
            return {
                t,
                // HELPERS
                createAnchorFromUrl,
                // LOCAL
                onInput,
                addListEntry,
                removeListEntry,
                toggleList,
                // PROPS
                name,
                disabled,
                onChange,
                // STATE
                state,
            }
        },
    }
</script>
