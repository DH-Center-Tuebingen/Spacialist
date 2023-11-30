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
            <div class="modal-body">
                <div
                    class="d-flex flex-column bg-secondary bg-opacity-10 rounded-4 px-3 py-3 position-relative text-body"
                >
                    <div class="position-relative m-4">
                        <div
                            class="progress"
                            role="progressbar"
                            aria-label="Progress"
                            :aria-valuenow="state.accessTypeValue"
                            aria-valuemin="0"
                            aria-valuemax="100"
                            style="height: 3px; transform: translate(0, -1px);"
                        >
                            <div
                                class="progress-bar"
                                :style="`width: ${state.accessTypeValue}%`"
                            />
                        </div>
                        <button
                            type="button"
                            class="position-absolute top-0 start-0 translate-middle btn btn-sm rounded-pill"
                            :class="btnStateClasses('restricted')"
                            style="width: 2rem; height:2rem;"
                            @click="state.accessType = 'restricted'"
                        >
                            <i class="fas fa-fw fa-user-shield fa-sm" />
                        </button>
                        <button
                            type="button"
                            class="position-absolute top-0 start-50 translate-middle btn btn-sm rounded-pill"
                            :class="btnStateClasses('users')"
                            style="width: 2rem; height:2rem;"
                            @click="state.accessType = 'users'"
                        >
                            <i class="fas fa-users fa-sm" />
                        </button>
                        <button
                            type="button"
                            class="position-absolute top-0 start-100 translate-middle btn btn-sm rounded-pill"
                            :class="btnStateClasses('open')"
                            style="width: 2rem; height:2rem;"
                            @click="state.accessType = 'open'"
                        >
                            <i class="fas fa-unlock-alt fa-sm" />
                        </button>
                    </div>
                    <div
                        class="d-flex flex-column bg-info bg-opacity-25 rounded-3 px-3 py-2 mt-3 text-center"
                    >
                        <template v-if="state.accessType == 'restricted'">
                            <h5 class="mb-0">
                                Restricted Access
                            </h5>
                            <hr class="my-2 mx-5">
                            <p class="mb-0">
                                This settings gives you the possibilities to define which users and/or groups can have access to this data at all. Furthermore, you can decide whether users can have only read access or are allowed to modify and add data.
                            </p>
                        </template>
                        <template v-else-if="state.accessType == 'users'">
                            <h5 class="mb-0">
                                User Access (Default)
                            </h5>
                            <hr class="my-2 mx-5">
                            <p class="mb-0">
                                This is the default setting. All registered users can view and/or modify data based on their individual roles.
                            </p>
                        </template>
                        <template v-else-if="state.accessType == 'open'">
                            <h5 class="mb-0">
                                Public/Open Access
                            </h5>
                            <hr class="my-2 mx-5">
                            <p class="mb-0">
                                This gives the public read access to this data through the <span class="fst-italic">Open Access</span> feature. To allow the public to modify data, they still need a user account, so changes are always related to a specific user. 
                            </p>
                        </template>
                    </div>
                    <div class="mt-3">
                        <span class="fw-bold">Note</span>: <span class="fst-italic">data</span> in this context always refers to this entity and all it's sub-entities!
                    </div>
                </div>
                <div
                    v-if="state.isRestricted"
                    class="mt-2"
                >
                    <hr>
                    <h5>Access Rules</h5>
                    <template v-if="state.accessRules.length > 0">
                        <table class="table table-striped table-hover">
                            <thead>
                                <tr>
                                    <th>
                                        User/Working Group
                                    </th>
                                    <th>
                                        <span title="Read-only">
                                            <i class="fas fa-fw fa-eye" />
                                        </span>
                                        /
                                        <span title="Role-based">
                                            <i class="fas fa-fw fa-shield-alt" />
                                        </span>
                                        /
                                        <span title="Define matrix">
                                            <i class="fas fa-fw fa-list-check" />
                                        </span>
                                    </th>
                                    <th class="text-end">
                                        Remove
                                    </th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr
                                    v-for="(item, i) in state.accessRules"
                                    :key="`entity-access-rule-${i}`"
                                >
                                    <td class="mw-50">
                                        <group-user-item :item="item" />
                                    </td>
                                    <td class="d-flex flex-column">
                                        <div class="d-flex flex-row gap-2 align-items-center text-muted">
                                            <input
                                                v-model="item.access_type"
                                                type="radio"
                                                class="form-check-input mt-0"
                                                :name="`access-rule-radio-${i}`"
                                                value="read"
                                            >
                                            /
                                            <input
                                                v-model="item.access_type"
                                                type="radio"
                                                class="form-check-input mt-0"
                                                :name="`access-rule-radio-${i}`"
                                                value="role_based"
                                            >
                                            /
                                            <input
                                                v-model="item.access_type"
                                                type="radio"
                                                class="form-check-input mt-0"
                                                :name="`access-rule-radio-${i}`"
                                                value="matrix"
                                                @click="setMatrixType(item)"
                                            >
                                        </div>
                                        <div
                                            v-if="item.access_type == 'matrix'"
                                            class="d-flex flex-row gap-3"
                                        >
                                            <div class="form-check">
                                                <input
                                                    id="access-rule-matrix-write"
                                                    v-model="item.matrix_values.write"
                                                    class="form-check-input"
                                                    type="checkbox"
                                                >
                                                <label
                                                    class="form-check-label"
                                                    for="access-rule-matrix-write"
                                                >
                                                    Write
                                                </label>
                                            </div>
                                            <div class="form-check">
                                                <input
                                                    id="access-rule-matrix-create"
                                                    v-model="item.matrix_values.create"
                                                    class="form-check-input"
                                                    type="checkbox"
                                                >
                                                <label
                                                    class="form-check-label"
                                                    for="access-rule-matrix-create"
                                                >
                                                    Create
                                                </label>
                                            </div>
                                            <div class="form-check">
                                                <input
                                                    id="access-rule-matrix-delete"
                                                    v-model="item.matrix_values.delete"
                                                    class="form-check-input"
                                                    type="checkbox"
                                                >
                                                <label
                                                    class="form-check-label"
                                                    for="access-rule-matrix-delete"
                                                >
                                                    Delete
                                                </label>
                                            </div>
                                            <div class="form-check">
                                                <input
                                                    id="access-rule-matrix-export"
                                                    v-model="item.matrix_values.export"
                                                    class="form-check-input"
                                                    type="checkbox"
                                                >
                                                <label
                                                    class="form-check-label"
                                                    for="access-rule-matrix-export"
                                                >
                                                    Export
                                                </label>
                                            </div>
                                        </div>
                                    </td>
                                    <td class="text-end">
                                        <a
                                            href="#"
                                            class="text-reset col-2 text-end"
                                            @click.prevent="removeAccessRule(i)"
                                        >
                                            <i class="fas fa-fw fa-times" />
                                        </a>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </template>
                    <alert
                        v-else
                        :message="`No Access rules defined. This would equal to Default Access. Please change access type or add your desired users/groups.`"
                        :type="'warning'"
                        :noicon="false"
                    />

                    <h6 class="mt-3">Add Working group or User</h6>
                    <simple-search
                        :endpoint="searchGroupsAndUsers"
                        :key-fn="_ => {}"
                        :filter-fn="preprocessMatches"
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
            const typeToValue = type => {
                let val = 0;
                if(type == 'restricted') {
                    val = 0;
                } else if(type == 'users') {
                    val = 50;
                } else if(type == 'open') {
                    val = 100;
                }
                return val;
            };
            const btnStateClasses = btnType => {
                const value = typeToValue(btnType);
                const classes = [];
                if(state.accessTypeValue >= value) {
                    classes.push('btn-primary');
                } else {
                    classes.push('btn-secondary');
                }
                return classes;
            }
            const preprocessMatches = (results, query) => {
                const filtered = [];
                results.forEach(itm => {
                    if(itm.result_type == 'wg') {
                        itm.id = `wg_${itm.id}`;
                    }
                    // Do not display users/groups already added
                    if(state.accessRuleIds.includes(itm.id)) {
                        return;
                    }
                    filtered.push(itm);
                });
                return filtered;
            };
            const setMatrixType = item => {
                if(!item.matrix_values) {
                    item.matrix_values = {
                        write: false,
                        create: false,
                        delete: false,
                        export: false,
                    };
                }
            }
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
                accessTypeValue: computed(_ => typeToValue(state.accessType)),
                accessRuleIds: computed(_ => state.accessRules.map(ar => ar.id)),
                accessRules: [],
            });

            // RETURN
            return {
                t,
                // HELPERS
                searchGroupsAndUsers,
                // LOCAL
                btnStateClasses,
                preprocessMatches,
                setMatrixType,
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