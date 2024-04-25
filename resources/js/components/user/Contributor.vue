<template>
    <div class="contributor col-md-6 d-flex flex-column align-items-start">
        <span>
            {{ value.name }}
        </span>
        <div class="roles d-flex gap-1 flex-wrap">
            <span
                v-for="role of value.roles"
                :key="`${value.name}-${role}`"
                class="badge bg-primary text-line"
            >
                {{ t(`main.about.roles.${role}`) }}
            </span>
        </div>
    </div>
</template>

<script>
    import { useI18n } from 'vue-i18n';

    export default {
        props: {
            value: {
                type: Object,
                required: true
            }
        }, setup() {
            return {
                t: useI18n().t
            };
        }
    };
</script>