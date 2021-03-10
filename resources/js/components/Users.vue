<template>
    <div class="d-flex flex-column h-100">
        <h4>
            {{ t('main.user.active_users') }}
            <button type="button" class="btn btn-outline-success" @click="showNewUserModal()" :disabled="!can('create_users')">
                <i class="fas fa-fw fa-plus"></i> {{ t('main.user.add-button') }}
            </button>
        </h4>
        <div class="table-responsive flex-grow-1">
            <table class="table table-striped table-hover table-light" v-dcan="'view_users'" v-if="state.dataInitialized">
                <thead class="sticky-top">
                    <tr>
                        <th>{{ t('global.name') }}</th>
                        <th>{{ t('global.email') }}</th>
                        <th>{{ t('global.roles') }}</th>
                        <th>{{ t('global.added_at') }}</th>
                        <th>{{ t('global.updated_at') }}</th>
                        <th>{{ t('global.options') }}</th>
                    </tr>
                </thead>
                <tbody>
                    <tr v-for="user in state.userList" :key="user.id">
                        <td>
                            <a href="#" @click.prevent="showUserInfo(user)" class="text-nowrap text-reset text-decoration-none">
                                <user-avatar class="align-middle" :user="user" :size="20"></user-avatar>
                                <span class="align-middle ms-2">
                                    {{ user.name }} <span class="text-muted">{{ user.nickname }}</span>
                                </span>
                            </a>
                        </td>
                        <td>
                            <input type="email" class="form-control" @input="v.fields[user.id].mail.handleInput" :class="getClassByValidation(v.fields[user.id].mail.errors)" v-model="v.fields[user.id].mail.value" :name="`email_${user.id}`" required />

                            <div class="invalid-feedback">
                                <span v-for="(msg, i) in v.fields[user.id].mail.errors" :key="i">
                                    {{ msg }}
                                </span>
                            </div>
                        </td>
                        <td>
                            <multiselect
                                v-model="v.fields[user.id].roles.value"
                                :class="getClassByValidation(v.fields[user.id].roles.errors)"
                                :name="`roles_${user.id}`"
                                :object="true"
                                :label="'display_name'"
                                :track-by="'display_name'"
                                :valueProp="'id'"
                                :mode="'tags'"
                                :disabled="!can('add_remove_role')"
                                :options="state.roles"
                                :placeholder="t('main.user.add-role-placeholder')"
                                @input="v.fields[user.id].roles.handleInput">
                            </multiselect>

                            <div class="invalid-feedback">
                                <span v-for="(msg, i) in v.fields[user.id].roles.errors" :key="i">
                                    {{ msg }}
                                </span>
                            </div>
                        </td>
                        <td>
                            {{ date(user.created_at) }}
                        </td>
                        <td>
                            {{ date(user.updated_at) }}
                        </td>
                        <td>
                            <div class="dropdown">
                                <span id="dropdownMenuButton" class="clickable" data-bs-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                    <i class="fas fa-fw fa-ellipsis-h"></i>
                                    <sup class="notification-info" v-if="userDirty(user.id)">
                                        <i class="fas fa-fw fa-xs fa-circle text-warning"></i>
                                    </sup>
                                </span>
                                <div class="dropdown-menu" aria-labelledby="dropdownMenuButton">
                                    <a class="dropdown-item" href="#" v-if="userDirty(user.id)" :disabled="!userValid(user.id) || !can('add_remove_role')" @click.prevent="patchUser(user.id)">
                                        <i class="fas fa-fw fa-check text-success"></i> {{ t('global.save') }}
                                    </a>
                                    <a class="dropdown-item" href="#" v-if="userDirty(user.id)" @click.prevent="resetUser(user.id)">
                                        <i class="fas fa-fw fa-undo text-warning"></i> {{ t('global.reset') }}
                                    </a>
                                    <a class="dropdown-item" href="#" v-if="can('delete_users') && hasPreference('prefs.enable-password-reset-link')" :disabled="!can('change_password')" @click.prevent="updatePassword(user.email)">
                                        <i class="fas fa-fw fa-paper-plane text-info"></i> {{ t('global.send_reset_mail') }}
                                    </a>
                                    <a class="dropdown-item" href="#" :disabled="!can('delete_users')" @click.prevent="deactivateUser(user.id)">
                                        <i class="fas fa-fw fa-user-times text-danger"></i> {{ t('global.deactivate') }}
                                    </a>
                                </div>
                            </div>
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>

        <hr>

        <h4>
            {{ t('main.user.deactivated_users') }}
        </h4>
        <div class="table-responsive flex-grow-1" v-if="state.deletedUserList.length > 0">
            <table class="table table-striped table-hover table-light" v-dcan="'view_users'">
                <thead class="sticky-top">
                    <tr>
                        <th>{{ t('global.name') }}</th>
                        <th>{{ t('global.email') }}</th>
                        <th>{{ t('global.roles') }}</th>
                        <th>{{ t('global.added_at') }}</th>
                        <th>{{ t('global.updated_at') }}</th>
                        <th>{{ t('global.deactivated_at') }}</th>
                        <th>{{ t('global.options') }}</th>
                    </tr>
                </thead>
                <tbody>
                    <tr v-for="dUser in state.deletedUserList" :key="dUser.id">
                        <td>
                            <a href="#" @click.prevent="showUserInfo(dUser)" class="text-nowrap text-reset text-decoration-none">
                                <user-avatar class="align-middle" :user="dUser" :size="20"></user-avatar>
                                <span class="align-middle ms-2">
                                    {{ dUser.name }} <span class="text-muted">{{ dUser.nickname }}</span>
                                </span>
                            </a>
                        </td>
                        <td>
                            {{ dUser.email }}
                        </td>
                        <td>
                            <multiselect
                                v-model="dUser.roles"
                                :name="`roles_${dUser.id}`"
                                :object="true"
                                :label="'display_name'"
                                :track-by="'display_name'"
                                :valueProp="'id'"
                                :mode="'tags'"
                                :disabled="true"
                                :options="[]"
                                :placeholder="t('main.user.add-role-placeholder')">
                            </multiselect>
                        </td>
                        <td>
                            {{ date(dUser.created_at) }}
                        </td>
                        <td>
                            {{ date(dUser.updated_at) }}
                        </td>
                        <td>
                            {{ date(dUser.deleted_at) }}
                        </td>
                        <td>
                            <div class="dropdown">
                                <span :id="`deactive-user-dropdown-${dUser.id}`" class="clickable" data-bs-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                    <i class="fas fa-fw fa-ellipsis-h"></i>
                                </span>
                                <div class="dropdown-menu" :aria-labelledby="`deactive-user-dropdown-${dUser.id}`">
                                    <a class="dropdown-item" href="#" :disabled="!can('delete_users')" @click.prevent="reactivateUser(dUser.id)">
                                        <i class="fas fa-fw fa-user-check text-success"></i> {{ t('global.reactivate') }}
                                    </a>
                                </div>
                            </div>
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>
        <div class="alert alert-info" role="alert" v-else>
            {{ t('main.user.empty_list') }}
        </div>
    </div>
</template>

<script>
    import {
        computed,
        reactive,
    } from 'vue';

    import { onBeforeRouteLeave } from 'vue-router';
    import { useI18n } from 'vue-i18n';
    import { useField } from 'vee-validate';

    import * as yup from 'yup';

    import store from '../bootstrap/store.js';

    import {
        reactivateUser as reactivateUserApi,
    } from '../api.js';
    import {
        showDiscard,
        showAddUser,
        showDeactivateUser,
        showUserInfo,
    } from '../helpers/modal.js';
    import {
        can,
        getClassByValidation,
        getUserBy,
        hasPreference,
    } from '../helpers/helpers.js';
    import {
        date,
    } from '../helpers/filters.js';

    export default {
        setup(props) {
            const { t } = useI18n();

            // FUNCTIONS
            const userDirty = id => {
                return v.fields[id].mail.meta.dirty || v.fields[id].roles.meta.dirty;
            };
            const userValid = id => {
                return v.fields[id].mail.meta.valid && v.fields[id].roles.meta.valid;
            };
            const resetUser = id => {
                v.fields[id].mail.reset();
                v.fields[id].roles.reset();
            };
            const cleanUser = id => {
                v.fields[id].mail.reset();
                v.fields[id].roles.reset();
            };
            const patchUser = id => {
                if(!userValid(id) || !can('add_remove_role')) {
                    return;
                }
                v.fields[id].mail.reset();
                v.fields[id].roles.reset();
            };
            const showNewUserModal = _ => {
                showAddUser();
            };
            const deactivateUser = id => {
                if(!can('delete_users')) return;
                showDeactivateUser(getUserBy(id));
            };
            const reactivateUser = id => {
                if(!can('delete_users')) return;
                reactivateUserApi(id).then(_ => {
                    store.dispatch('reactivateUser', id);
                });
            };
            const anyUserDirty = _ => {
                let isDirty = false;
                for(let i=0; i<state.userList.length; i++) {
                    const u = state.userList[i];
                    if(userDirty(u.id)) {
                        isDirty = true;
                        break;
                    }
                }
                return isDirty;
            };
            // Used in Discard Modal to make all fields undirty
            const resetData = _ => {
                for(let i=0; i<state.userList.length; i++) {
                    cleanUser(state.userList[i].id);
                }
            };
            // Used in Discard Modal to store data before moving on
            const onBeforeConfirm = _ => {
                for(let i=0; i<state.userList.length; i++) {
                    // TODO actually store dirty data
                    resetUser(state.userList[i].id);
                }
                return new Promise(r => r(null));
            };

            // DATA
            const state = reactive({
                userList: computed(_ => store.getters.users),
                deletedUserList: computed(_ => store.getters.deletedUsers),
                roles: computed(_ => store.getters.roles(true)),
                dataInitialized: computed(_ => state.userList.length > 0 && state.roles.length > 0),
            });
            const v = reactive({
                fields: computed(_ => {
                    const list = {};
                    for(let i=0; i<state.userList.length; i++) {
                        const u = state.userList[i];
                        const {
                            errors: em,
                            meta: mm,
                            value: vm,
                            handleInput: him,
                            handleReset: hrm,
                        } = useField(`email_${u.id}`, yup.string().required().email(), {
                            initialValue: u.email,
                        });
                        const {
                            errors: er,
                            meta: mr,
                            value: vr,
                            handleInput: hir,
                            handleReset: hrr,
                        } = useField(`roles_${u.id}`, yup.array(), {
                            initialValue: u.roles,
                        });
                        list[u.id] = reactive({
                            mail: {
                                errors: em,
                                meta: mm,
                                value: vm,
                                handleInput: him,
                                reset: hrm,
                            },
                            roles: {
                                errors: er,
                                meta: mr,
                                value: vr,
                                handleInput: hir,
                                reset: hrr,
                            },
                        });
                    }
                    return list;
                })
            });

            // ON BEFORE LEAVE
            onBeforeRouteLeave(async (to, from) => {
                if(anyUserDirty()) {
                    showDiscard(to, resetData, onBeforeConfirm);
                    return false;
                } else {
                    return true;
                }
            });

            // RETURN
            return {
                t,
                // HELPERS
                can,
                date,
                getClassByValidation,
                hasPreference,
                showUserInfo,
                // LOCAL
                userDirty,
                userValid,
                resetUser,
                patchUser,
                showNewUserModal,
                deactivateUser,
                reactivateUser,
                // PROPS
                // STATE
                state,
                v,
            }
        },
        //     onAddUser(newUser) {
        //         if(!this.$can('create_users')) return;
        //         $http.post('user', newUser).then(response => {
        //             this.userList.push(response.data);
        //             this.hideNewUserModal();
        //         }).catch(e => {
        //             this.$getErrorMessages(e, this.error);
        //         });
        //     },
        //     onPatchUser(id) {
        //         if(!this.$can('add_remove_role')) return new Promise(r => r());
        //         if(!this.userDirty(id)) return new Promise(r => r());
        //         let user = this.userList.find(u => u.id == id);
        //         if(!user.email) {
        //             // TODO error message
        //             return;
        //         }
        //         let data = {};
        //         if(this.isDirty(`roles_${id}`)) {
        //             let roles = [];
        //             for(let i=0; i<user.roles.length; i++) {
        //                 roles.push(user.roles[i].id);
        //             }
        //             data.roles = roles;
        //         }
        //         if(this.isDirty(`email_${id}`)) {
        //             data.email = user.email;
        //         }
        //         return $httpQueue.add(() => $http.patch(`user/${id}`, data).then(response => {
        //             this.setUserPristine(id);
        //             user.updated_at = response.data.updated_at;
        //             this.$showToast(
        //                 this.$t('main.user.toasts.updated.title'),
        //                 this.$t('main.user.toasts.updated.msg', {
        //                     name: user.name
        //                 }),
        //                 'success'
        //             );
        //         }).catch(e => {
        //             this.$getErrorMessages(e, this.error, `_${id}`);
        //         }));
        //     },
        //     updatePassword(email) {
        //         if(!this.$can('change_password')) return;
        //         const data = {
        //             email: email
        //         };
        //         $httpQueue.add(() => $http.post(`user/reset/password`, data).then(response => {
        //         }).catch(e => {
        //         }));
        //     },
        // },
    }
</script>
