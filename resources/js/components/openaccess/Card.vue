<template>
    <div class="card mb-2">
        <div class="row g-0">
            <!-- <div class="col-md-2">
                <img src="" class="img-fluid rounded-start">
            </div> -->
            <div class="col-md-12">
                <div class="card-body">
                    <h5 class="card-title fw-bold">
                        {{ entity.name }}
                    </h5>
                    <p class="card-text">
                        <small class="text-muted d-flex flex-row justify-content-between">
                            <div>
                                <span class="fw-bold">
                                    Added:
                                </span>
                                <span :title="date(entity.created_at)">
                                    {{ ago(entity.created_at) }}
                                </span>
                            </div>
                            <div>
                                <span class="fw-bold">
                                    Last updated:
                                </span>
                                <span :title="date(entity.updated_at)">
                                    {{ ago(entity.updated_at) }}
                                </span>
                            </div>
                            <div>
                                <span class="fw-bold">
                                    Updated by:
                                </span>
                                <a :href="`mailto:${entity.user.email}`">
                                    {{ entity.user.name }}
                                </a>
                            </div>
                        </small>
                    </p>
                    <div class="d-flex flex-row justify-content-center gap-3">
                        <hr class="text-muted flex-grow-1"/>
                        <button class="btn btn-fab btn-primary" @click="toggleShowData()">
                            <span v-show="state.showData">
                                <i class="fas fa-fw fa-minus roll-in"></i>
                            </span>
                            <span v-show="!state.showData">
                                <i class="fas fa-fw fa-plus roll-in"></i>
                            </span>
                        </button>
                        <hr class="text-muted flex-grow-1"/>
                    </div>
                    <div class="bg-white rounded-2 p-3 mt-2" v-show="state.showData">
                        <h6 class="card-text fw-bold">
                            Entity Data
                        </h6>
                        <attribute-list
                            class="fade-in-fast"
                            v-if="state.attributes.length > 0"
                            :attributes="state.attributes"
                            :selections="{}"
                            :options="{'hide_labels': true, 'hide_entity_link': true}"
                            :values="state.values"
                            :disable-drag="true"
                        />
                        <p class="alert alert-warning" v-else>
                            No data available.
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>

<script>
    import {
        computed,
        reactive,
        toRefs,
    } from 'vue';

    import {
        ago,
        date,
    } from '@/helpers/filters.js';

    import { useI18n } from 'vue-i18n';

    export default {
        props: {
            entity: {
                required: true,
                type: Object,
            },
        },
        setup(props) {
            const { t } = useI18n();

            const {
                entity,
            } = toRefs(props);

            // FETCH

            // DATA
            const state = reactive({
                showData: false,
                attributes: computed(_ => {
                    return entity.value.attributes.map(a => {
                        return {
                            ...a,
                            isDisabled: true,
                        };
                    });
                }),
                values: computed(_ => {
                    const vals = {};
                    for(let i=0; i<entity.value.attributes.length; i++) {
                        const curr = entity.value.attributes[i];
                        vals[curr.id] = {
                            value: curr.value,
                        };
                    }
                    return vals;
                })
            });

            // FUNCTIONS
            const toggleShowData = _ => {
                state.showData = !state.showData
            };

            // WATCHER

            // RETURN
            return {
                t,
                // HELPERS
                ago,
                date,
                // LOCAL
                toggleShowData,
                // PROPS
                entity,
                // STATE
                state,
            };
        }
    }
</script>