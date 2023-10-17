<template>
    <div>
        <h5 class="mt-3 d-flex gap-1">
            {{ t('main.bibliography.modal.bibtex_code') }}
            <small
                class="clickable"
                @click="toggle()"
            >
                <span v-show="state.show">
                    <i class="fas fa-fw fa-caret-up" />
                </span>
                <span v-show="!state.show">
                    <i class="fas fa-fw fa-caret-down" />
                </span>
            </small>
            <small
                class="clickable text-primary"
                @click="codeToClipboard()"
            >
                <i class="fas fa-fw fa-copy" />
            </small>
        </h5>
        <span
            v-show="state.show"
            :id="state.id"
            v-html="bibtexify(code, type)"
        />
    </div>
</template>

<script>
    import {
        computed,
        reactive,
        toRefs,
    } from 'vue';

    import { useI18n } from 'vue-i18n';

    import { useToast } from '@/plugins/toast.js';

    import {
        getTs,
        copyToClipboard,
    } from '@/helpers/helpers.js';
    import {
        bibtexify,
    } from '@/helpers/filters.js';

    export default {
        props: {
            code: {
                required: true,
                type: Object,
            },
            type: {
                required: true,
                type: String,
            },
            show: {
                required: false,
                type: Boolean,
                default: false,
            }
        },
        setup(props, context) {
            const {
                code,
                type,
                show,
            } = toRefs(props);
            const { t } = useI18n();
            const toast = useToast();

            // FUNCTIONS
            const toggle = _ => {
                state.show = !state.show;
            };
            const codeToClipboard = _ => {
                copyToClipboard(state.id);
                const label = t('main.bibliography.toast.bibtex_code_to_clipboard.title');
                toast.$toast(label, '', {
                    channel: 'success',
                    simple: true,
                });
            };

            // DATA
            const state = reactive({
                id: `bibliography-item-bibtex-code-${getTs()}`,
                show: show.value,
            });

            // RETURN
            return {
                t,
                // HELPERS
                codeToClipboard,
                bibtexify,
                // LOCAL
                toggle,
                //STATE
                state,
            }
        },
    }
</script>
