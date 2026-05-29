<template>
    <div class="migration-item d-flex align-items-center justify-content-between gap-2">
        <div class="d-flex gap-1">
            <button
                v-if="migration.ran"
                class="btn btn-sm btn-outline-success border-0"
                disabled
            >
                <i
                    v-if="migration.ran"
                    class="fas fa-fw fa-check text-success"
                />
            </button>
            <button
                v-else
                class="btn btn-sm btn-outline-danger border-0"
                disabled=""
            >
                <i
                    class="fas fa-fw fa-times"
                    :title="t('main.plugins.info.add_migration_to_db')"
                />
            </button>
            <button
                v-if="!migration.ran"
                class="btn btn-sm btn-outline-secondary"
                @click="$emit('set', migration)"
                :title="t('main.plugins.info.migration_notice')"
            >
                {{ t('global.set') }}
            </button>
        </div>
        <span class="ms-3 text-truncate">{{ migration.name }}</span>
    </div>
</template>

<script setup>

    import { useI18n } from 'vue-i18n';
    const { t } = useI18n();

    defineProps({
        migration: {
            type: Object,
            required: true,
        },
    });

    const emit = defineEmits(['set']);

</script>