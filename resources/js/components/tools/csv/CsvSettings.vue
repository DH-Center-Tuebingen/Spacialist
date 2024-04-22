<template>
    <form
        class="csv-settings form-control d-inline-flex flex-wrap align-items-center gap-2"
        style="order: 1;"
    >
        <div class="line line-top gap-2">
            <div class="input-group  flex-nowrap">
                <span class="input-group-text">
                    {{ t('global.paginator.count') }}
                </span>
                <!-- <label
                for="row-count"
                class="visually-hidden"
            >
                {{ t('main.csv.uploader.nr_of_shown_rows') }}
            </label> -->
                <input
                    id="row-count"
                    :value="showCount"
                    type="number"
                    class="form-control"
                    min="0"
                    step="1"
                    :style="paginatorInputStyle"
                    :placeholder="t('main.csv.uploader.nr_of_shown_rows')"
                    @input="(event) => $emit('update:showCount', parseInt(event.target.value))"
                >

                <!-- <label
                for="skipped-row-count"
                class="visually-hidden"
            >
                {{ t('main.csv.uploader.nr_of_skipped_rows') }}
            </label> -->
                <span class="input-group-text">
                    {{ t('global.paginator.skip') }}
                </span>
                <input
                    id="skipped-row-count"
                    :value="skippedCount"
                    type="number"
                    class="form-control"
                    :style="paginatorInputStyle"
                    min="0"
                    :max="total"
                    step="1"
                    :placeholder="t('main.csv.uploader.nr_of_skipped_rows')"
                    @input="(event) => $emit('update:skippedCount', parseInt(event.target.value))"
                >
                <span class="input-group-text">
                    {{ rangeInformationText }}
                </span>
            </div>
        </div>
        <div
            class="line line-bottom gap-2 flex-wrap"
            style="order: 2; flex-basis: fit-content;"
        >
            <div
                class="delimiter-group input-group"
                style="position: relative; flex-wrap: nowrap; flex-basis: fit-content; flex-grow: 1; max-width: 250px;"
            >
                <label
                    class="input-group-text"
                    for="delimiter"
                    style="height: 42px;"
                >
                    {{ t('main.csv.uploader.delimiter') }}
                </label>
                <input
                    v-if="useCustomDelimiter"
                    id="delimiter"
                    :value="delimiter"
                    type="text"
                    class="form-control"
                    :placeholder="t('main.csv.uploader.delimiter_with_info')"
                    :style="delimiterInputStyle"
                    @input="$emit('update:delimiter', $event.target.value)"
                >
                <Multiselect
                    v-else
                    :value="delimiter"
                    :options="delimiterOptions"
                    :classes="multiselectResetClasslist"
                    :can-deselect="false"
                    :can-clear="false"
                    :style="delimiterInputStyle"
                    @change="value => $emit('update:delimiter', value)"
                />
                <button
                    class="btn btn-outline-secondary"
                    type="button"
                    style="border-left-width: 2px;"
                    @click="changeCustomDelimiter"
                >
                    <span v-if="useCustomDelimiter">
                        <i class="far fa-fw fa-rectangle-list" />
                    </span>
                    <span v-else>
                        <i class="far fa-fw fa-keyboard" />
                    </span>
                </button>
            </div>

            <div class="form-check form-switch">
                <input
                    id="has-header"
                    :checked="hasHeaderRow"
                    class="form-check-input"
                    type="checkbox"
                    @input="(event) => $emit('update:hasHeaderRow', event.target.checked)"
                >
                <label
                    class="form-check-label"
                    for="has-header"
                >
                    {{ t('main.csv.uploader.has_header') }}
                </label>
            </div>
            <div class="form-check form-switch">
                <input
                    id="show-linenumbers"
                    :checked="showLinenumbers"
                    class="form-check-input"
                    type="checkbox"
                    @input="(event) => $emit('update:showLinenumbers', event.target.checked)"
                >
                <label
                    class="form-check-label"
                    for="show-linenumbers"
                >
                    {{ t('main.csv.uploader.show_linenumbers') }}
                </label>
            </div>
            <IconButton
                icon="eye"
                :model-value="showPreview"
                alt-icon="eye-slash"
                @click="$emit('update:showPreview', !showPreview)"
            />
        </div>
    </form>
</template>

<script>
    import Multiselect from '@vueform/multiselect';
    import { useI18n } from 'vue-i18n';

    import { multiselectResetClasslist } from '@/helpers/helpers.js';
    import IconButton from '../../forms/IconButton.vue';
    import { computed } from 'vue';


    export default {
        components: {
            Multiselect,
            IconButton
        },
        props: {
            delimiter: {
                type: String,
                required: false,
                default: ',',
            },
            useCustomDelimiter: {
                type: Boolean,
                required: false,
                default: false,
            },
            hasHeaderRow: {
                type: Boolean,
                required: false,
                default: true,
            },
            showLinenumbers: {
                type: Boolean,
                required: false,
                default: false,
            },
            showCount: {
                type: Number,
                required: false,
                default: 10,
            },
            skippedCount: {
                type: Number,
                required: false,
                default: 0,
            },
            maxRows: {
                type: Number,
                required: false,
                default: 100,
            },
            showPreview: {
                type: Boolean,
                required: false,
                default: true,
            },
            total: {
                type: Number,
                required: true
            }
        },
        emits: [
            'update:delimiter',
            'update:hasHeaderRow',
            'update:showLinenumbers',
            'update:useCustomDelimiter',
            'update:showCount',
            'update:skippedCount',
            'update:showPreview',
        ],
        setup(props, context) {
            const { t } = useI18n();


            const delimiterOptions = [
                { value: ',', label: ',' },
                { value: ';', label: ';' },
                { value: '\t', label: t('keyboard.tab') },
                { value: ' ', label: t('keyboard.space') },
                // { value: 'CUSTOM', label: 'Custom' }
            ];

            function changeCustomDelimiter() {
                if(props.useCustomDelimiter) {
                    const delimiterExists = delimiterOptions.find(option => option.value === props.delimiter);
                    if(!delimiterExists)
                        context.emit('update:delimiter', delimiterOptions[0].value);
                }

                context.emit('update:useCustomDelimiter', !props.useCustomDelimiter);
            }

            const delimiterInputStyle = {
                width: '5em',
                minWidth: '5em',
                flex: '1 1 min-content',
                zIndex: 500,
            };

            const rangeInformationText = computed(() => {

                const start = props.skippedCount + 1;
                let end = props.skippedCount + props.showCount;
                if(end > props.total) {
                    end = props.total;
                }

                return `${start}-${end} ${t('global.paginator.of')} ${props.total}`;

            });

            const paginatorInputStyle = {
                minWidth: '5em',
            };


            return {
                t,
                changeCustomDelimiter,
                delimiterOptions,
                delimiterInputStyle,
                multiselectResetClasslist,
                rangeInformationText,
                paginatorInputStyle,
            };
        },

    };
</script>

<style scoped>
    .line {
        display: flex;
        flex: 1;
        justify-content: flex-start;
        align-items: center;
        flex-wrap: nowrap;
        gap: 2rem !important;
    }
</style>