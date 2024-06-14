<template>
    <div class="entity-history-row">
        <header class="d-flex flex-row gap-3 align-items-center">
            <span :title="entry.description">

                <span>
                    <i :class="iconClass" />
                </span>
            </span>
            <div>
                <span class="fw-bold">
                    {{ entry.properties.attributes.name }}
                </span>
                <!-- <span
                    v-if="isCreate"
                    class="fw-bold"
                >
                    {{ t('main.history.created_as') }}
                </span>
                <span
                    v-else-if="isRename"
                    class="fw-bold"
                >
                    {{ t('main.history.entity.name_update') }}
                </span>
                <span
                    v-else-if="isMove"
                    class="fw-bold"
                >
                    {{ t('main.history.entity.moved') }}
                </span>
                <span
                    v-else
                    class="fw-bold"
                >
                    {{ t('main.history.entity.certainty_update') }}
                </span> -->
            </div>

            <!-- <div class="flex-grow-1">
                <template v-if="entry.subject_type == 'App\\Entity'">
                    <div v-if="entry.description == 'created'">
                        <div class="d-flex flex-row gap-2 align-items-center">
  
                            <span class="badge bg-primary bg-opacity-75">
                                {{ entry.properties.attributes.name }}
                            </span>
                            <div class="d-flex flex-row">
                                (
                                <entity-type-label
                                    :type="entry.properties.attributes.entity_type_id"
                                    :icon-only="false"
                                />
                                )
                            </div>
                        </div>
                        <div
                            v-if="entry.properties.attributes.root_entity_id"
                            class="d-flex flex-row gap-2 align-items-center"
                        >
                            <span class="fw-bold">
                                {{ t('main.history.entity.created_in') }}
                            </span>
                            <router-link
                                class="link-underline link-underline-opacity-0 link-underline-opacity-100-hover"
                                :to="{ name: 'entitydetail', params: { id: entry.properties.attributes.root_entity_id }, query: route.query }"
                                append
                            >
                                {{ getEntity(entry.properties.attributes.root_entity_id).name }}
                            </router-link>
                        </div>
                    </div>
                    <div
                        v-else-if="entry.description == 'updated'"
                        class="d-flex flex-column align-items-start gap-2"
                    >
                        <div
                            v-if="entry.properties.old.name && entry.properties.attributes.name"
                            class="d-flex flex-row gap-2 align-items-center"
                        >

                            <span class="badge bg-danger bg-opacity-75">
                                {{ entry.properties.old.name }}
                            </span>
                            <i class="fas fa-fw fa-2xs fa-arrow-right" />
                            <span class="badge bg-success bg-opacity-75">
                                {{ entry.properties.attributes.name }}
                            </span>
                        </div>
                        <div
                            v-else-if="(entry.properties.old.root_entity_id || entry.properties.old.rank) && entry.properties.attributes.root_entity_id || entry.properties.attributes.rank"
                            class="d-flex flex-row gap-2 align-items-center"
                        >

                            <span class="badge bg-danger bg-opacity-75">
                                <template v-if="entry.properties.old.root_entity_id">
                                    <span v-if="getEntity(entry.properties.old.root_entity_id).name">
                                        {{ getEntity(entry.properties.old.root_entity_id).name }}
                                    </span>
                                    <span
                                        v-else
                                        class="fst-italic"
                                        :title="t('main.history.entity.name_unknown_info')"
                                    >
                                        {{ t('main.history.entity.name_unknown') }}
                                    </span>
                                </template>
<span v-else>
    {{ t('main.entity.top_level') }}
</span>
|
<span :title="t('main.history.entity.rank')">
    {{ entry.properties.old.rank }}
</span>
</span>
<i class="fas fa-fw fa-2xs fa-arrow-right" />
<span class="badge bg-success bg-opacity-75">
    <template v-if="entry.properties.attributes.root_entity_id">
                                    <span v-if="getEntity(entry.properties.attributes.root_entity_id).name">
                                        {{ getEntity(entry.properties.attributes.root_entity_id).name }}
                                    </span>
                                    <span
                                        v-else
                                        class="fst-italic"
                                        :title="t('main.history.entity.name_unknown_info')"
                                    >
                                        {{ t('main.history.entity.name_unknown') }}
                                    </span>
                                </template>
    <span v-else>
        {{ t('main.entity.top_level') }}
    </span>
    |
    <span :title="t('main.history.entity.rank')">
        {{ entry.properties.attributes.rank }}
    </span>
</span>
</div>
<div v-else>
    Something has changed...
    {{ entry.properties }}
</div>
</div>
</template>
<template v-else-if="entry.subject_type == 'attribute_values'">
                    <div v-if="entry.description == 'created'">
                        <span class="d-flex flex-row align-items-center gap-2">
                            <span class="fw-bold">
                                {{ t('main.history.entity.value_add_attribute') }}
                            </span>
                            <span>
                                {{ getAttributeName(entry.attribute.id) }}
                            </span>
                            <a
                                href="#"
                                class="text-reset"
                                @click.prevent="showHistoryChange[entry.id] = !showHistoryChange[entry.id]"
                            >
                                <span v-show="showHistoryChange[entry.id]">
                                    <i class="fas fa-fw fa-eye" />
                                </span>
                                <span v-show="!showHistoryChange[entry.id]">
                                    <i class="fas fa-fw fa-eye-slash" />
                                </span>
                            </a>
                        </span>
                  
                    </div>
                    <div v-else-if="entry.description == 'updated'">
                        <template
                            v-if="hasHistoryEntryKey(entry.properties.attributes, '!certainty') || hasHistoryEntryKey(entry.properties.old, '!certainty')"
                        >
                            <span class="d-flex flex-row align-items-center gap-2">
                                <span class="fw-bold">
                                    {{ t('main.history.entity.value_update_attribute') }}
                                </span>
                                <span>
                                    {{ getAttributeName(entry.attribute.id) }}
                                </span>
                                <a
                                    href="#"
                                    class="text-reset"
                                    @click.prevent="showHistoryChange[entry.id] = !showHistoryChange[entry.id]"
                                >
                                    <span v-show="showHistoryChange[entry.id]">
                                        <i class="fas fa-fw fa-eye" />
                                    </span>
                                    <span v-show="!showHistoryChange[entry.id]">
                                        <i class="fas fa-fw fa-eye-slash" />
                                    </span>
                                </a>
                            </span>
                            
                        </template>
<div v-else class="d-flex flex-row gap-2 align-items-center">
    <span class="fw-bold">
        {{ t('main.history.entity.certainty_update') }}
    </span>
    <span>
        {{ getAttributeName(entry.attribute.id) }}
    </span>
    <div class="d-flex flex-row align-items-center gap-1">
        <span class="badge bg-danger bg-opacity-75">
            {{ entry.properties.old.certainty || t('main.history.entity.certainty_unknown')
            }}
        </span>
        <i class="fas fa-fw fa-xs fa-arrow-right" />
        <span class="badge bg-success bg-opacity-75">
            {{ entry.properties.attributes.certainty ||
            t('main.history.entity.certainty_unknown') }}
        </span>
    </div>
</div>
</div>
</template>
</div> -->
            <div class="text-nowrap">
                <span
                    class="badge bg-opacity-75 pe-3"
                    :class="{ 'bg-warning': entry.user_id == creatorId && entry.user_id != userId(), 'bg-primary': entry.user_id != creatorId && entry.user_id != userId(), 'bg-success': entry.user_id == userId() }"
                >
                    {{ getUsername(entry) }}
                </span>
                <user-avatar
                    :user="getUserBy(entry.user_id)"
                    :size="20"
                    class="align-middle ms-n2"
                />
            </div>
            <span
                class="small ms-auto text-secondary text-nowrap"
                :title="date(entry.created_at)"
            >
                {{ ago(entry.created_at) }}
            </span>
            <div
                role="button"
                @click="collapsed = !collapsed"
            >
                <span v-show="collapsed">
                    <i class="fas fa-fw fa-eye" />
                </span>

                <span v-show="!collapsed">
                    <i class="fas fa-fw fa-eye-slash" />
                </span>
            </div>
        </header>

        <pre>
            {{ entry }}
        </pre>

        <div
            v-if="!collapsed"
            class="mt-2"
        >
            <attribute-list
                v-if="isCreate"
                :group="{ name: 'entity-history-created', pull: false, put: false }"
                :classes="'mx-0 py-2 px-2 rounded-3 bg-primary bg-opacity-50'"
                :attributes="formatHistoryEntryAttributes(entry.attribute)"
                :values="formatHistoryEntryValue(entry.attribute, entry.value_after)"
                :options="{ 'hide_labels': true, 'item_classes': 'px-0' }"
                :selections="{}"
                :preview="true"
            />


            <div
                v-if="isUpdate"
                class="d-flex flex-row gap-2 align-items-center"
            >
                <attribute-list
                    v-if="hasHistoryEntryKey(entry.properties.old, '!certainty')"
                    :group="{ name: 'entity-history-changed-from', pull: false, put: false }"
                    :classes="'flex-grow-1 mx-0 py-2 px-2 rounded-3 bg-danger bg-opacity-50'"
                    :attributes="formatHistoryEntryAttributes(entry.attribute)"
                    :values="formatHistoryEntryValue(entry.attribute, entry.value_before)"
                    :options="{ 'hide_labels': true, 'item_classes': 'px-0' }"
                    :selections="{}"
                    :preview="true"
                />
                <span
                    v-else
                    class="badge bg-danger"
                >
                    <span class="fst-italic">
                        {{ t('main.history.no_value') }}
                    </span>
                </span>
                <i class="fas fa-fw fa-arrow-right" />
                <attribute-list
                    v-if="hasHistoryEntryKey(entry.properties.attributes, '!certainty')"
                    :group="{ name: 'entity-history-changed-to', pull: false, put: false }"
                    :classes="'flex-grow-1 mx-0 py-2 px-2 rounded-3 bg-success bg-opacity-50'"
                    :attributes="formatHistoryEntryAttributes(entry.attribute)"
                    :values="formatHistoryEntryValue(entry.attribute, entry.value_after)"
                    :options="{ 'hide_labels': true, 'item_classes': 'px-0' }"
                    :selections="{}"
                    :preview="true"
                />
                <span
                    v-else
                    class="badge bg-danger"
                >
                    <span class="fst-italic">
                        {{ t('main.history.no_value') }}
                    </span>
                </span>
            </div>
        </div>
    </div>
</template>

<script>

    import {
        computed,
        ref
    } from 'vue';

    import {
        useI18n
    } from 'vue-i18n';


    import {
        ago,
        date
    } from '@/helpers/filters.js';


    import {
        getConcept,
        userId,
        getAttributeName,
        getEntity,
        getUserBy,
    } from '@/helpers/helpers.js';

    export default {
        props: {
            entry: {
                type: Object,
                required: true
            },
            creatorId: {
                type: Number,
                required: true
            }
        },
        setup(props) {

            const isCreate = computed(() => {
                return props.entry.description === 'created';
            });

            const isUpdate = computed(() => {
                return props.entry.description === 'updated';
            });

            const isMove = computed(() => {
                let entry = props.entry;
                return isUpdate.value &&
                    (entry.properties.old.root_entity_id || entry.properties.old.rank) &&
                    (entry.properties.attributes.root_entity_id || entry.properties.attributes.rank);
            });

            const isRename = computed(() => {
                return isUpdate.value && props.entry.properties.old.name !== props.entry.properties.attributes.name;
            });


            const iconClass = computed(() => {
                let description = props.entry?.description;

                if(isCreate.value)
                    return 'fas fa-fw fa-plus text-success';
                if(isUpdate.value)
                    return 'fas fa-fw fa-edit text-warning';

                return 'fas fa-fw fa-question text-danger';

            });

            const getUsername = (entry) => {
                const entryUser = getUserBy(entry.user_id);
                if(entryUser) {
                    return entryUser.name;
                } else {
                    return 'N/A';
                }
            };

            const collapsed = ref(true);


            const hasHistoryEntryKey = (entry, key) => {
                // if starts with !, func checks if there is any other key than the one provided
                if(key.startsWith('!')) {
                    const searchKey = key.substr(1);
                    const keys = Object.keys(entry);
                    return !keys.includes(searchKey);
                }

                return !!entry[key];
            };


            const formatHistoryEntryValue = (attribute, value) => {
                console.log(attribute);
                const compValue = {
                    isDisabled: true,
                };
                if(attribute.datatype == 'string-sc') {
                    compValue.value = {
                        id: getConcept(value).id,
                        concept_url: value,
                    };
                } else if(attribute.datatype == 'string-mc') {
                    compValue.value = value;
                } else if(attribute.datatype == 'entity') {
                    compValue.value = value.id;
                    compValue.name = value.name == 'main.entity.metadata.deleted_entity_name' ? t(value.name, { id: value.id }) : value.name;
                } else if(attribute.datatype == 'entity-mc') {
                    compValue.value = value.map(v => v.id);
                    compValue.name = value.map(v => {
                        return v.name == 'main.entity.metadata.deleted_entity_name' ? t(v.name, { id: v.id }) : v.name;
                    });
                } else {
                    compValue.value = value;
                }

                return {
                    [attribute.id]: compValue,
                };
            };
            const formatHistoryEntryAttributes = attr => {
                console.log(attr);
                attr.isDisabled = true;
                return [
                    attr
                ];
            };


            return {
                ago,
                collapsed,
                date,
                formatHistoryEntryAttributes,
                formatHistoryEntryValue,
                getAttributeName,
                getConcept,
                getEntity,
                getUserBy,
                getUsername,
                hasHistoryEntryKey,
                iconClass,
                isCreate,
                isUpdate,
                isMove,
                isRename,
                userId,
                t: useI18n().t
            };
        }
    };

</script>