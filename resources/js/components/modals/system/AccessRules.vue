<template>
    <vue-final-modal
        class="modal-container modal"
        content-class="sp-modal-content"
        name="dataset-access-rules-modal"
    >
        <div class="sp-modal-content">
            <div class="modal-header">
                <h5 class="modal-title">
                    {{ t('main.app.modals.access.title') }}
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
                    v-if="state.isRestricted"
                    class="my-2"
                >
                    <h5>Access Rules</h5>

                    <h6 class="mt-3">
                        Add Working group or User
                    </h6>
                    <simple-search
                        :endpoint="searchGroupsAndUsers"
                        :key-fn="_ => { }"
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
                                    :key="`dataset-access-rule-${i}`"
                                >
                                    <td class="mw-50">
                                        <group-user-item :item="item" />
                                    </td>
                                    <td class="d-flex flex-column">
                                        <div class="d-flex flex-row gap-2 align-items-center text-muted">
                                            <template
                                                v-for="type in ['read', 'role', 'matrix']"
                                                :key="`access-rule-radio-${i}-${type}`"
                                            >
                                                <input
                                                    v-model="item.rule_type"
                                                    type="radio"
                                                    class="form-check-input mt-0"
                                                    :name="`access-rule-radio-${i}`"
                                                    :value="type"
                                                    @click="() => type === 'matrix' ? setMatrixType(item) : null"
                                                >
                                                <span v-if="type != 'matrix'">
                                                    /
                                                </span>
                                            </template>
                                        </div>
                                        <div
                                            v-if="item.rule_type == 'matrix'"
                                            class="d-flex flex-row gap-3"
                                        >
                                            <div
                                                v-for="type in ['write', 'create', 'delete', 'share']"
                                                :key="`access-rule-matrix-${type}`"
                                                class="form-check"
                                            >
                                                <input
                                                    :id="`access-rule-matrix-${type}`"
                                                    v-model="item.rule_values[type]"
                                                    class="form-check-input"
                                                    type="checkbox"
                                                >
                                                <label
                                                    class="form-check-label user-select-none"
                                                    :for="`access-rule-matrix-${type}`"
                                                >
                                                    <!-- {{ type[0].toUpperCase() + type.slice(1) }} -->
                                                    {{ type.charAt(0).toUpperCase() + type.slice(1) }}
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
                        <alert
                            class="mt-3"
                            :message="`Access Rules for users are having a <span class='fw-bold'>higher priority</span> than group rules! So it is possible to a whole group only read access, but allow a specific user from that group to have advanced access rights.`"
                            :type="'info'"
                            :noicon="false"
                            :icontext="'Note'"
                            :closeable="true"
                        />
                    </template>
                    <alert
                        v-else
                        class="mt-3"
                        :message="`No Access rules defined. This would equal to Default Access. Please change access type or add your desired users/groups.`"
                        :type="'warning'"
                        :noicon="false"
                    />
                </div>

                <hr>
                <div
                    class="d-flex flex-column bg-secondary bg-opacity-10 rounded-4 px-3 py-3 position-relative text-body">
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
                            :class="btnStateClasses('private')"
                            style="width: 2rem; height:2rem;"
                            @click="state.accessType = 'private'"
                        >
                            <i class="fas fa-fw fa-user-lock fa-sm" />
                        </button>
                        <button
                            type="button"
                            class="position-absolute top-0 start-33 translate-middle btn btn-sm rounded-pill"
                            :class="btnStateClasses('restricted')"
                            style="width: 2rem; height:2rem;"
                            @click="state.accessType = 'restricted'"
                        >
                            <i class="fas fa-fw fa-user-shield fa-sm" />
                        </button>
                        <button
                            type="button"
                            class="position-absolute top-0 start-66 translate-middle btn btn-sm rounded-pill"
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
                    <div class="d-flex flex-column bg-info bg-opacity-25 rounded-3 px-3 py-2 mt-3 text-center">
                        <template v-if="state.accessType == 'private'">
                            <h5 class="mb-0">
                                Private Access
                            </h5>
                            <hr class="my-2 mx-5">
                            <p class="mb-0">
                                This settings gives only yourself access to this data. All other users can neither see,
                                nor modify.
                            </p>
                        </template>
                        <template v-else-if="state.accessType == 'restricted'">
                            <h5 class="mb-0">
                                Restricted Access
                            </h5>
                            <hr class="my-2 mx-5">
                            <p class="mb-0">
                                This settings gives you the possibilities to define which users and/or groups can have
                                access to this data at all. Furthermore, you can decide whether users can have only read
                                access or are allowed to modify and add data.
                            </p>
                        </template>
                        <template v-else-if="state.accessType == 'users'">
                            <h5 class="mb-0">
                                User Access (Default)
                            </h5>
                            <hr class="my-2 mx-5">
                            <p class="mb-0">
                                This is the default setting. All registered users can view and/or modify data based on
                                their individual roles.
                            </p>
                        </template>
                        <template v-else-if="state.accessType == 'open'">
                            <h5 class="mb-0">
                                Public/Open Access
                            </h5>
                            <hr class="my-2 mx-5">
                            <p class="mb-0">
                                This gives the public read access to this data through the <span class="fst-italic">Open
                                    Access</span> feature. To allow the public to modify data, they still need a user
                                account, so changes are always related to a specific user.
                                <br>
                                WIP: To allow external users to get read access through the <span
                                    class="fst-italic">Open Access</span> feature, but keep the restrictions on
                                modification, use the checkbox in the respective type.
                            </p>
                        </template>
                    </div>
                    <div class="mt-3">
                        <span class="fw-bold">Note</span>: <span class="fst-italic">data</span> in this context always
                        refers to this entry
                        and all it's (if possible) sub-entries!
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button
                    type="submit"
                    class="btn btn-outline-success"
                    :disabled="!rulesValid()"
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
        onMounted,
    } from 'vue';
    import {useI18n} from 'vue-i18n';

    import store from '@/bootstrap/store.js';

    import {
        searchGroupsAndUsers,
    } from '@/api.js';

    import {
        getTs,
        getUserBy,
        getGroupBy,
    } from '@/helpers/helpers.js';

    import GroupUserItem from '@/components/user/GroupUserListItem.vue';

    export default {
        components: {
            'group-user-item': GroupUserItem,
        },
        props: {
            id: {
                required: true,
                type: Number,
            },
        },
        emits: ['closing', 'confirm'],
        setup(props, context) {
            const {t} = useI18n();
            const {
                id,
            } = toRefs(props);

            // FUNCTIONS
            const convertAccessRules = rules => {
                return rules.map(r => {
                    let r2 = null;
                    const metadata = {
                        rule_type: r.rule_type,
                        rule_values: r.rule_values,
                    };
                    if(r.guardable_type == 'App\\Group') {
                        r2 = {
                            ...getGroupBy(r.guardable_id),
                            ...{
                                id: `wg_${r.guardable_id}`,
                            },
                        };
                        metadata.result_type = 'wg';
                    } else if(r.guardable_type == 'App\\User') {
                        r2 = getUserBy(r.guardable_id, 'id', true);
                        metadata.result_type = 'u';
                    }

                    r2 = {
                        ...r2,
                        ...metadata,
                    };

                    return r2;
                });
            };
            const typeToValue = type => {
                let val = 0;
                if(type == 'private') {
                    val = 0;
                } else if(type == 'restricted') {
                    val = 33;
                } else if(type == 'users') {
                    val = 66;
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
            };
            const preprocessMatches = (results, query) => {
                const filtered = [];
                results.forEach(itm => {
                    // multiselect component uses 'valueProp' as part of the id generation
                    // thus same user id and group id would lead to errors
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
                if(!item.rule_values) {
                    item.rule_values = {
                        write: false,
                        create: false,
                        delete: false,
                        share: false,
                    };
                }
            };
            const addAccessRule = e => {
                if(e.removed) return;
                delete e.added;
                state.accessRules.push(e);
                state.resetValue = {
                    reset: true,
                    ts: getTs(),
                };
            };
            const removeAccessRule = idx => {
                state.accessRules.splice(idx, 1);
            };
            const rulesValid = _ => {
                if(state.isRestricted) {
                    if(state.accessRules.length == 0) return false;

                    for(let i = 0; i < state.accessRules.length; i++) {
                        const curr = state.accessRules[i];
                        // an access rule requires a rule type...
                        if(!curr.rule_type || curr.rule_type == '') {
                            return false;
                        }
                        // ...and matrix rule type requires at least one matrix rule
                        if(curr.rule_type == 'matrix' && !Object.values(curr.rule_values).includes(true)) {
                            return false;
                        }
                    }
                }

                return true;
            };
            const setAccess = _ => {
                if(!rulesValid()) return;

                const data = {
                    type: state.accessType,
                };

                if(state.isRestricted) {
                    data.rules = state.accessRules.map(rule => {
                        const stripped = {
                            guard_type: rule.result_type,
                            type: rule.rule_type,
                        };

                        if(`${rule.id}`.startsWith('wg_')) {
                            stripped.id = parseInt(rule.id.substr(3));
                        } else {
                            stripped.id = rule.id;
                        }
                        if(rule.rule_type == 'matrix') {
                            stripped.values = rule.rule_values;
                        }
                        return stripped;
                    });
                }

                context.emit('confirm', data);
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
                // TODO replace with generic getter
                entity: computed(_ => store.getters.entities[id.value]),
            });

            onMounted(_ => {
                state.accessRules = state.entity.access_rules ? convertAccessRules(state.entity.access_rules) : [];
                state.accessType = state.entity.access_type ? state.entity.access_type.type : 'users';
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
                rulesValid,
                setAccess,
                closeModal,
                // STATE
                state,
            };
        },
    };
</script>