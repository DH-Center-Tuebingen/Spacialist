<template>
    <div>
        <div class="input-group">
            <input
                v-model="v.value"
                type="text"
                class="form-control"
                :disabled="disabled"
                @input="onInput()"
            >
            <button
                type="button"
                class="btn btn-outline-secondary"
                :disabled="v.noContent"
                @click="loadIconclassInfo()"
            >
                <i class="fas fa-fw fa-eye" />
            </button>
        </div>
        <div
            v-if="state.infoLoaded"
            class="bg-light mt-2 p-2 border rounded"
        >
            <div class="d-flex flex-row justify-content-between">
                <span class="fw-bold">
                    {{ state.text }}
                </span>
                <button
                    type="button"
                    class="btn-close"
                    aria-label="Close"
                    @click="closeInfoBox()"
                />
            </div>
            <hr class="my-2">
            <div>
                <span>{{ state.keywords.join(' &bull; ') }}</span>
            </div>
            <footer class="blockquote-footer mt-2">
                <!-- eslint-disable-next-line vue/no-v-html -->
                <span v-html="t('main.entity.attributes.iconclass.cite_info', {class: v.value})" />
            </footer>
        </div>
        <p
            v-if="state.infoErrored"
            class="alert alert-danger my-2"
        >
            {{ t('main.entity.attributes.iconclass.doesnt_exist') }}
        </p>
    </div>
</template>

<script>
    import {
        computed,
        reactive,
        toRefs,
        watch,
    } from 'vue';

    import { useField } from 'vee-validate';

    import * as yup from 'yup';

    import { useI18n } from 'vue-i18n';

    import {
        getIconClassInfo,
    } from '@/api.js';

    import {
        getPreference,
    } from '@/helpers/helpers.js';

    export default {
        props: {
            name: {
                type:String , 
                required: true
            },
            value: {
                type: String,
                required: true,
            },
            disabled: {
                type: Boolean,
                required: false,
                default: false,
            }
        },
        emits: ['change'],
        setup(props, context) {
            const { t } = useI18n();

            const {
                name,
                value,
            } = toRefs(props);

            // FETCH

            // FUNCTIONS
            const resetInfoRequest = _ => {
                state.info = null;
                state.requestState = 'not';
            };
            const closeInfoBox = _ => {
                resetInfoRequest();
            };
            const resetFieldState = _ => {
                v.resetField({
                    value: value.value
                });
                resetInfoRequest();
            };
            const undirtyField = _ => {
                v.resetField({
                    value: v.value,
                });
            };
            const onInput = _ => {
                resetInfoRequest();
                v.handleChange(v.value);
            };
            const loadIconclassInfo = _ => {
                resetInfoRequest();
                state.requestState = 'requested';
                getIconClassInfo(v.value).then(data => {
                    if(!!data) {
                        state.info = data;
                        state.requestState = 'success';
                    } else {
                        state.info = null;
                        state.requestState = 'failed';
                    }
                }).catch(e => {
                    state.requestState = 'failed';
                });
            };

            // DATA
            const {
                handleChange,
                value: fieldValue,
                meta,
                resetField,
            } = useField(`iconclass_${name.value}`, yup.string(), {
                initialValue: value.value,
            });
            const state = reactive({
                info: null,
                requestState: 'not',
                language: getPreference('prefs.gui-language'),
                infoLoaded: computed(_ => state.requestState == 'success' && !!state.info),
                infoErrored: computed(_ => state.requestState == 'failed'),
                keywords: computed(_ => {
                    if(state.infoLoaded) {
                        return state.info.kw[state.language] ? state.info.kw[state.language] : state.info.kw['en'];
                    }
                    return [];
                }),
                text: computed(_ => {
                    if(state.infoLoaded) {
                        return state.info.txt[state.language] ? state.info.txt[state.language] : state.info.txt['en'];
                    }
                    return '';
                }),
            });
            const v = reactive({
                value: fieldValue,
                handleChange,
                meta,
                resetField,
                noContent: computed(_ => !v.value),
            });


            watch(_ => value, (newValue, oldValue) => {
                resetFieldState();
            });
            watch(_ => v.value, (newValue, oldValue) => {
                // only emit @change event if field is validated (required because Entity.vue components)
                // trigger this watcher several times even if another component is updated/validated
                if(!v.meta.validated) return;
                context.emit('change', {
                    dirty: v.meta.dirty,
                    valid: v.meta.valid,
                    value: v.value,
                });
            });

            // RETURN
            return {
                t,
                // HELPERS
                getPreference,
                // LOCAL
                resetFieldState,
                closeInfoBox,
                undirtyField,
                onInput,
                loadIconclassInfo,
                // STATE
                state,
                v,
            };
        },
    };
</script>
