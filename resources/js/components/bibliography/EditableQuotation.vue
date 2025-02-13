<template>
    <div class="editable-quotation">
        <header class="d-flex flex-row justify-content-end small ms-2">
            <span
                v-if="value.updated_at"
                class="text-muted fw-light"
            >
                {{ date(value.updated_at) }}
            </span>
            <div class="dropdown ms-1">
                <span
                    :id="`edit-reference-dropdown-${value.id}`"
                    class="clickable text-muted"
                    data-bs-toggle="dropdown"
                    aria-haspopup="true"
                    aria-expanded="false"
                >
                    <i class="fas fa-fw fa-ellipsis-h" />
                </span>
                <div
                    class="dropdown-menu"
                    :aria-labelledby="`edit-reference-dropdown-${value.id}`"
                >
                    <a
                        class="dropdown-item"
                        href="#"
                        @click.prevent="edit"
                    >
                        <i class="fas fa-fw fa-edit text-info" /> {{ t('global.edit') }}
                    </a>
                    <a
                        class="dropdown-item"
                        href="#"
                        @click.prevent="remove"
                    >
                        <i class="fas fa-fw fa-trash text-danger" /> {{ t('global.delete') }}
                    </a>
                </div>
            </div>
        </header>
        <div class="flex-grow-1">
            <Quotation
                v-if="!isEditing"
                :value="value"
            />
            <!-- 
                Currently this only allows editing the text of the quote
                but not the literature reference. This could be easily done
                by using the ReferenceForm component here.
                
                But to stick with the current design, we only allow editing
                the quote text. Which is handled by the QuotationInput component,
                which contains the former code.
            -->
            <QuotationInput
                v-else
                :value="value"
                @cancel="cancelEdit"
                @update="update"
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
    import {
        computed,
        reactive
    } from 'vue';

    export default {
        components: {
            Quotation,
            QuotationInput,
        },
        props: {
            value: {
                type: Object,
                required: true,
            },
            editing: {
                type: Boolean,
                default: false,
            },
            editable: {
                type: Boolean,
                default: true,
            },
        },
        emits: ['update', 'delete'],
        setup(props, { emit }) {
            const state = reactive({
                editing: props.editing,
            });


            const cancelEdit = _ => {
                state.editing = false;
            };

            const edit = _ => {
                if(!props.editable) return;

                if(state.editing == false) {
                    state.editing = true;
                }
            };

            const update = reference => {
                emit('update', reference, (success) => {
                    if(success) {
                        cancelEdit();
                    } else {
                        console.error('Failed to update reference');
                    }
                });
            };

            const remove = _ => {
                emit('delete', props.value);
            };

            const isEditing = computed(_ => {
                return props.editable && state.editing;
            });

            return {
                t: useI18n().t,
                //External
                date,
                //Local
                isEditing,
                state,
                edit,
                cancelEdit,
                remove,
                update,
            };
        }
    };
</script>