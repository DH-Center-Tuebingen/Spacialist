<template>
    <div>
        <div class="input-group">
            <button
                type="button"
                class="btn btn-outline-secondary d-flex align-items-center gap-1"
                :disabled="disabled"
                @click="toggleList()"
            >
                <div v-show="!state.expanded">
                    <i class="fas fa-fw fa-eye-slash" />
                </div>
                <div v-show="state.expanded">
                    <i class="fas fa-fw fa-eye" />
                </div>
                <span>({{ v.value.length }})</span>
            </button>
            <input
                v-model="state.input"
                type="text"
                class="form-control"
                :disabled="disabled"
                @keydown.enter="addListEntry()"
            >
            <button
                v-if="!disabled"
                type="button"
                class="btn btn-outline-success"
                :disabled="!state.valid"
                @click="addListEntry()"
            >
                <i class="fas fa-fw fa-plus" />
            </button>
        </div>
        <ul
            v-if="state.expanded && v.value.length"
            class="mt-2 mb-0 list-group"
        >
            <li
                v-for="(l, i) in v.value"
                :key="i"
                class="list-group-item d-flex"
            >
                <span class="me-4 text-secondary">{{ i }}</span>
                <!-- eslint-disable-next-line vue/no-v-html -->
                <span
                    class="flex-fill"
                    v-html="createAnchorFromUrl(l)"
                />
                <a
                    v-if="!disabled"
                    href="#"
                    class="text-danger"
                    @click.prevent="removeListEntry(i)"
                >
                    <i class="fas fa-fw fa-trash" />
                </a>
            </li>
        </ul>
    </div>
</template>

<script>
    import {
        computed,
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
            name: {
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
            // FETCH

            // FUNCTIONS
            const addListEntry = _ => {
                if(!state.valid) return;
                v.value.push(state.input);
                v.meta.dirty = true;
                state.input = '';
                v.meta.validated = true;
            };
            const removeListEntry = index => {
                v.value.splice(index, 1);
                v.meta.dirty = true;
                v.meta.validated = true;
            };
            const toggleList = _ => {
                state.expanded = !state.expanded;
            };
            const resetFieldState = _ => {
                // make sure to keep original array and only re-push values
                v.value.length = 0;
                v.value.push(...state.initialValue.slice());

                v.meta.dirty = false;
                v.meta.valid = true;
                v.meta.validated = false;
            };
            const undirtyField = _ => {
                state.initialValue = v.value.slice();

                v.meta.dirty = false;
                v.meta.valid = true;
                v.meta.validated = false;
            };

            // DATA
            const state = reactive({
                input: '',
                initialValue: props.entries.slice(),
                expanded: true,
                valid: computed(_ => !!state.input),
            });
            const v = reactive({
                meta: {
                    dirty: false,
                    valid: true,
                    validated: false,
                },
                value: props.entries.slice(),
            });

            watch(v.value, (newValue, oldValue) => {
                // only emit @change event if field is validated (required because Entity.vue components)
                // trigger this watcher several times even if another component is updated/validated
                if(!v.meta.validated) return;
                context.emit('change', {
                    dirty: v.meta.dirty,
                    valid: v.meta.valid,
                    value: v.value,
                });
            });
            watch(_ => props.entries, (newValue, oldValue) => {
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
            };
        },
    };
</script>
