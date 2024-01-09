<template>
    <div class="spinner">
        <i 
            class="fas fa-circle-notch fa-spin fa-fw"
            :class="sizeClass"
        />
    </div>
</template>

<script>

export default {
    name: 'Spinner',
    props: {
        size: {
            type: String,
            default: '',
        },
    },
    computed: {
        sizeClass() {
            return `fa-${this.size}`;
        },
    },
};

</script>

