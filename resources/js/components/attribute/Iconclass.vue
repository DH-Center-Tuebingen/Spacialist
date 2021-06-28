<template>
    <div>
        <div class="input-group">
            <input type="text" class="form-control" :disabled="disabled" v-model="v.value" @input="onInput()" />
            <div class="input-group-append">
                <button type="button" class="btn btn-outline-secondary" @click="loadIconclassInfo()">
                    <i class="fas fa-fw fa-sync"></i>
                </button>
            </div>
        </div>
        <div class="bg-light mt-2 p-2 border rounded" v-if="state.infoLoaded">
            <div class="d-flex flex-row justify-content-between">
                <span class="fw-bold">
                    {{ state.text }}
                </span>
                <button type="button" class="btn-close" aria-label="Close" @click="closeInfoBox()">
                </button>
            </div>
            <hr class="my-2" />
            <div>
                <span>{{ state.keywords.join(' &bull; ') }}</span>
            </div>
            <footer class="blockquote-footer mt-2">
                <span v-html="t('main.entity.attributes.iconclass.cite_info', {class: v.value})"></span>
            </footer>
        </div>
        <p class="alert alert-danger my-2" v-if="state.infoErrored">
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
    } from '../../api.js';

    import {
        getPreference,
    } from '../../helpers/helpers.js';

    export default {
        props: {
            name: String,
            value: {
                type: String,
                required: false,
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
                disabled,
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
                }),
                text: computed(_ => {
                    if(state.infoLoaded) {
                        return state.info.txt[state.language] ? state.info.txt[state.language] : state.info.txt['en'];
                    }
                }),
            });
            const v = reactive({
                value: fieldValue,
                handleChange,
                meta,
                resetField,
            });

            watch(v.meta, (newValue, oldValue) => {
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
                // PROPS
                disabled,
                value,
                // STATE
                state,
                v,
            };
        },
    }
</script>
