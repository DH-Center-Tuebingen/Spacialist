<template>
    <div class="row">
        <label class="col-md-2 form-label" />
        <div class="col-md-10">
            <multiselect
                id="language-search"
                :value="modelValue"
                :hide-selected="true"
                :mode="'single'"
                :filterResults="true"
                :options="state.languageList"
                :searchable="true"
                :disabled="readonly"
                :readonly="readonly"
                :placeholder="t('global.select.placeholder')"
                @change="onChange"
            />
            <!-- eslint-disable -->
            <button
                v-if="state.showSetButton"
                type="button"
                class="btn btn-outline-primary mt-2"
                :disabled="readonly"
                @click="setBrowserLanguage()"
                v-html="t('main.preference.info.set_to_language', {lang: state.browserLanguage})"
            />
            <!-- eslint-enable -->
        </div>
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
        getSupportedLanguages,
    } from '@/bootstrap/i18n.js';

    export default {
        props: {
            modelValue: {
                required: true,
                type: String,
            },
            readonly: {
                required: false,
                type: Boolean,
                default: false,
            },
            browserDefault: {
                required: false,
                type: Boolean,
                default: false,
            },
        },
        emits: ['changed', 'upadte:modelValue'],
        setup(props, context) {
            const { t } = useI18n();
            const {
                readonly,
                browserDefault,
            } = toRefs(props);

            // FUNCTIONS
            const onChange = value => {
                if(readonly.value) return;
                context.emit('changed',  value );
            };

            const setBrowserLanguage = _ => {
                if(!browserDefault.value || readonly.value) return;

                onChange(state.browserLanguage);
            };

            // DATA
            const state = reactive({
                languageList: getSupportedLanguages(),
                browserLanguage: computed(_ => navigator.language ? navigator.language.split('-')[0] : 'en'),
                showSetButton: computed(_ => {
                    return browserDefault.value && state.browserLanguage != props.modelValue && state.languageList.includes(state.browserLanguage);
                }),
            });

            // RETURN
            return {
                t,
                // LOCAL
                onChange,
                setBrowserLanguage,
                // STATE
                state,
            };
        }
    }
</script>