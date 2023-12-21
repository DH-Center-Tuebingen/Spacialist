<template>
    <div class="h-100 d-flex flex-column">
        <entity-breadcrumbs
            v-if="state.showBreadcrumb"
            class="mb-2 small"
            :entity="state.entity"
        />
        <div class="d-flex align-items-center justify-content-between">
            <h3
                class="mb-0"
                @mouseenter="onEntityHeaderHover(true)"
                @mouseleave="onEntityHeaderHover(false)"
            >
                <span v-if="!state.entity.editing">
                    {{ state.entity.name }}
                    <small class="d-inline-flex gap-1">
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
                    </small>
                </span>
                <form
                    v-else
                    class="d-flex flex-row"
                    @submit.prevent="updateEntityName()"
                >
                    <input
                        v-model="state.editedEntityName"
                        type="text"
                        class="form-control form-control-sm me-2"
                    >
                    <button
                        type="submit"
                        class="btn btn-outline-success btn-sm me-2"
                    >
                        <i class="fas fa-fw fa-check" />
                    </button>
                    <button
                        type="reset"
                        class="btn btn-outline-danger btn-sm"
                        @click="cancelEditEntityName()"
                    >
                        <i class="fas fa-fw fa-ban" />
                    </button>
                </form>
            </h3>
            <div class="d-flex flex-row gap-2">
                <button
                    type="submit"
                    form="entity-attribute-form"
                    class="btn btn-outline-success btn-sm"
                    :disabled="!state.formDirty || !can('entity_data_write')"
                    @click.prevent="saveEntity()"
                >
                    <i class="fas fa-fw fa-save" /> {{ t('global.save') }}
                </button>
                <button
                    type="button"
                    class="btn btn-outline-warning btn-sm"
                    :disabled="!state.formDirty"
                    @click="resetForm()"
                >
                    <i class="fas fa-fw fa-undo" /> {{ t('global.reset') }}
                </button>
                <button
                    type="button"
                    class="btn btn-outline-danger btn-sm"
                    :disabled="!can('entity_delete')"
                    @click="confirmDeleteEntity()"
                >
                    <i class="fas fa-fw fa-trash" /> {{ t('global.delete') }}
                </button>
            </div>
        </div>
        <div class="d-flex justify-content-between my-2">
            <entity-type-label
                :type="state.entity.entity_type_id"
                :icon-only="false"
            />
            <div>
                <i class="fas fa-fw fa-user-edit" />
                <span
                    class="ms-1"
                    :title="date(state.lastModified, undefined, true, true)"
                >
                    {{ ago(state.lastModified) }}
                </span>
                -
                <a
                    v-if="state.entity.user"
                    href="#"
                    class="fw-medium"
                    @click.prevent="showUserInfo(state.entityUser)"
                >
                    {{ state.entityUser.name }}
                    <user-avatar
                        :user="state.entityUser"
                        :size="20"
                        class="align-middle"
                    />
                </a>
            </div>
        </div>
        <ul
            id="entity-detail-tabs"
            class="nav nav-tabs"
            role="tablist"
        >
            <li
                v-for="(tg, key) in state.entityGroups"
                :key="`attribute-group-${tg.id}-tab`"
                class="nav-item"
                role="presentation"
            >
                <a
                    :id="`active-entity-attributes-group-${tg.id}-tab`"
                    class="nav-link active-entity-attributes-tab active-entity-detail-tab d-flex gap-2 align-items-center"
                    href="#"
                    @click.prevent="setDetailPanel(`attributes-${tg.id}`)"
                >
                    <span class="fa-layers fa-fw">
                        <i class="fas fa-fw fa-layer-group" />
                        <span class="fa-layers-counter fa-counter-lg bg-secondary-subtle text-reset">
                            {{ tg.data.length }}
                        </span>
                    </span>
                    <span v-if="key == 'default'">
                        {{ t('main.entity.tabs.default') }}
                    </span>
                    <span v-else>
                        {{ translateConcept(key) }}
                    </span>
                    <div
                        v-if="state.dirtyStates[tg.id]"
                        class="d-flex flex-row gap-2 align-items-center"
                        @mouseover="showTabActions(tg.id, true)"
                        @mouseleave="showTabActions(tg.id, false)"
                    >
                        <i class="fas fa-fw fa-2xs fa-circle text-warning" />
                        <div
                            v-show="state.attributeGrpHovered == tg.id"
                        >
                            <a
                                href="#"
                                @click.prevent.stop="saveEntity(`${tg.id}`)"
                            >
                                <i class="fas fa-fw fa-save text-success" />
                            </a>
                            <a
                                href="#"
                                @click.prevent.stop="resetForm(`${tg.id}`)"
                            >
                                <i class="fas fa-fw fa-undo text-warning" />
                            </a>
                        </div>
                    </div>
                </a>
            </li>
            <!-- empty nav-item to separate metadata and comments from attributes -->
            <li
                class="nav-item nav-item-list-divider ms-auto"
            />
            <li
                v-show="can('entity_read')"
                class="nav-item"
                role="presentation"
            >
                <a
                    id="active-entity-metadata-tab"
                    class="nav-link active-entity-detail-tab d-flex gap-2 align-items-center"
                    href="#"
                    @click.prevent="setDetailPanel('metadata')"
                >
                    <i class="fas fa-fw fa-file-shield" />
                    {{ t('main.entity.tabs.metadata') }}
                    <span
                        v-if="!state.entity.metadata || !state.entity.metadata.licence"
                        :title="t('global.licence_missing')"
                    >
                        <i class="fas fa-exclamation text-warning" />
                    </span>
                </a>
            </li>
            <li
                v-show="can('comments_read')"
                class="nav-item"
                role="presentation"
            >
                <a
                    id="active-entity-comments-tab"
                    class="nav-link active-entity-detail-tab d-flex gap-2 align-items-center"
                    href="#"
                    @click.prevent="setDetailPanel('comments')"
                >
                    <span class="fa-layers fa-fw">
                        <i class="fas fa-fw fa-comments" />
                        <span class="fa-layers-counter fa-counter-lg bg-secondary-subtle text-reset">
                            {{ state.entity.comments_count }}
                        </span>
                    </span>
                    {{ t('main.entity.tabs.comments') }}
                </a>
            </li>
        </ul>
        <div
            id="entity-detail-tab-content"
            class="tab-content col ps-0 pe-0 overflow-hidden"
        >
            <div
                v-for="tg in state.entityGroups"
                :id="`active-entity-attributes-panel-${tg.id}`"
                :key="`attribute-group-${tg.id}-panel`"
                class="tab-pane fade h-100 active-entity-detail-panel active-entity-attributes-panel show active"
                role="tabpanel"
            >
                <form
                    :id="`entity-attribute-form-${tg.id}`"
                    :name="`entity-attribute-form-${tg.id}`"
                    class="h-100"
                >
                    <attribute-list
                        v-if="state.attributesFetched"
                        :ref="el => setAttrRefs(el, tg.id)"
                        v-dcan="'entity_data_read'"
                        class="pt-2 h-100 scroll-y-auto scroll-x-hidden"
                        :attributes="tg.data"
                        :hidden-attributes="state.hiddenAttributeList"
                        :show-hidden="state.hiddenAttributeState"
                        :disable-drag="true"
                        :metadata-addon="hasReferenceGroup"
                        :selections="state.entityTypeSelections"
                        :values="state.entity.data"
                        @dirty="e => setFormState(e, tg.id)"
                        @metadata="showMetadata"
                    />
                </form>
            </div>
            <div
                v-show="can('entity_read')"
                id="active-entity-metadata-panel"
                class="tab-pane fade h-100 active-entity-detail-panel d-flex flex-column overflow-hidden"
                role="tabpanel"
            >
                <div
                    class="mt-3"
                >
                    <button
                        type="button"
                        class="btn btn-sm btn-outline-success"
                        @click="saveMetadata"
                    >
                        <i class="fas fa-fw fa-save" />
                        {{ t('main.entity.metadata.save') }}
                    </button>
                </div>
                <div
                    class="d-flex flex-row gap-2 justify-content-between align-items-center mt-2"
                >
                    <div>
                        <h5 class="mb-1">
                            {{ t('global.creator') }}
                        </h5>
                        <div
                            v-if="getUserBy(state.entity.creator)"
                            class="d-flex flex-row gap-2 align-items-center"
                        >
                            <a
                                href="#"
                                @click.prevent="showUserInfo(getUserBy(state.entity.creator))"
                            >
                                <span 
                                    class="badge bg-primary bg-opacity-75 pe-3"
                                    :class="{'bg-primary': state.entity.creator != userId(), 'bg-success': state.entity.creator == userId()}"
                                >
                                    {{ getUserBy(state.entity.creator).name }}
                                </span>
                                <user-avatar
                                    :user="getUserBy(state.entity.creator)"
                                    :size="20"
                                    class="align-middle ms-n2"
                                />
                            </a>
                        </div>
                        <span v-else>
                            Fetching…
                        </span>
                    </div>
                    <div>
                        <h5 class="mb-1">
                            {{ t('global.editors') }}
                        </h5>
                        <div
                            v-if="state.entity.editors"
                            class="d-flex flex-row gap-2 align-items-center"
                        >
                            <a
                                v-for="h in state.entity.editors"
                                :key="h.user_id"
                                href="#"
                                @click.prevent="showUserInfo(getUserBy(h.user_id))"
                            >
                                <span 
                                    class="badge bg-opacity-75 pe-3"
                                    :class="{'bg-warning': h.user_id == state.entity.creator && h.user_id != userId(), 'bg-primary': h.user_id != state.entity.creator && h.user_id != userId(), 'bg-success': h.user_id == userId()}"
                                >
                                    {{ getUserBy(h.user_id).name }}
                                </span>
                                <user-avatar
                                    :user="getUserBy(h.user_id)"
                                    :size="20"
                                    class="align-middle ms-n2"
                                />
                            </a>
                        </div>
                        <span v-else>
                            Fetching…
                        </span>
                    </div>
                    <div v-if="state.entity.metadata">
                        <h5 class="mb-1">
                            {{ t('global.licence') }}
                        </h5>
                        <input
                            v-model="state.entityMetadata.licence"
                            type="text"
                            class="form-control"
                            placeholder="CC-BY 4.0, ..."
                        >
                    </div>
                </div>
                <form>
                    <div>
                        <label
                            for="entity-metadata-summary"
                            class="form-label mb-1"
                        >
                            <h5 class="mb-0">
                                {{ t('global.summary') }}
                            </h5>
                        </label>
                        <richtext
                            id="entity-metadata-summary"
                            :ref="el => rtRef = el"
                            :value="state.entityMetadata.summary"
                            @change="updateEntityMetadata"
                        />
                    </div>
                </form>
                <hr>
                <div
                    class="overflow-hidden d-flex flex-column"
                >
                    <h5 class="mb-1">
                        {{ t('main.history.title') }}
                    </h5>
                    <ul
                        v-if="state.entity.history"
                        class="list-group pe-2 overflow-auto"
                    >
                        <li
                            v-for="entry in state.entity.history"
                            :key="`entity-history-entry-${entry.id}`"
                            class="list-group-item d-flex flex-row gap-3 align-items-center"
                        >
                            <span
                                :title="entry.description"
                            >
                                <span
                                    v-if="entry.description == 'created'"
                                >
                                    <i class="fas fa-fw fa-plus text-success" />
                                </span>
                                <span
                                    v-else-if="entry.description == 'updated'"
                                >
                                    <i class="fas fa-fw fa-edit text-warning" />
                                </span>
                            </span>
                            <div class="flex-grow-1">
                                <template
                                    v-if="entry.subject_type == 'App\\Entity'"
                                >
                                    <div
                                        v-if="entry.description == 'created'"
                                    >
                                        <div
                                            class="d-flex flex-row gap-2 align-items-center"
                                        >
                                            <span class="fw-bold">
                                                {{ t('main.history.created_as') }}
                                            </span>
                                            <span class="badge bg-primary bg-opacity-75">{{ entry.properties.attributes.name }}</span>
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
                                                :to="{name: 'entitydetail', params: {id: entry.properties.attributes.root_entity_id}, query: route.query}"
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
                                            <span class="fw-bold">
                                                {{ t('main.history.entity.name_update') }}
                                            </span>
                                            <span class="badge bg-danger bg-opacity-75">{{ entry.properties.old.name }}</span>
                                            <i class="fas fa-fw fa-2xs fa-arrow-right" />
                                            <span class="badge bg-success bg-opacity-75">{{ entry.properties.attributes.name }}</span>
                                        </div>
                                        <div
                                            v-else-if="(entry.properties.old.root_entity_id || entry.properties.old.rank) && entry.properties.attributes.root_entity_id || entry.properties.attributes.rank"
                                            class="d-flex flex-row gap-2 align-items-center"
                                        >
                                            <span
                                                class="fw-bold"
                                            >
                                                {{ t('main.history.entity.moved') }}
                                            </span>
                                            <span
                                                class="badge bg-danger bg-opacity-75"
                                            >
                                                <template
                                                    v-if="entry.properties.old.root_entity_id"
                                                >
                                                    <span
                                                        v-if="getEntity(entry.properties.old.root_entity_id).name"
                                                    >
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
                                                <span
                                                    v-else
                                                >
                                                    {{ t('main.entity.top_level') }}
                                                </span>
                                                |
                                                <span
                                                    :title="t('main.history.entity.rank')"
                                                >
                                                    {{ entry.properties.old.rank }}
                                                </span>
                                            </span>
                                            <i class="fas fa-fw fa-2xs fa-arrow-right" />
                                            <span
                                                class="badge bg-success bg-opacity-75"
                                            >
                                                <template
                                                    v-if="entry.properties.attributes.root_entity_id"
                                                >
                                                    <span
                                                        v-if="getEntity(entry.properties.attributes.root_entity_id).name"
                                                    >
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
                                                <span
                                                    v-else
                                                >
                                                    {{ t('main.entity.top_level') }}
                                                </span>
                                                |
                                                <span
                                                    :title="t('main.history.entity.rank')"
                                                >
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
                                <template
                                    v-else-if="entry.subject_type == 'attribute_values'"
                                >
                                    <div
                                        v-if="entry.description == 'created'"
                                    >
                                        <span
                                            class="d-flex flex-row align-items-center gap-2"
                                        >
                                            <span class="fw-bold">
                                                {{ t('main.history.entity.value_add_attribute') }}
                                            </span>
                                            <span>
                                                {{ getAttributeName(entry.attribute.id) }}
                                            </span>
                                            <a
                                                href="#"
                                                class="text-reset"
                                                @click.prevent="state.showHistoryChange[entry.id] = !state.showHistoryChange[entry.id]"
                                            >
                                                <span v-show="state.showHistoryChange[entry.id]">
                                                    <i class="fas fa-fw fa-eye" />
                                                </span>
                                                <span v-show="!state.showHistoryChange[entry.id]">
                                                    <i class="fas fa-fw fa-eye-slash" />
                                                </span>
                                            </a>
                                        </span>
                                        <attribute-list
                                            v-show="state.showHistoryChange[entry.id]"
                                            :group="{name: 'entity-history-created', pull: false, put: false}"
                                            :classes="'mx-0 py-2 px-2 rounded-3 bg-primary bg-opacity-50'"
                                            :attributes="formatHistoryEntryAttributes(entry.attribute)"
                                            :values="formatHistoryEntryValue(entry.attribute.id, entry.value_after)"
                                            :options="{'hide_labels': true, 'item_classes': 'px-0'}"
                                            :selections="{}"
                                            :preview="true"
                                        />
                                    </div>
                                    <div
                                        v-else-if="entry.description == 'updated'"
                                    >
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
                                                    @click.prevent="state.showHistoryChange[entry.id] = !state.showHistoryChange[entry.id]"
                                                >
                                                    <span v-show="state.showHistoryChange[entry.id]">
                                                        <i class="fas fa-fw fa-eye" />
                                                    </span>
                                                    <span v-show="!state.showHistoryChange[entry.id]">
                                                        <i class="fas fa-fw fa-eye-slash" />
                                                    </span>
                                                </a>
                                            </span>
                                            <div
                                                v-if="state.showHistoryChange[entry.id]"
                                                class="d-flex flex-row gap-2 align-items-center"
                                            >
                                                <attribute-list
                                                    v-if="hasHistoryEntryKey(entry.properties.old, '!certainty')"
                                                    :group="{name: 'entity-history-changed-from', pull: false, put: false}"
                                                    :classes="'flex-grow-1 mx-0 py-2 px-2 rounded-3 bg-danger bg-opacity-50'"
                                                    :attributes="formatHistoryEntryAttributes(entry.attribute)"
                                                    :values="formatHistoryEntryValue(entry.attribute.id, entry.value_before)"
                                                    :options="{'hide_labels': true, 'item_classes': 'px-0'}"
                                                    :selections="{}"
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
                                                    :group="{name: 'entity-history-changed-to', pull: false, put: false}"
                                                    :classes="'flex-grow-1 mx-0 py-2 px-2 rounded-3 bg-success bg-opacity-50'"
                                                    :attributes="formatHistoryEntryAttributes(entry.attribute)"
                                                    :values="formatHistoryEntryValue(entry.attribute.id, entry.value_after)"
                                                    :options="{'hide_labels': true, 'item_classes': 'px-0'}"
                                                    :selections="{}"
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
                                        </template>
                                        <div
                                            v-else
                                            class="d-flex flex-row gap-2 align-items-center"
                                        >
                                            <span class="fw-bold">
                                                {{ t('main.history.entity.certainty_update') }}
                                            </span>
                                            <span>
                                                {{ getAttributeName(entry.attribute.id) }}
                                            </span>
                                            <div
                                                class="d-flex flex-row align-items-center gap-1"
                                            >
                                                <span class="badge bg-danger bg-opacity-75">
                                                    {{ entry.properties.old.certainty || t('main.history.entity.certainty_unknown') }}
                                                </span>
                                                <i class="fas fa-fw fa-xs fa-arrow-right" />
                                                <span class="badge bg-success bg-opacity-75">
                                                    {{ entry.properties.attributes.certainty || t('main.history.entity.certainty_unknown') }}
                                                </span>
                                            </div>
                                        </div>
                                    </div>
                                </template>
                            </div>
                            <div class="text-nowrap">
                                <span 
                                    class="badge bg-opacity-75 pe-3"
                                    :class="{'bg-warning': entry.user_id == state.entity.creator && entry.user_id != userId(), 'bg-primary': entry.user_id != state.entity.creator && entry.user_id != userId(), 'bg-success': entry.user_id == userId()}"
                                >
                                    {{ getUserBy(entry.user_id).name }}
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
                        </li>
                    </ul>
                    <alert
                        v-else
                        :type="'info'"
                        :message="'Fetching history data…'"
                    />
                </div>
            </div>
            <div
                v-show="can('comments_read')"
                id="active-entity-comments-panel"
                class="tab-pane fade h-100 active-entity-detail-panel d-flex flex-column"
                role="tabpanel"
            >
                <div
                    v-if="state.entity.comments"
                    class="mb-auto scroll-y-auto h-100"
                >
                    <div
                        v-if="state.commentsFetching"
                        class="mt-2"
                    >
                        <!-- eslint-disable vue/no-v-html -->
                        <p
                            class="alert alert-info mb-0"
                            v-html="t('global.comments.fetching')"
                        />
                        <!-- eslint-enable vue/no-v-html -->
                    </div>
                    <div
                        v-else-if="state.commentFetchFailed"
                        class="mt-2"
                    >
                        <p class="alert alert-danger mb-0">
                            {{ t('global.comments.fetching_failed') }}
                            <button
                                type="button"
                                class="d-block mt-2 btn btn-sm btn-outline-success"
                                @click="fetchComments"
                            >
                                <i class="fas fa-fw fa-sync" />
                                {{ t('global.comments.retry_failed') }}
                            </button>
                        </p>
                    </div>
                    <comment-list
                        v-else
                        :avatar="48"
                        :comments="state.entity.comments"
                        :hide-button="false"
                        :resource="state.resourceInfo"
                        @added="addComment"
                    />
                </div>
            </div>
        </div>
        <router-view
            v-if="state.attributesFetched"
            :entity="state.entity"
        />
    </div>
</template>

<script>
import {
    computed,
    nextTick,
    onBeforeUpdate,
    onMounted,
    reactive,
    ref,
    watch,
} from 'vue';

import {
    useRoute,
    onBeforeRouteLeave,
    onBeforeRouteUpdate,
} from 'vue-router';

import { useI18n } from 'vue-i18n';

import {
    Popover,
} from 'bootstrap';

import store from '@/bootstrap/store.js';
import router from '@/bootstrap/router.js';

import { useToast } from '@/plugins/toast.js';

    import { ago, date } from '@/helpers/filters.js';
    import {
        fetchEntityHistoryMetadata,
        getEntityComments,
        patchAttributes,
        patchEntityName,
        patchEntityMetadata,
    } from '@/api.js';
    import {
        can,
        isModerated,
        isArray,
        userId,
        getAttribute,
        getAttributeName,
        getUserBy,
        getEntity,
        getEntityTypeAttributeSelections,
        getEntityTypeDependencies,
        translateConcept,
        _cloneDeep,
    } from '@/helpers/helpers.js';
    import {
        showDiscard,
        showDeleteEntity,
        showUserInfo,
        canShowReferenceModal,
    } from '@/helpers/modal.js';

export default {
    props: {
        bibliography: {
            required: false,
            type: Array,
            default: () => []
        },
        onDelete: {
            required: false,
            type: Function,
            default: () => { }
        }
    },
    setup(props) {
        const { t } = useI18n();
        const route = useRoute();
        const toast = useToast();

        // FETCH
        store.dispatch('getEntity', route.params.id).then(_ => {
            getEntityTypeAttributeSelections();
            state.initFinished = true;
            updateAllDependencies();
        });

        // DATA
        const attrRefs = ref({});
        const state = reactive({
            formDirty: computed(_ => {
                for(let k in state.dirtyStates) {
                    if(state.dirtyStates[k]) return true;
                }
                return false;
            }),
            dirtyStates: {},
            attributeGrpHovered: null,
            hiddenAttributes: {},
            entityHeaderHovered: false,
            editedEntityName: '',
            entityMetadata: {},
            showHistoryChange: {},
            initFinished: false,
            commentLoadingState: 'not',
            metadataTabLoaded: false,
            hiddenAttributeState: false,
            attributesInTabs: true,
            routeQuery: computed(_ => route.query),
            entity: computed(_ => store.getters.entity),
            entityUser: computed(_ => state.entity.user),
            entityAttributes: computed(_ => store.getters.entityTypeAttributes(state.entity.entity_type_id)),
            entityGroups: computed(_ => {
                if(!state.entityAttributes) {
                    return state.entityAttributes;
                }

                if(state.attributesInTabs) {
                    const tabGroups = {};
                    let currentGroup = 'default';
                    let currentGroupId = 'default';
                    let currentUnnamedGroupCntr = 1;
                    state.entityAttributes.forEach(a => {
                        if(a.is_system && a.datatype == 'system-separator') {
                            if(!a.pivot.metadata || !a.pivot.metadata.title) {
                                currentGroup = t(`main.entity.tabs.untitled_group`, { cnt: currentUnnamedGroupCntr });
                                currentUnnamedGroupCntr++;
                            } else {
                                currentGroup = translateConcept(a.pivot.metadata.title);
                            }
                            currentGroupId = a.pivot.id;
                            return;
                        }
                        if(!tabGroups[currentGroup]) {
                            tabGroups[currentGroup] = {
                                id: currentGroupId,
                                data: []
                            };
                        }
                        tabGroups[currentGroup].data.push(a);
                    });

                    return tabGroups;
                } else {
                    return {
                        default: {
                            id: 'default',
                            data: state.entityAttributes,
                        },
                    };
                }
            }),
            entityTypeSelections: computed(_ => getEntityTypeAttributeSelections(state.entity.entity_type_id)),
            entityTypeDependencies: computed(_ => getEntityTypeDependencies(state.entity.entity_type_id)),
            hasAttributeLinks: computed(_ => state.entity.attributeLinks && state.entity.attributeLinks.length > 0),
            groupedAttributeLinks: computed(_ => {
                if(!state.hasAttributeLinks) return {};

                const groups = {};
                state.entity.attributeLinks.forEach(l => {
                    if(!groups[l.id]) {
                        groups[l.id] = {
                            ...l,
                            attribute_urls: [translateConcept(l.attribute_url)],
                        };
                    } else {
                        groups[l.id].attribute_urls.push(translateConcept(l.attribute_url));
                    }
                });
                return groups;
            }),
            attributesFetched: computed(_ => state.initFinished && state.entity.data && !!state.entityAttributes && state.entityAttributes.length > 0),
            hiddenAttributeList: computed(_ => {
                const keys = Object.keys(state.hiddenAttributes);
                const values = Object.values(state.hiddenAttributes);
                const list = [];
                for(let i = 0; i < keys.length; i++) {
                    if(values[i].hide && (!state.hiddenAttributes[values[i].by] || !state.hiddenAttributes[values[i].by].hide)) {
                        list.push(keys[i]);
                    }
                }
                return list;
            }),
            hiddenAttributeCount: computed(_ => state.hiddenAttributeList.length),
            hiddenAttributeListing: computed(_ => {
                let listing = `<div>`;
                if(!!state.attributesFetched) {
                    const keys = Object.keys(state.hiddenAttributes);
                    const values = Object.values(state.hiddenAttributes);
                    const listGroups = {};
                    for(let i = 0; i < keys.length; i++) {
                        const k = keys[i];
                        const v = values[i];
                        if(v.hide && (!state.hiddenAttributes[v.by] || !state.hiddenAttributes[v.by].hide)) {
                            if(!listGroups[v.by]) {
                                listGroups[v.by] = [];
                            }
                            listGroups[v.by].push(k);
                        }
                    }
                    for(let k in listGroups) {
                        const grpAttr = getAttribute(k);
                        listing += `<span class="text-muted fw-light fs-6"># ${translateConcept(grpAttr.thesaurus_url)}</span>`;
                        listing += `<ol class="mb-0">`;
                        // const data = state.entity.data[keys[i]];
                        for(let i = 0; i < listGroups[k].length; i++) {
                            const attr = getAttribute(listGroups[k][i]);
                            listing += `<li><span class="fw-bold">${translateConcept(attr.thesaurus_url)}</span></li>`;
                        }
                        listing += `</ol>`;
                    }
                }
                listing += `</div>`;
                return listing;
            }),
            resourceInfo: computed(_ => {
                if(!state.entity) return {};

                return {
                    id: state.entity.id,
                    type: 'entity',
                };
            }),
            showBreadcrumb: computed(_ => {
                return state.entity.parentIds && state.entity.parentIds.length > 1;
            }),
            lastModified: computed(_ => {
                return state.entity.updated_at || state.entity.created_at;
            }),
            commentsFetching: computed(_ => {
                return state.commentLoadingState === 'fetching';
            }),
            commentsFetched: computed(_ => {
                return state.commentLoadingState === 'fetched';
            }),
            commentFetchFailed: computed(_ => {
                return state.commentLoadingState === 'failed';
            }),
        });

        // FUNCTIONS
        const fetchMetadataTabData = _ => {
            if(state.metadataTabLoaded) return;

            fetchEntityHistoryMetadata(state.entity.id).then(data => {
                state.metadataTabLoaded = true;
                store.dispatch('updateEntityHistoryMetadata', {
                    eid: state.entity.id,
                    data: data,
                });
            });
        };
        const hasReferenceGroup = group => {
            if(!state.entity.references) return false;
            if(!Object.keys(state.entity.references).length) return false;
            if(!state.entity.references[group]) return false;
            return Object.keys(state.entity.references[group]).length > 0;
        };
        const showMetadata = e => {
            const attribute = e.element;
            const canOpen = canShowReferenceModal(attribute.id);
            if(canOpen) {
                router.push({
                    append: true,
                    name: 'entityrefs',
                    query: route.query,
                    params: {
                        aid: attribute.id,
                    },
                });
            } else {
                const msg = t('main.entity.references.toasts.cannot_edit_metadata.msg');
                toast.$toast(msg, '', {
                    duration: 2500,
                    autohide: true,
                    channel: 'warning',
                    icon: true,
                    simple: true,
                });
            }
        };
        const editEntityName = _ => {
            if(!can('entity_write')) return;

            state.editedEntityName = state.entity.name;
            state.entity.editing = true;
        };
        const updateEntityName = _ => {
            // If name does not change, just cancel
            if(state.entity.name == state.editedEntityName) {
                cancelUpdateEntityName();
            } else {
                patchEntityName(state.entity.id, state.editedEntityName).then(data => {
                    store.dispatch('updateEntity', {
                        ...data,
                        name: state.editedEntityName,
                    });
                    cancelEditEntityName();
                });
            }
        };
        const cancelEditEntityName = _ => {
            state.entity.editing = false;
            state.editedEntityName = '';
        };
        const updateDependencyState = (aid, value) => {
            const attrDeps = state.entityTypeDependencies[aid];
            if(!attrDeps) return;
            const type = getAttribute(aid).datatype;
            attrDeps.forEach(ad => {
                let matches = false;
                switch(ad.operator) {
                    case '=':
                        if(type == 'string-sc') {
                            matches = value.id == ad.value;
                        } else if(type == 'string-mc') {
                            matches = value && value.some(mc => mc.id == ad.value);
                        } else {
                            matches = value == ad.value;
                        }
                        break;
                    case '!=':
                        if(type == 'string-sc') {
                            matches = value.id != ad.value;
                        } else if(type == 'string-mc') {
                            matches = value && value.every(mc => mc.id != ad.value);
                        } else {
                            matches = value != ad.value;
                        }
                        break;
                    case '<':
                        matches = value < ad.value;
                        break;
                    case '>':
                        matches = value > ad.value;
                        break;
                }
                state.hiddenAttributes[ad.dependant] = {
                    hide: matches,
                    by: aid,
                };
            });
        };
        const updateAllDependencies = _ => {
            if(!state.entityAttributes) return;

            for(let i = 0; i < state.entityAttributes.length; i++) {
                const curr = state.entityAttributes[i];
                updateDependencyState(curr.id, state.entity.data[curr.id].value);
            }
        };
        const showHiddenAttributes = _ => {
            state.hiddenAttributeState = true;
        };
        const hideHiddenAttributes = _ => {
            state.hiddenAttributeState = false;
        };
        const confirmDeleteEntity = _ => {
            if(!can('entity_delete')) return;

            showDeleteEntity(state.entity.id);
        };
        const setDetailPanel = tab => {
            const query = {
                view: tab,
            };
            router.push({
                query: {
                    ...route.query,
                    ...query,
                }
            });
        };
        const setDetailPanelView = (tab = 'attributes-default') => {
            const tabId = tab.substring(tab.indexOf('-') + 1);
            let newTab, oldTabs, newPanel, oldPanels;
            if(tab === 'comments') {
                newTab = document.getElementById('active-entity-comments-tab');
                newPanel = document.getElementById('active-entity-comments-panel');
                if(!state.commentsFetched) {
                    fetchComments();
                }
            } else if(tab === 'metadata') {
                newTab = document.getElementById('active-entity-metadata-tab');
                newPanel = document.getElementById('active-entity-metadata-panel');
                fetchMetadataTabData();
            } else {
                newTab = document.getElementById(`active-entity-attributes-group-${tabId}-tab`);
                newPanel = document.getElementById(`active-entity-attributes-panel-${tabId}`);
            }
            oldTabs = document.getElementsByClassName('active-entity-detail-tab');
            oldPanels = document.getElementsByClassName('active-entity-detail-panel');

            oldTabs.forEach(t => t.classList.remove('active'));
            if(newTab) newTab.classList.add('active');
            oldPanels.forEach(p => p.classList.remove('show', 'active'));
            if(newPanel) newPanel.classList.add('show', 'active');
        };
        const onEntityHeaderHover = hoverState => {
            state.entityHeaderHovered = hoverState;
        };
        const updateEntityMetadata = e => {
            state.entityMetadata.summary = e.value;
        };
        const hasHistoryEntryKey = (entry, key) => {
            // if starts with !, func checks if there is any other key than the one provided
            if(key.startsWith('!')) {
                const searchKey = key.substr(1);
                const keys = Object.keys(entry);
                return !keys.includes(searchKey);
            }

            return !!entry[key];
        };
        const formatHistoryEntryValue = (id, val) => {
            return {
                [id]: {
                    value: val,
                },
            };
        };
        const formatHistoryEntryAttributes = attr => {
            attr.isDisabled = true;
            return [
                attr
            ];
        };
        const showTabActions = (grp, status) => {
            state.attributeGrpHovered = status ? grp : null;
        };
        const setFormState = (e, grp) => {
            state.dirtyStates[grp] = e.dirty && e.valid;
            updateDependencyState(e.attribute_id, e.value);
        };
        const getDirtyValues = grp => {
            const list = grp ? grp.split(',') : Object.keys(attrRefs.value);
            let values = {};
            list.forEach(g => {
                values = {
                    ...values,
                    ...attrRefs.value[g].getDirtyValues(),
                };
            });
            return values;
        };
        const undirtyList = grp => {
            const list = grp ? grp.split(',') : Object.keys(attrRefs.value);
            list.forEach(g => {
                attrRefs.value[g].undirtyList();
            });
        };
        const resetListValues = grp => {
            const list = grp ? grp.split(',') : Object.keys(attrRefs.value);
            list.forEach(g => {
                attrRefs.value[g].resetListValues();
            });
        };
        const resetDirtyStates = grp => {
            const list = grp ? grp.split(',') : Object.keys(attrRefs.value);
            list.forEach(g => {
                state.dirtyStates[g] = false;
            });
        };
        const fetchComments = _ => {
            if(!can('comments_read')) return;

                state.commentLoadingState = 'fetching';
                getEntityComments(state.entity.id).then(comments => {
                    store.dispatch('setEntityComments', comments);
                    state.commentLoadingState = 'fetched';
                }).catch(e => {
                    state.commentLoadingState = 'failed';
                });
            };
            const addComment = event => {
                const comment = event.comment;
                const replyTo = event.replyTo;
                if(replyTo) {
                    const op = state.entity.comments.find(c => c.id == replyTo);
                    if(op.replies) {
                        op.replies.push(comment);
                    }
                    op.replies_count++;
                } else {
                    if(!state.entity.comments) {
                        state.entity.comments = [];
                    }
                    state.entity.comments.push(comment);
                    state.entity.comments_count++;
                }
            };
            const saveMetadata = _ => {
                const metadata = {};
                for(let k in state.entityMetadata) {
                    const upd = state.entityMetadata[k];
                    const curr = state.entity.metadata[k];
                    if(!curr || upd != curr) {
                        metadata[k] = upd;
                    }
                }
                patchEntityMetadata(state.entity.id, metadata).then(data => {
                    store.dispatch('updateEntityMetadata', {
                        eid: state.entity.id,
                        data: data,
                    });

                    toast.$toast(
                        t('main.entity.toasts.updated_metadata.msg', {
                            name: data.name
                        }),
                        t('main.entity.toasts.updated_metadata.title'), {
                        channel: 'success',
                        autohide: true,
                        icon: true,
                    });
                })
            };
            const saveEntity = grps => {
                if(!can('entity_data_write')) return;

                const dirtyValues = getDirtyValues(grps);
                const patches = [];
                const moderations = [];

                for(let v in dirtyValues) {
                    const aid = v;
                    const data = state.entity.data[aid];
                    const patch = {
                        op: null,
                        value: null,
                        params: {
                            aid: aid,
                        },
                    };
                    if(data.id) {
                        // if data.id exists, there has been an entry in the database, therefore it is a replace/remove operation
                        if(dirtyValues[v] && dirtyValues[v] != '') {
                            // value is set, therefore it is a replace
                            patch.op = 'replace';
                            patch.value = dirtyValues[v];
                            // patch.value = getCleanValue(patch.value, entity.attributes);
                        } else {
                            // value is empty, therefore it is a remove
                            patch.op = 'remove';
                        }
                    } else {
                        // there has been no entry in the database before, therefore it is an add operation
                        if(dirtyValues[v] && dirtyValues[v] != '') {
                            patch.op = 'add';
                            patch.value = dirtyValues[v];
                            // patch.value = getCleanValue(patch.value, entity.attributes);
                        } else {
                            // there has be no entry in the database before and values are not different (should not happen ;))
                            continue;
                        }
                    }
                    patches.push(patch);
                    moderations.push(aid);
                }
                return patchAttributes(state.entity.id, patches).then(data => {
                    undirtyList(grps);
                    store.dispatch('updateEntity', data);
                    store.dispatch('updateEntityData', {
                        data: dirtyValues,
                        eid: state.entity.id,
                    });
                    if(isModerated()) {
                        store.dispatch('updateEntityDataModerations', {
                            entity_id: state.entity.id,
                            attribute_ids: moderations,
                            state: 'pending',
                        });
                    }

                toast.$toast(
                    t('main.entity.toasts.updated.msg', {
                        name: data.name
                    }),
                    t('main.entity.toasts.updated.title'), {
                    channel: 'success',
                    autohide: true,
                    icon: true,
                });
            }).catch(error => {
                const r = error.response;
                toast.$toast(
                    r.data.error,
                    `${r.status}: ${r.statusText}`, {
                    channel: 'error',
                    autohide: true,
                    icon: true,
                    duration: 5000,
                },
                );
            });
        };
        const resetForm = grps => {
            resetListValues(grps);
            resetDirtyStates(grps);
        };
        const setAttrRefs = (el, grp) => {
            attrRefs.value[grp] = el;
        }

        // ON MOUNTED
        onMounted(_ => {
            console.log('entity detail component mounted');
            let hiddenAttrElem = document.getElementById('hidden-attributes-icon');
            if(!!hiddenAttrElem) {
                new Popover(hiddenAttrElem, {
                    title: _ => t('main.entity.attributes.hidden', { cnt: state.hiddenAttributeCount }, state.hiddenAttributeCount),
                    content: state.hiddenAttributeListing,
                });
            }
        });
        onBeforeUpdate(_ => {
            attrRefs.value = {};
            state.commentLoadingState = 'not';
        });

        watch(_ => state.hiddenAttributeCount,
            async (newCount, oldCount) => {
                if(newCount > 0) {
                    let hiddenAttrElem = document.getElementById('hidden-attributes-icon');
                    if(!!hiddenAttrElem) {
                        new Popover(hiddenAttrElem, {
                            title: _ => t('main.entity.attributes.hidden', { cnt: state.hiddenAttributeCount }, state.hiddenAttributeCount),
                            content: state.hiddenAttributeListing,
                        });
                    }
                }
            }
        );

        watch(_ => route.params,
            async (newParams, oldParams) => {
                if(newParams.id == oldParams.id) return;
                if(!newParams.id) return;
                state.initFinished = false;
                store.dispatch('getEntity', newParams.id).then(_ => {
                    getEntityTypeAttributeSelections();
                    state.initFinished = true;
                    updateAllDependencies();
                });
            }
        );

        watch(_ => state.entity,
            async (newValue, oldValue) => {
                if(!newValue || !newValue.id) return;

                state.entityMetadata = _cloneDeep(state.entity.metadata);
                if(isArray(state.entityMetadata)) {
                    state.entityMetadata = {};
                }

                nextTick(_ => {
                    setDetailPanelView(route.query.view);

                    const currUrlParam = route.query?.view;
                    if(currUrlParam == 'metadata') {
                        fetchMetadataTabData();
                    }
                });
            }
        );

        watch(_ => route.query.view,
            async (newValue, oldValue) => {
                if(route.name != 'entitydetail') return;
                if(newValue == oldValue) return;

                nextTick(_ => {
                    setDetailPanelView(newValue);
                });
            }
        );

        // ON BEFORE LEAVE
        onBeforeRouteLeave(async (to, from) => {
            if(state.formDirty) {
                showDiscard(to, resetDirtyStates, saveEntity);
                return false;
            } else {
                store.dispatch('resetEntity');
                return true;
            }
        });
        onBeforeRouteUpdate(async (to, from) => {
            if(to.params.id !== route.params.id) {
                if(state.formDirty) {
                    showDiscard(to, resetDirtyStates, saveEntity);
                    return false;
                } else {
                    state.hiddenAttributes = {};
                    state.metadataTabLoaded = false;
                    // store.dispatch('resetEntity');
                    return true;
                }
            } else {
                // if not id changed, but query, we do not need discard modal
                return true;
            }
        });

        // RETURN
        return {
            t,
            route,
            // HELPERS
            can,
            ago,
            date,
            userId,
            getUserBy,
            showUserInfo,
            getAttributeName,
            getEntity,
            translateConcept,
            // LOCAL
            hasReferenceGroup,
            showMetadata,
            editEntityName,
            updateEntityName,
            cancelEditEntityName,
            showHiddenAttributes,
            hideHiddenAttributes,
            confirmDeleteEntity,
            setDetailPanel,
            onEntityHeaderHover,
            updateEntityMetadata,
            hasHistoryEntryKey,
            formatHistoryEntryValue,
            formatHistoryEntryAttributes,
            showTabActions,
            setFormState,
            addComment,
            saveMetadata,
            saveEntity,
            resetForm,
            setAttrRefs,
            // STATE
            attrRefs,
            state,
        };
    }
}
</script>
