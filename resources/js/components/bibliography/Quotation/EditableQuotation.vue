<template>
    <div class="editable-quotation">
        <header class="d-flex flex-row small ms-2">
            <span class="text-muted fw-light">
                {{ date(reference.updated_at) }}
            </span>
            <div class="dropdown ms-1">
                <span
                    :id="`edit-reference-dropdown-${reference.id}`"
                    class="clickable text-muted"
                    data-bs-toggle="dropdown"
                    aria-haspopup="true"
                    aria-expanded="false"
                >
                    <i class="fas fa-fw fa-ellipsis-h" />
                </span>
                <div
                    class="dropdown-menu"
                    :aria-labelledby="`edit-reference-dropdown-${reference.id}`"
                >
                    <a
                        class="dropdown-item"
                        href="#"
                        @click.prevent="enableEditReference(reference)"
                    >
                        <i class="fas fa-fw fa-edit text-info" /> {{ t('global.edit') }}
                    </a>
                    <a
                        class="dropdown-item"
                        href="#"
                        @click.prevent="onDeleteReference(reference)"
                    >
                        <i class="fas fa-fw fa-trash text-danger" /> {{ t('global.delete') }}
                    </a>
                </div>
            </div>
        </header>
        <div class="flex-grow-1">
            <Quotation
                v-if="!state.editing"
                :value="reference"
            />
            <QuotationInput
                v-else
                :state="state"
                :reference="reference"
            />
        </div>
    </div>
</template>

<script>
    import {
        date,
    } from '@/helpers/filters.js';

    import Quotation from './Quotation.vue';
    import QuotationInput from './QuotationInput.vue';
    import { useI18n } from 'vue-i18n';
    import { reactive } from 'vue';

    export default {
        components: {
            Quotation,
            QuotationInput,
        },
        props: {
            reference: {
                type: Object,
                required: true,
            },
        },
        setup() {
            const state = reactive({
                editing: false,
                editItem: {},
            });

            const enableEditReference = reference => {
                state.editing = true;
                state.editItem = {
                    ...reference
                };
            };
            const cancelEditReference = _ => {
                state.editing = false;
                state.editItem = {};
            };

            return {
                t: useI18n().t,
                //External
                date,
                //Local
                state,
                enableEditReference,
                cancelEditReference,
            };
        }
    };
</script>

<style lang='scss' scoped></style>