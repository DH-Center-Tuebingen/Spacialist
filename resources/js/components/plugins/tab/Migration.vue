<template>
    <div class="d-flex flex-column gap-2">
        <div
            v-if="!migrationLoaded"
            class="form-control"
        >
            {{ t('main.plugins.info.no_migration_data') }}
        </div>
        <div
            v-else
            class="form-control"
        >
            <template v-if="migrationState.length == 0">
                {{ t('main.plugins.info.no_migrations') }}
            </template>
            <template v-else>
                <ul class="list-unstyled mb-0 overflow-x-auto">
                    <li
                        v-for="migration in migrationState"
                        :key="migration.name"
                        class="d-flex justify-content-between align-items-center mb-1"
                    >
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
                            class="btn btn-sm btn-outline-danger"
                            @click="addMigrationToDatabase(migration)"
                        >
                            <i
                                class="fas fa-fw fa-times"
                                :title="t('main.plugins.info.add_migration_to_db')"
                            />
                        </button>

                        <span class="ms-3">{{ migration.name }}</span>
                    </li>
                </ul>
            </template>
        </div>
        <div class="d-flex gap-2 flex-row-reverse">
            <button
                class="btn btn-sm btn-outline-secondary"
                @click="migrate()"
            >
                {{ t('main.plugins.migrate') }}
            </button>
            <button
                class="btn btn-sm btn-outline-secondary"
                @click="rollback()"
            >
                {{ t('main.plugins.rollback') }}
            </button>

            <button
                class="btn btn-sm btn-outline-secondary"
                @click="updateMigrationState()"
            >
                Check Migration
            </button>
        </div>

        <alert :message="t('main.plugins.info.migration_notice')" />
    </div>
</template>

<script>
    import { ref, onMounted } from 'vue';
    import { useI18n } from 'vue-i18n';

    // TODO:: move to api if still needed after rework
    import http from '@/bootstrap/http.js';

    export default {
        props: {
            value: {
                type: Object,
                required: true,
            },
        },
        setup(props) {
            const { t } = useI18n();
            const migrationState = ref([]);
            const migrationLoaded = ref(false);

            onMounted(() => {
                updateMigrationState();
            });

            const addMigrationToDatabase = async (migration) => {
                if(confirm(t('main.plugins.info.add_migration_to_db'))) {
                    try {
                        const result = await http.post(`/plugin/migrate/${props.value.id}/force_add`, {
                            name: migration.name,
                        });
                        migrationState.value = result.data;
                    } catch(error) {
                        console.error('Error adding migration to database', error);
                    }
                }
            };

            const migrate = async _ => {
                if(confirm(t('main.plugins.info.migration-confirm'))) {
                    return http.post(`/plugin/migrate/${props.value.id}`);
                }
            };

            const rollback = async _ => {
                if(confirm(t('main.plugins.info.rollback-confirm'))) {
                    return http.post(`/plugin/migrate/${props.value.id}/rollback`);
                }
            };

            const updateMigrationState = async _ => {
                try {
                    const result = await http.get(`/plugin/migrate/${props.value.id}/check`);
                    migrationState.value = result.data;
                    migrationLoaded.value = true;
                } catch(e) {
                    console.error('Error fetching migration state', e);
                    migrationState.value = [];
                }
            };

            return {
                t,
                addMigrationToDatabase,
                migrate,
                updateMigrationState,
                rollback,
                migrationState,
                migrationLoaded,
            };
        }
    };
</script>

<style lang='scss' scoped></style>