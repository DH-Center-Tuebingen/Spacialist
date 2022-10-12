<template>
    <div>
        <div class="input-group">
            <input type="text" class="form-control" :disabled="disabled" v-model="v.value" @input="onInput()" />
            <button type="button" class="btn btn-outline-secondary" :disabled="v.noContent" @click="loadRismInfo()">
                <i class="fas fa-fw fa-eye"></i>
            </button>
        </div>
        <div class="bg-light mt-2 p-2 border rounded" v-if="state.infoLoaded">
            <div class="d-flex flex-row justify-content-between">
                <span class="fw-bold">
                    {{ state.text }}
                </span>
                <button type="button" class="btn-close" aria-label="Close" @click="closeInfoBox()">
                </button>
            </div>
            <footer class="blockquote-footer mt-2">
                <span v-html="t('main.entity.attributes.rism.cite_info', {id: v.value})"></span>
            </footer>
        </div>
        <p class="alert alert-danger my-2" v-if="state.infoErrored">
            {{ t('main.entity.attributes.rism.doesnt_exist') }}
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
        getRismInfo,
    } from '@/api.js';

    import {
        getPreference,
    } from '@/helpers/helpers.js';

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
            const loadRismInfo = _ => {
                resetInfoRequest();
                state.requestState = 'requested';
                getRismInfo(v.value).then(data => {
                    if(!!data) {
                        const parser = new DOMParser();
                        const doc = parser.parseFromString(data, 'text/xml');
                        const root = doc.documentElement;
                        const nor = root.getElementsByTagName('zs:numberOfRecords');
                        if(!nor || parseInt(nor[0].textContent) == 0) {
                            state.info = null;
                            state.requestState = 'failed';
                        } else {
                            state.info = root;
                            state.requestState = 'success';
                        }
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
            } = useField(`rism_${name.value}`, yup.string(), {
                initialValue: value.value,
            });
            const state = reactive({
                info: null,
                requestState: 'not',
                infoLoaded: computed(_ => state.requestState == 'success' && !!state.info),
                infoErrored: computed(_ => state.requestState == 'failed'),
                keywords: 'none',
                text: computed(_ => {
                    if(state.infoLoaded) {
                        const record = state.info.getElementsByTagName('zs:records')[0].children[0];
                        const recordData = record.getElementsByTagName('zs:recordData')[0];
                        const marcRecord = recordData.getElementsByTagName('marc:record')[0];
                        const datafields = marcRecord.getElementsByTagName('marc:datafield');
                        for(let i=0; i<datafields.length; i++) {
                            const df = datafields.item(i);
                            if(df.getAttribute('tag') == 245) {
                                return df.children[0].textContent;
                            }
                        }
                        return t('main.entity.attributes.rism.doesnt_exist');
                    }
                }),
            });
            const v = reactive({
                value: fieldValue,
                handleChange,
                meta,
                resetField,
                noContent: computed(_ => !v.value),
            });


            watch(value, (newValue, oldValue) => {
                resetFieldState();
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
                loadRismInfo,
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
