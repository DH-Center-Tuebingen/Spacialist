<template>
    <form
        v-dcan="'bibliography_read|entity_data_write'"
        role="form"
        @submit.prevent="onAddReference()"
    >
        <div class="mb-2">
            <AutoTextarea
                v-model="state.description"
                class="form-control"
                :placeholder="t('main.entity.references.bibliography.comment')"
            />
        </div>
        <div class="d-flex flex-row gap-2">
            <div class="flex-grow-1">
                <multiselect
                    id="bibliography-search"
                    v-model="state.entry"
                    :object="true"
                    :label="'title'"
                    :track-by="'id'"
                    :hide-selected="true"
                    :value-prop="'id'"
                    :mode="'single'"
                    :delay="0"
                    :min-chars="0"
                    :resolve-on-load="true"
                    :filterResults="false"
                    :options="async query => await filterBibliographyList(query)"
                    :searchable="true"
                    open-direction="top"
                    :placeholder="t('global.select.placeholder')"
                >
                    <template #singlelabel="{ value }">
                        <div class="multiselect-single-label">
                            <div>
                                <span class="fw-medium">{{ formatBibtexText(value.title) }}</span>
                                -
                                <cite class="small">
                                    {{ formatAuthors(value.author) }} ({{ value.year }})
                                </cite>
                            </div>
                        </div>
                    </template>
                    <template #option="{ option }">
                        <div>
                            <div>
                                <span class="fw-medium">{{ formatBibtexText(option.title) }}</span>
                            </div>
                            <cite class="small">
                                {{ formatAuthors(option.author) }}
                                <span class="fw-light">({{ option.year }})</span>
                            </cite>
                        </div>
                    </template>
                </multiselect>
            </div>

            <button
                type="submit"
                class="btn btn-outline-success"
                :disabled="state.addReferenceDisabled || state.pending"
                :title="t('main.entity.references.bibliography.add_button')"
            >
                <i class="fas fa-fw fa-plus" />
                {{ t("global.add") }}
            </button>
        </div>
    </form>
</template>

<script>
    import {
        computed,
        reactive,
    } from 'vue';
    import { useI18n } from 'vue-i18n';
    import useBibliographyStore from '@/bootstrap/stores/bibliography.js';
    import {
        can,
    } from '@/helpers/helpers.js';
    import {
        formatAuthors,
        formatBibtexText
    } from '@/helpers/bibliography.js';
    import AutoTextarea from '@/components/forms/AutoTextarea.vue';

    export default {
        components: {
            AutoTextarea,
        },
        props: {
            value: {
                type: Object,
                required: true
            },
            maxHeight: {
                type: String,
                default: '',
            }
        },
        emits: ['add'],
        setup(props, context) {
            const { t } = useI18n();
            const bibliographyStore = useBibliographyStore();
            const isMatch = (prop, exp) => {
                return !!prop && !!prop.match(exp);
            };
            const filterBibliographyList = async query => {
                if(!query) {
                    return await new Promise(r => r(state.bibliography));
                } else {
                    const exp = new RegExp(query, 'i');
                    return await new Promise(r => r(
                        state.bibliography.filter(entry => {
                            return (
                                isMatch(entry.title, exp) ||
                                isMatch(entry.booktitle, exp) ||
                                isMatch(entry.author, exp) ||
                                isMatch(entry.year, exp) ||
                                isMatch(entry.citekey, exp) ||
                                isMatch(entry.journal, exp)
                            );
                        })
                    ));
                }
            };
            const reset = _ => {
                state.entry = null;
                state.pending = false;
                state.description = '';
            };

            const successCallback = (success = false) => {
                if(success)
                    reset();
            };

            const onAddReference = _ => {
                if(!can('bibliography_read|entity_data_write')) return;
                state.pending = true;
                const data = {
                    bibliography_id: state.entry.id,
                    description: state.description,
                };
                context.emit('add', data, successCallback);
            };
            const state = reactive({
                entry: null,
                pending: false,
                description: '',
                bibliography: computed(_ => bibliographyStore.bibliography),
                addReferenceDisabled: computed(_ => !state.entry?.id || !state.description),
            });

            return {
                t,
                formatAuthors,
                filterBibliographyList,
                onAddReference,
                state,
                reset,
                formatBibtexText,
            };
        },
    };
</script>