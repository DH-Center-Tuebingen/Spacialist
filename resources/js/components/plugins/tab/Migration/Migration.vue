<template>
    <div class="d-flex flex-column gap-2">
        <div
            v-if="!migrationLoaded"
            class="form-control text-center"
        >
            <i class="fas fa-2x fa-fw fa-spinner fa-spin" />
        </div>
        <div
            v-else
            class="form-control"
        >
            <template v-if="noMigrations">
                {{ t('main.plugins.info.no_migrations') }}
            </template>
            <template v-else>
                <ul class="list-unstyled mb-0 overflow-x-auto pt-1">
                    <li
                        v-for="migration in migrationList"
                        :key="migration.name"
                        class="mb-1"
                    >
                        <MigrationItem
                            :migration="migration"
                            @set="addMigrationToDatabase"
                        />
                    </li>
                </ul>
            </template>
        </div>
        <div class="d-flex gap-2 flex-row-reverse">
            <button
                class="btn btn-sm btn-outline-secondary"
                :disabled="!hasMissingMigrations"
                @click="migrate()"
            >
                {{ t('main.plugins.migrate') }}
            </button>
            <button
                class="btn btn-sm btn-outline-secondary"
                :disabled="noMigrations"
                @click="rollback()"
            >
                {{ t('main.plugins.rollback') }}
            </button>

            <button
                class="btn btn-sm btn-outline-secondary"
                :disabled="noMigrations"
                @click="updateMigrationState()"
            >
                Check Migration
            </button>
        </div>
    </div>
</template>

<script>
    import { computed, ref, onMounted } from 'vue';
    import { useI18n } from 'vue-i18n';

    // TODO:: move to api if still needed after rework
    import http from '@/bootstrap/http.js';
    import MigrationItem from './MigrationItem.vue';

    export default {
        components: {
            MigrationItem,
        },
        props: {
            value: {
                type: Object,
                required: true,
            },
        },
        setup(props) {
            const { t } = useI18n();
            const migrationList = ref([]);
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
                        migrationList.value = result.data;
                    } catch(error) {
                        console.error('Error adding migration to database', error);
                    }
                }
            };

            const migrate = async _ => {
                if(confirm(t('main.plugins.info.migration-confirm'))) {
                    const response = await http.post(`/plugin/migrate/${props.value.id}`);
                    migrationList.value = response.data;
                }
            };

            const rollback = async _ => {
                if(confirm(t('main.plugins.info.rollback-confirm'))) {
                     const response = await http.post(`/plugin/migrate/${props.value.id}/rollback`);
                     migrationList.value = response.data;
                }
            };

            const updateMigrationState = async _ => {
                try {
                    const result = await http.get(`/plugin/migrate/${props.value.id}/check`);
                    migrationList.value = result.data;
                    migrationLoaded.value = true;
                } catch(e) {
                    console.error('Error fetching migration state', e);
                    migrationList.value = [];
                }
            };

            const noMigrations = computed(() => {
                return migrationList.value.length === 0;
            });

            const hasMissingMigrations = computed(() => {
                const missingMigrations = migrationList.value.filter(m => m.ran === false)
                console.log("Missing Migrations: ", missingMigrations);
                return missingMigrations.length > 0;
            });

            return {
                t,
                addMigrationToDatabase,
                migrate,
                updateMigrationState,
                rollback,
                migrationList,
                migrationLoaded,
                noMigrations,
                hasMissingMigrations,
            };
        }
    };
</script>

<style lang='scss' scoped></style>