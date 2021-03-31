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
        reactive,
        toRefs,
        watch,
    } from 'vue';

    import { useI18n } from 'vue-i18n';

    import {
        createAnchorFromUrl,
    } from '../../helpers/helpers.js';

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
        },
        emits: ['change'],
        setup(props, context) {
            const { t } = useI18n();
            const {
                name,
                entries,
                disabled,
            } = toRefs(props);
            // FETCH

            // FUNCTIONS
            const addListEntry = _ => {
                state.entries.push(state.input);
                state.input = '';
                state.meta.dirty = true;
            };
            const removeListEntry = index => {
                state.entries.splice(index, 1);
                state.meta.dirty = true;
            };
            const toggleList = _ => {
                state.expanded = !state.expanded;
            };
            const resetFieldState = _ => {
                state.entries = state.initialValue.slice();
                state.meta.dirty = false;
                state.meta.valid = true;
            };
            const undirtyField = _ => {
                state.meta.dirty = false;
                state.meta.valid = true;
            };

            // DATA
            const state = reactive({
                input: '',
                entries: entries.value.slice(),
                initialValue: entries.value.slice(),
                expanded: false,
                meta: {
                    dirty: false,
                    valid: true,
                }
            });

            watch(state.meta, (newValue, oldValue) => {
                console.log("meta updated", state.meta.dirty, state.meta.valid);
                context.emit('change', {
                    dirty: state.meta.dirty,
                    valid: state.meta.valid,
                });
            });

            // RETURN
            return {
                t,
                // HELPERS
                createAnchorFromUrl,
                // LOCAL
                addListEntry,
                removeListEntry,
                toggleList,
                resetFieldState,
                undirtyField,
                // PROPS
                name,
                disabled,
                // STATE
                state,
            }
        },
    }
</script>
