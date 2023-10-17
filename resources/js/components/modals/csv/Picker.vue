<template>
    <vue-final-modal
        class="modal-container modal"
        content-class="sp-modal-content"
        name="csv-uploader-modal"
    >
        <div class="sp-modal-content">
            <div class="modal-header">
                <h5 class="modal-title">
                    {{
                        t('main.csv.picker.title')
                    }}
                </h5>
                <button
                    type="button"
                    class="btn-close"
                    aria-label="Close"
                    data-bs-dismiss="modal"
                    @click="closeModal()"
                />
            </div>
            <div class="modal-body">
                <alert
                    :message="'Selet all the columns you want to import data from.'"
                    :type="'info'"
                    :noicon="false"
                />
                <alert
                    :message="'The order in which you pick the columns will be the order for the import. It is not possible skip columns.'"
                    :type="'warning'"
                    :noicon="false"
                    :icontext="t('global.note')"
                />
                <form>
                    <div
                        v-for="(sel, i) in selection"
                        :key="i"
                        class="form-check"
                    >
                        <input
                            :id="`picker-selection-${i}`"
                            v-model="state.pickedColumns"
                            class="form-check-input"
                            type="checkbox"
                            :value="sel"
                        >
                        <label
                            class="form-check-label"
                            :for="`picker-selection-${i}`"
                        >
                            {{ ucfirst(sel) }}
                        </label>
                    </div>
                    <div class="row">
                        <label
                            for="picker-selection-result"
                            class="col-2 col-form-label"
                            :class="state.warningClasses"
                        >
                            Picked Columns
                            <span v-show="!state.hasPickedEnough">
                                <i class="fas fa-fw fa-exclamation-triangle" />
                            </span>
                        </label>
                        <div class="col-10">
                            <input
                                id="picker-selection-result"
                                type="text"
                                readonly
                                class="form-control-plaintext"
                                :class="state.warningClasses"
                                :value="state.joinedPick"
                            >
                        </div>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button
                    type="button"
                    class="btn btn-outline-success"
                    data-bs-dismiss="modal"
                    :disabled="!state.hasPickedEnough"
                    @click="confirmModal()"
                >
                    <i class="fas fa-fw fa-check" /> {{ t('global.create') }}
                </button>
                <button
                    type="button"
                    class="btn btn-outline-secondary"
                    data-bs-dismiss="modal"
                    @click="closeModal()"
                >
                    <i class="fas fa-fw fa-times" /> {{ t('global.cancel') }}
                </button>
            </div>
        </div>
    </vue-final-modal>
</template>

<script>
    import {
        computed,
        reactive,
        toRefs,
    } from 'vue';

    import { useI18n } from 'vue-i18n';

    import {
        ucfirst,
    } from '@/helpers/filters.js';

    export default {
        props: {
            max: {
                type: Number,
                required: true,
            },
            forceMax: {
                type: Boolean,
                required: false,
                default: false,
            },
            selection: {
                type: Array,
                required: true,
            },
        },
        emits: ['confirm', 'closing'],
        setup(props, context) {
            const { t } = useI18n();
            const {
                max,
                forceMax,
                selection,
            } = toRefs(props);

            // FUNCTIONS
            const confirmModal = _ => {
                context.emit('confirm', state.pickedColumns);
            };
            const closeModal = _ => {
                context.emit('closing', false);
            };

            // DATA
            const state = reactive({
                pickedColumns: [],
                joinedPick: computed(_ => state.pickedColumns.map(c => ucfirst(c)).join(', ')),
                hasPickedEnough: computed(_ => {
                    if(!forceMax.value) {
                        return state.pickedColumns.length > 0 && state.pickedColumns.length <= max.value
                    } else {
                        return state.pickedColumns.length == max.value;
                    }
                }),
                warningClasses: computed(_ => {
                    return {
                        'text-danger': !state.hasPickedEnough,
                    };
                }),
            });

            // RETURN
            return {
                t,
                // HELPERS
                ucfirst,
                // LOCAL
                confirmModal,
                closeModal,
                // STATE
                state,
            }
        },
    }
</script>
