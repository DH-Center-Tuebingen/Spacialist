<template>
    <div class="quotation-input d-flex align-items-end">
        <AutoTextarea
            v-model="state.updatedReference.description"
            class="form-control me-1"
        />
        <button
            type="button"
            class="btn btn-outline-success btn-sm me-1"
            :disabled="!isDirty && !state.pending"
            @click.prevent="update"
        >
            <i class="fas fa-fw fa-check" />
        </button>
        <button
            type="button"
            class="btn btn-outline-danger btn-sm"
            @click.prevent="cancel"
        >
            <i class="fas fa-fw fa-times" />
        </button>
    </div>
</template>

<script>
    import {
        computed,
        reactive,
    } from 'vue';

    import { _cloneDeep } from '@/helpers/helpers';

    import AutoTextarea from '@/components/forms/AutoTextarea.vue';

    export default {
        components: {
            AutoTextarea,
        },
        props: {
            value: {
                type: Object,
                required: true,
            },
        }, emits: ['update', 'cancel'],
        setup(props, { emit }) {
            const state = reactive({
                pending: false,
                updatedReference: _cloneDeep(props.value),
            });
            const successCallback = () => {
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
                state,
                update,
                cancel,
                isDirty,
            };
        },
    };
</script>

<style
    lang='scss'
    scoped
></style>