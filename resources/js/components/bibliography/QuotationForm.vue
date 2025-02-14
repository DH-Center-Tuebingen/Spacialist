<template>
    <div class="quotation-input d-flex flex-column gap-1">
        <textarea
            v-model="state.updatedReference.description"
            class="form-control me-1"
        />
        <div class="d-flex gap-2">
            <button
                type="button"
                class="btn btn-outline-success btn-sm"
                :disabled="!isDirty && !state.pending"
                @click.prevent="update"
            >
                <i class="fas fa-fw fa-check" />
                {{ t('global.update') }}
            </button>
            <button
                type="button"
                class="btn btn-outline-danger btn-sm"
                @click.prevent="cancel"
            >
                <i class="fas fa-fw fa-times" />
                {{ t('global.cancel') }}
            </button>
        </div>
    </div>
</template>

<script>
    import {
        computed,
        reactive,
    } from 'vue';

    import { useI18n } from 'vue-i18n';

    import { _cloneDeep } from '@/helpers/helpers.js';

    export default {
        props: {
            value: {
                type: Object,
                required: true,
            },
        }, emits: ['update', 'cancel'],
        setup(props, { emit }) {
            const { t } = useI18n();

            const state = reactive({
                pending: false,
                updatedReference: _cloneDeep(props.value),
            });

            const successCallback = _ => {
                state.pending = false;
            };

            const update = _ => {
                state.pending = true;
                emit('update', state.updatedReference, successCallback);
            };

            const cancel = () => {
                emit('cancel', props.value);
            };

            const isDirty = computed(_ => {
                return state.updatedReference.description !== props.value.description;
            });

            return {
                t,
                state,
                update,
                cancel,
                isDirty,
            };
        },
    };
</script>