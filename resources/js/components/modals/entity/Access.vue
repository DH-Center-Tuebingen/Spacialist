<template>
    <vue-final-modal
        class="modal-container modal"
        content-class="sp-modal-content sp-modal-content-sm"
        name="entity-access-modal"
    >
        <div class="sp-modal-content sp-modal-content-sm">
            <div class="modal-header">
                <h5 class="modal-title">
                    {{ t('main.entity.modals.access.title') }}
                </h5>
                <button
                    type="button"
                    class="btn-close"
                    aria-label="Close"
                    data-bs-dismiss="modal"
                    @click="closeModal()"
                />
            </div>
            <div class="modal-body nonscrollable">
                <div
                    class="d-flex flex-column bg-secondary bg-opacity-10 rounded-4 px-3 py-3 position-relative text-body"
                >
                    <div class="d-flex flex-row justify-content-between">
                        <div
                            class="d-flex flex-column align-items-center gap-2"
                        >
                            <label
                                class="form-check-label d-flex flex-column justify-content-center align-items-center gap-1"
                                for="group-restrict-type-private"
                            >
                                <i class="fas fa-fw fa-user-shield" />
                                <span>
                                    Restricted Access
                                    <span
                                        class="text-primary"
                                        title="You can define which working groups or individual users can access this data"
                                    >
                                        <i class="fas fa-fw fa-info-circle" />
                                    </span>
                                </span>
                            </label>
                            <input
                                id="group-restrict-type-private"
                                v-model="state.accessType"
                                class="form-check-input m-0"
                                type="radio"
                                name="group-restriction"
                                value="restricted"
                            >
                        </div>
                        <div
                            class="d-flex flex-column align-items-center gap-2"
                        >
                            <label
                                class="form-check-label d-flex flex-column justify-content-center align-items-center gap-1"
                                for="group-restrict-type-selection"
                            >
                                <i class="fas fa-fw fa-users" />
                                <span>
                                    Default Access
                                    <span
                                        class="text-primary"
                                        title="This is the default. Everybody cann access this data according to their given roles and permissions"
                                    >
                                        <i class="fas fa-fw fa-info-circle" />
                                    </span>
                                </span>
                            </label>
                            <input
                                id="group-restrict-type-selection"
                                v-model="state.accessType"
                                class="form-check-input m-0"
                                type="radio"
                                name="group-restriction"
                                value="users"
                            >
                        </div>
                        <div
                            class="d-flex flex-column align-items-center gap-2"
                        >
                            <label
                                class="form-check-label d-flex flex-column justify-content-center align-items-center gap-1"
                                for="group-restrict-type-open"
                            >
                                <i class="fas fa-fw fa-unlock-alt" />
                                <span>
                                    Public Access
                                    <span
                                        class="text-primary"
                                        title="When set to public access, everybody can see this data in the analysis or open access page. Editing is still limited to registered users."
                                    >
                                        <i class="fas fa-fw fa-info-circle" />
                                    </span>
                                </span>
                            </label>
                            <input
                                id="group-restrict-type-open"
                                v-model="state.accessType"
                                class="form-check-input m-0"
                                type="radio"
                                name="group-restriction"
                                value="open"
                            >
                        </div>
                    </div>
                </div>
                <div
                    v-if="state.isRestricted"
                    class="mt-2"
                >
                    <hr>
                    <h5>Access Rules</h5>
                    <div class="mx-3">
                        <div class="row fw-bold">
                            <div class="col-7">
                                User/Working Group
                            </div>
                            <div class="col-3 text-center">
                                Read / Write
                            </div>
                            <div class="col-2 text-end">
                                Remove
                            </div>
                        </div>
                    </div>
                    <ul class="list-group list-group-flush">
                        <li
                            v-for="(item, i) in state.accessRules"
                            :key="`entity-access-rule-${i}`"
                            class="list-group-item"
                        >
                            <div class="row">
                                <group-user-item
                                    class="col-7"
                                    :item="item"
                                />
                                <div class="col-3 text-center d-flex flex-row gap-2 align-items-center text-muted justify-content-center">
                                    <i class="fas fa-fw fa-eye" />
                                    <input
                                        type="radio"
                                        class="form-check-input"
                                        :name="`access-rule-radio-${i}`"
                                        value="read"
                                    >
                                    /
                                    <input
                                        type="radio"
                                        class="form-check-input"
                                        :name="`access-rule-radio-${i}`"
                                        value="write"
                                    >
                                    <i class="fas fa-fw fa-edit" />
                                </div>
                                <a
                                    href="#"
                                    class="text-reset col-2 text-end"
                                    @click.prevent="removeAccessRule(i)"
                                >
                                    <i class="fas fa-fw fa-times" />
                                </a>
                            </div>
                        </li>
                    </ul>

                    <h6 class="mt-3">Add another working group/user</h6>
                    <simple-search
                        :endpoint="searchGroupsAndUsers"
                        :key-fn="_ => {}"
                        :default-value="state.resetValue"
                        @selected="e => addAccessRule(e)"
                    >
                        <template #resultsc="{ value }">
                            <group-user-item :item="value" />
                        </template>
                        <template #optionsc="{ value }">
                            <group-user-item :item="value" />
                        </template>
                    </simple-search>
                </div>
            </div>
            <div class="modal-footer">
                <button
                    type="submit"
                    class="btn btn-outline-success"
                    @click="setAccess()"
                >
                    <i class="fas fa-fw fa-save" /> {{ t('global.save') }}
                </button>
                <button
                    type="button"
                    class="btn btn-outline-secondary"
                    data-bs-dismiss="modal"
                    @click="closeModal()"
                >
                    <i class="fas fa-fw fa-times" /> {{ t('global.cancel') }}
                </button>
            </div>
        </div>
    </vue-final-modal>
</template>

<script>
    import {
        computed,
        reactive,
        toRefs,
    } from 'vue';
    import { useI18n } from 'vue-i18n';

    import {
        searchGroupsAndUsers,
    } from '@/api.js';

    import {
        getTs,
    } from '@/helpers/helpers.js';

    import GroupUserItem from '@/components/user/GroupUserListItem.vue';

    export default {
        components: {
            'group-user-item': GroupUserItem,
        },
        props: {
            entityId: {
                required: true,
                type: Number,
            },
        },
        emits: ['closing', 'confirm'],
        setup(props, context) {
            const { t } = useI18n();
            const {
                entityId,
            } = toRefs(props);

            // FUNCTIONS
            const addAccessRule = e => {
                if(e.removed) return;
                state.accessRules.push(e);
                state.resetValue = {
                    reset: true,
                    ts: getTs(),
                };
            };
            const removeAccessRule = idx => {
                state.accessRules.splice(idx, 1);
            }
            const setAccess = _ => {
                context.emit('confirm', true);
            };
            const closeModal = _ => {
                context.emit('closing', false);
            };

            // DATA
            const state = reactive({
                resetValue: null,
                accessType: 'users',
                isRestricted: computed(_ => state.accessType == 'restricted'),
                accessRules: [],
            });

            // RETURN
            return {
                t,
                // HELPERS
                searchGroupsAndUsers,
                // LOCAL
                addAccessRule,
                removeAccessRule,
                setAccess,
                closeModal,
                // STATE
                state,
            }
        },
    }
</script>