<template>
    <div>
        <div class="input-group">
            <button
                type="button"
                class="btn btn-outline-secondary"
                :disabled="disabled"
                @click="toggleList()"
            >
                <div v-show="!state.expanded">
                    <i class="fas fa-fw fa-caret-up" />
                    <span v-if="v.value.length">
                        ({{ v.value.length }})
                    </span>
                </div>
                <div v-show="state.expanded">
                    <i class="fas fa-fw fa-caret-down" />
                </div>
            </button>
            <input
                v-model="state.input"
                type="text"
                class="form-control"
                :disabled="disabled"
            >
            <button
                v-if="!disabled"
                type="button"
                class="btn btn-outline-success"
                @click="addListEntry()"
            >
                <i class="fas fa-fw fa-plus" />
            </button>
        </div>
        <ol
            v-if="state.expanded && v.value.length"
            class="mt-2 mb-0"
        >
            <li
                v-for="(l, i) in v.value"
                :key="i"
            >
                <span v-html="createAnchorFromUrl(l)" />
                <a
                    v-if="!disabled"
                    href="#"
                    class="text-danger"
                    @click.prevent="removeListEntry(i)"
                >
                    <i class="fas fa-fw fa-trash" />
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
    } from '@/helpers/helpers.js';

    export default {
        props: {
            name:{ 
                type: String, 
                required: false, 
                default: null
            },
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
                entries,
            } = toRefs(props);
            // FETCH

            // FUNCTIONS
            const addListEntry = _ => {
                v.value.push(state.input);
                v.meta.dirty = true;
                state.input = '';
            };
            const removeListEntry = index => {
                v.value.splice(index, 1);
                v.meta.dirty = true;
            };
            const toggleList = _ => {
                state.expanded = !state.expanded;
            };
            const resetFieldState = _ => {
                v.value = state.initialValue.slice();
                v.meta.dirty = false;
                v.meta.valid = true;
            };
            const undirtyField = _ => {
                state.initialValue = entries.value.slice();
                v.meta.dirty = false;
                v.meta.valid = true;
            };

            // DATA
            const state = reactive({
                input: '',
                initialValue: entries.value.slice(),
                expanded: false,
            });
            const v = reactive({
                meta:{
                    dirty: false,
                    valid: true,
                },
                value: entries.value.slice(),
            });

            watch(v.meta, (newValue, oldValue) => {
                context.emit('change', {
                    dirty: v.meta.dirty,
                    valid: v.meta.valid,
                    value: v.value,
                });
            });
            watch(entries, (newValue, oldValue) => {
                state.initialValue = newValue.slice();
                resetFieldState();
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
                // STATE
                state,
                v,
            }
        },
    }
</script>
