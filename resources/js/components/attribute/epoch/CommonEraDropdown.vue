<template>
    <button
        type="button"
        class="btn btn-outline-secondary dropdown-toggle"
        :disabled="disabled"
        data-bs-toggle="dropdown"
        aria-haspopup="true"
        aria-expanded="false"
    >
        {{ modelValue }}
    </button>
    <ul
        uib-dropdown-menu
        class="dropdown-menu"
    >
        <a
            v-for="(label, i) in timeLabels"
            :key="i"
            class="dropdown-item"
            href="#"
            @click.prevent="context.emit('update:modelValue', label)"
        >
            {{ label }}
        </a>
    </ul>
</template>

<script>
    import { computed } from 'vue';

    import { getLabelsOf } from '@/helpers/attributes/epoch';

    export default {
        props: {
            format: {
                type: String,
                required: true,
            },
            modelValue: {
                type: String,
                required: true,
            },
        },
        emits: ['update:modelValue'],
        setup(props, context) {
            const timeLabels = computed(_ => getLabelsOf(props.format));

            return {
                context,
                timeLabels
            }
        }
    };
</script>

<style lang='scss' scoped></style>