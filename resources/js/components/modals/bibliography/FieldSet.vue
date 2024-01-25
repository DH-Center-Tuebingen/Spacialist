<template>
    <div>
        <div
            v-for="(fieldData, field) in v.fields"
            :key="field"
            class="row mb-3"
        >
            <label class="col-form-label col-md-3 text-end">
                {{ t(`main.bibliography.column.${field}`) }}
                <span
                    v-if="isMandatoryField(field)"
                    class="text-danger"
                >
                    *
                </span>
                :
            </label>
            <div class="col-md-9">
                <input
                    v-model="fieldData.value"
                    type="text"
                    class="form-control d-inline"
                    :name="`${field}`"
                    :class="getClassByValidation(fieldData.errors)"
                    @input="fieldData.handleChange"
                >
                <a
                    v-show="fieldData.meta.dirty"
                    href="#"
                    class="text-muted ms--4-5"
                    tabindex="-1"
                    @click.prevent="resetField(field)"
                >
                    <span>
                        <i class="fas fa-fw fa-undo" />
                    </span>
                </a>

                <div class="invalid-feedback">
                    <span
                        v-for="(msg, i) in fieldData.errors"
                        :key="i"
                    >
                        {{ msg }}
                    </span>
                </div>
            </div>
        </div>
    </div>
</template>

<script>
    import {
        reactive,
        toRefs,
        watch,
    } from 'vue';

    import {
        useField,
        useForm,
    } from 'vee-validate';

    import * as yup from 'yup';

    import { useI18n } from 'vue-i18n';

    import {
        bibtex as bibtexValidation,
        bibtexExt,
    } from '@/bootstrap/validation.js';

    import {
        getClassByValidation,
    } from '@/helpers/helpers.js';
    import {
        bibliographyTypes,
    } from '@/helpers/bibliography.js';

    export default {
        props: {
            data: {
                required: true,
                type: Object
            },
            type: {
                required: true,
                type: String,
            },
        },
        emits: ['change'],
        setup(props, context) {
            const {
                data,
                type,
            } = toRefs(props);
            const { t } = useI18n();

            // FUNCTIONS
            const resetField = field => {
                v.fields[field].resetField({
                    value: data.value.fields[field] ? data.value.fields[field] : ''
                });

                context.emit('change', {
                    dirty: state.formMeta.dirty,
                    valid: state.formMeta.valid,
                    values: {
                        [field]: v.fields[field].value,
                    },
                });
            };
            const undirtyField = field => {
                v.fields[field].resetField({
                    value: v.fields[field].value,
                });
            };
            const undirtyFields = _ => {
                Object.keys(v.fields).forEach(f => {
                    undirtyField(f);
                });
            };
            const getData = onlyOnValid => {
                const values = {};

                if(!state.formMeta.dirty) return values;
                if(onlyOnValid && !state.formMeta.valid) return values;

                for(let k in v.fields) {
                    const field = v.fields[k];
                    if(field.meta.dirty && field.meta.valid) {
                        values[k] = field.value;
                    }
                }
                return values;
            };
            const getValidationRules = field => {
                let rules = yup.string();
                if(state.type.mandatory) {
                    if(state.type.mandatory[field] === true) {
                        rules = rules.required();
                    } else if(state.type.mandatory[field]) {
                        const refField = state.type.mandatory[field];
                        rules = rules.when(refField, {
                            is: '',
                            then: _ => yup.string().required(o => bibtexExt.requiredIf(o, refField)),
                            otherwise: _ => yup.string(),
                        });
                    }
                }
                return rules;
            };
            const initValidation = _ => {
                v.fields = {};
                const ruleSets = {};
                const initValues = {};
                state.type.fields.forEach(f => {
                    const key = f;
                    const value = data.value.fields[f] ? data.value.fields[f] : '';
                    ruleSets[key] = getValidationRules(f);
                    initValues[key] = value;
                });
                const wh = state.type.mandatory ? Object.keys(state.type.mandatory).filter(m => state.type.mandatory[m] && state.type.mandatory[m] !== true) : [];
                const schema = yup.object().shape(ruleSets, wh);
                const {
                    meta: formMeta,
                } = useForm({
                    validationSchema: schema,
                    initialValues: initValues,
                });
                state.formMeta = formMeta;
                state.type.fields.forEach(f => {
                    const key = f;
                    const {
                        errors,
                        handleChange,
                        value,
                        meta: fMeta,
                        resetField,
                    } = useField(key);
                    v.fields[key] = {
                        errors: errors,
                        handleChange: handleChange,
                        value, value,
                        meta: fMeta,
                        resetField: resetField,
                    };
                });
            };
            const isMandatoryField = field => {
                return state.type.mandatory && state.type.mandatory[field] === true;
            };

            // DATA
            const state = reactive({
                type: bibliographyTypes.find(bt => bt.name == type.value),
                formMeta: {},
            });
            const v = reactive({
                fields: {},
            });

            yup.setLocale(bibtexValidation);

            initValidation();

            watch(_ => state.formMeta, (newValue, oldValue) => {
                const isDirty = newValue.dirty;
                const isValid = newValue.valid;

                context.emit('change', {
                    dirty: isDirty,
                    valid: isValid,
                    values: getData(),
                });
            });

            // RETURN
            return {
                t,
                // HELPERS
                getClassByValidation,
                // PROPS
                // LOCAL
                resetField,
                undirtyFields,
                getData,
                isMandatoryField,
                //STATE
                state,
                v,
            }
        },
    }
</script>
