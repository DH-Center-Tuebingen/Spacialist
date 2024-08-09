<template>
    <div class="d-flex align-items-center justify-content-between">
        <!-- TODO: Replace with Editable Field -->
        <EditableField
            :value="entity.name"
            @change="updateEntityName"
        />
        <!-- TODO: THE FOLLOWING WAS INSIDE THE HEADER ON HOVER - I DONT KNOW WHAT IT IS AND IF IT IS OBSOLETE OR NOT -->
        <!-- <small class="d-inline-flex gap-1">
                <button
                    v-show="state.hiddenAttributeCount > 0"
                    id="hidden-attributes-icon"
                    class="border-0 bg-body text-secondary p-0"
                    data-bs-container="body"
                    data-bs-toggle="popover"
                    data-bs-trigger="hover"
                    data-bs-placement="bottom"
                    :data-bs-content="state.hiddenAttributeListing"
                    data-bs-html="true"
                    data-bs-custom-class="popover-p-2"
                    @mousedown="showHiddenAttributes()"
                    @mouseup="hideHiddenAttributes()"
                >
                    <span v-show="state.hiddenAttributeState">
                        <span class="fa-layers fa-fw">
                            <i class="fas fa-eye fa-xs" />
                            <span
                                class="fa-layers-counter fa-counter-lg"
                                style="background:Tomato"
                            >
                                {{ state.hiddenAttributeCount }}
                            </span>
                        </span>
                    </span>
                    <span v-show="!state.hiddenAttributeState">
                        <span class="fa-layers fa-fw">
                            <i class="fas fa-eye-slash fa-xs" />
                            <span
                                class="fa-layers-counter fa-counter-lg"
                                style="background:Tomato"
                            >
                                {{ state.hiddenAttributeCount }}
                            </span>
                        </span>
                    </span>
                </button>
                <span
                    v-if="state.hasAttributeLinks"
                    class="dropdown bg-body text-secondary clickable me-1"
                >
                    <span
                        class="fa-layers fa-fw"
                        data-bs-toggle="dropdown"
                    >
                        <i class="fas fa-fw fa-xs fa-link fa-xs" />
                        <span
                            class="fa-layers-counter fa-counter-lg"
                            style="background:Tomato"
                        >
                            {{ state.entity.attributeLinks.length }}
                        </span>
                    </span>
                    <ul class="dropdown-menu">
                        <li
                            v-for="link in state.groupedAttributeLinks"
                            :key="link.id"
                        >
                            <router-link
                                :to="{ name: 'entitydetail', params: { id: link.id }, query: state.routeQuery }"
                                class="dropdown-item d-flex align-items-center gap-1"
                                :title="link.path.join(' / ')"
                            >
                                <entity-type-label
                                    :type="link.entity_type_id"
                                    :icon-only="true"
                                />
                                {{ link.name }}
                                <span class="text-muted small">{{ link.attribute_urls.join(', ') }}</span>
                            </router-link>
                        </li>
                    </ul>
                </span>
                <a
                    v-if="state.entityHeaderHovered && can('entity_write')"
                    href="#"
                    class="text-secondary"
                    @click.prevent="editEntityName()"
                >
                    <i class="fas fa-fw fa-edit fa-xs" />
                </a>
            </small> -->


        <div class="d-flex flex-row gap-2">
            <button
                type="submit"
                form="entity-attribute-form"
                class="btn btn-outline-success btn-sm"
                :disabled="!dirty || !can('entity_data_write')"
                @click.prevent="_ => $emit('save', entity)"
            >
                <i class="fas fa-fw fa-save" /> {{ t('global.save') }}
            </button>
            <button
                type="button"
                class="btn btn-outline-warning btn-sm"
                :disabled="!dirty"
                @click="_ => $emit('reset', entity)"
            >
                <i class="fas fa-fw fa-undo" /> {{ t('global.reset') }}
            </button>
            <button
                type="button"
                class="btn btn-outline-danger btn-sm"
                :disabled="!can('entity_delete')"
                @click="_ => $emit('delete', entity)"
            >
                <i class="fas fa-fw fa-trash" /> {{ t('global.delete') }}
            </button>
        </div>
    </div>
    <div class="d-flex justify-content-between my-2">
        <entity-type-label
            :type="entity.entity_type_id"
            :icon-only="false"
        />
        <div>
            <i class="fas fa-fw fa-user-edit" />
            <span
                class="ms-1"
                :title="date(lastModified, undefined, true, true)"
            >
                {{ ago(lastModified) }}
            </span>
            -
            <a
                v-if="entity.user"
                href="#"
                class="fw-medium"
                @click.prevent="showUserInfo(entityUser)"
            >
                {{ entityUser.name }}
                <user-avatar
                    :user="entityUser"
                    :size="20"
                    class="align-middle"
                />
            </a>
        </div>
    </div>
</template>

<script>

    import {
        can,
    } from '@/helpers/helpers.js';

    import {
        patchEntityName,
    } from '@/api.js';

    import store from '@/bootstrap/store.js';
    import { ago, date } from '@/helpers/filters.js';
    import { computed } from 'vue';
    import { useI18n } from 'vue-i18n';

    import EntityTypeLabel from '@/components/entity/EntityTypeLabel.vue';
    import EditableField from '../forms/EditableField.vue';

    export default {
        components: {
            EditableField,
            EntityTypeLabel,
        },
        props: {
            entity: {
                type: Object,
                required: true
            },
            entityUser: {
                type: Object,
                required: true
            },
            dirty: {
                type: Boolean,
                default: false,
                required: true
            },
        },
        emits: ['delete', 'reset', 'save'],
        setup(props) {

            const updateEntityName = name => {
                patchEntityName(props.entity.id, name).then(data => {
                    store.dispatch('updateEntity', {
                        ...data,
                        name,
                    });
                });
            };

            const lastModified = computed(_ => {
                return props.entity.updated_at || props.entity.created_at;
            });

            return {
                t: useI18n().t,
                can,
                ago,
                date,
                lastModified,
                updateEntityName,
            };
        }
    };
</script>