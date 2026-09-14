<template>
    <div class="markdown-text">
        <VueMarkdownIt :source="stringValue" />
    </div>
</template>

<script setup>
    import { VueMarkdownIt } from '@f3ve/vue-markdown-it';
    import { computed } from 'vue';

    const props = defineProps({
        value: {
            type: String,
            required: true,
        },
    });
    
    const stringValue = computed(() => {
        if(typeof props.value === 'string') {
            return props.value;
        }
        return '';
    });
</script>