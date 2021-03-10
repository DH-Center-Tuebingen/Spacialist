<template>
    <div>
        <div class="input-group">
            <input type="text" class="form-control" :disabled="disabled" v-model="state.value" @input="onInput()" />
            <div class="input-group-append">
                <button type="button" name="button" class="btn btn-outline-secondary" @click="loadIconclassInfo(state.value)">
                    <i class="fas fa-fw fa-sync"></i>
                </button>
            </div>
        </div>
        <div class="bg-light mt-2 p-2 border rounded" v-if="state.infoLoaded">
            <span class="fw-bold">{{ state.text }}</span>
            <hr class="my-2" />
            <div>
                <span>{{ state.keywords.join(' &bull; ') }}</span>
            </div>
            <footer class="blockquote-footer mt-2">
                <span v-html="t('main.entity.attributes.iconclass.cite_info', {class: state.value})"></span>
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
        onMounted,
        reactive,
        toRefs,
    } from 'vue';

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
                required: false,
                type: String,
            },
            disabled: {
                type: Boolean,
            }
        },
        emits: ['input'],
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
            const onInput = _ => {
                resetInfoRequest();
                context.emit('input', state.value);
            };
            const loadIconclassInfo = iconClass => {
                resetInfoRequest();
                state.requestState = 'requested';
                getIconClassInfo(iconClass).then(data => {
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
            const state = reactive({
                value: '',
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

            // RETURN
            return {
                t,
                // HELPERS
                getPreference,
                // LOCAL
                onInput,
                loadIconclassInfo,
                // PROPS
                disabled,
                value,
                // STATE
                state,
            };
        },
    }
</script>
