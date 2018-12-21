<template>
    <div class="d-flex justify-content-start align-items-center">
        <div :style="style" class="color-gradient"></div>
        <span>{{ label }}</span>
    </div>
</template>

<script>
    export default {
        props: {
            from: {
                required: true,
                type: String
            },
            to: {
                required: true,
                type: String
            },
            label: {
                required: true,
                type: String
            }
        },
        computed: {
            style: function() {
                if(!this.from || !this.to) {
                    return '';
                }
                return `background: linear-gradient(to right, ${this.from}, ${this.to})`;
            }
        }
    }
</script>
