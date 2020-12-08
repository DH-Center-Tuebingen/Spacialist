<template>
    <div>
        <h4>
            {{ $t('main.user.active_users') }}
        </h4>
        <table class="table table-striped table-hover" v-can="'view_users'">
            <thead class="thead-light">
                <tr>
                    <th>{{ $t('global.name') }}</th>
                    <th>{{ $t('global.email') }}</th>
                    <th>{{ $t('global.roles') }}</th>
                    <th>{{ $t('global.added-at') }}</th>
                    <th>{{ $t('global.updated-at') }}</th>
                    <th>{{ $t('global.options') }}</th>
                </tr>
            </thead>
            <tbody>
                <tr v-for="user in userList" :key="user.id">
                    <td>
                        {{ user.name }} ({{ user.nickname }})
                    </td>
                    <td>
                        <input type="email" class="form-control" :class="$getValidClass(error, `email_${user.id}`)" v-model="user.email" v-validate="" :name="`email_${user.id}`" required />

                        <div class="invalid-feedback">
                            <span v-for="msg in error[`email_${user.id}`]">
                                {{ msg }}
                            </span>
                        </div>
                    </td>
                    <td>
                        <multiselect
                            label="display_name"
                            track-by="id"
                            v-model="user.roles"
                            v-validate=""
                            :closeOnSelect="false"
                            :disabled="!$can('add_remove_role')"
                            :hideSelected="true"
                            :multiple="true"
                            :name="`roles_${user.id}`"
                            :options="roles"
                            :placeholder="$t('main.user.add-role-placeholder')"
                            :select-label="$t('global.select.select')"
                            :deselect-label="$t('global.select.deselect')">
                        </multiselect>
                    </td>
                    <td>
                        {{ user.created_at }}
                    </td>
                    <td>
                        {{ user.updated_at }}
                    </td>
                    <td>
                        <div class="dropdown">
                            <span id="dropdownMenuButton" class="clickable" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                <i class="fas fa-fw fa-ellipsis-h"></i>
                                <sup class="notification-info" v-if="userDirty(user.id)">
                                    <i class="fas fa-fw fa-xs fa-circle text-warning"></i>
                                </sup>
                            </span>
                            <div class="dropdown-menu" aria-labelledby="dropdownMenuButton">
                                <a class="dropdown-item" href="#" v-if="userDirty(user.id)" :disabled="!$can('add_remove_role')" @click.prevent="onPatchUser(user.id)">
                                    <i class="fas fa-fw fa-check text-success"></i> {{ $t('global.save') }}
                                </a>
                                <a class="dropdown-item" href="#" v-if="$hasPreference('prefs.enable-password-reset-link')" :disabled="!$can('change_password')" @click.prevent="updatePassword(user.email)">
                                    <i class="fas fa-fw fa-paper-plane text-info"></i> Send Reset-Mail
                                </a>
                                <a class="dropdown-item" href="#" :disabled="!$can('delete_users')" @click.prevent="requestDeleteUser(user.id)">
                                    <i class="fas fa-fw fa-user-times text-danger"></i> {{ $t('global.deactivate') }}
                                </a>
                            </div>
                        </div>
                    </td>
                </tr>
            </tbody>
        </table>

        <button type="button" class="btn btn-success" @click="showNewUserModal" :disabled="!$can('create_users')">
            <i class="fas fa-fw fa-plus"></i> {{ $t('main.user.add-button') }}
        </button>

        <hr>

        <h4>
            {{ $t('main.user.deactivated_users') }}
        </h4>
        <table class="table table-striped table-hover" v-can="'view_users'" v-if="deletedUserList.length > 0">
            <thead class="thead-light">
                <tr>
                    <th>{{ $t('global.name') }}</th>
                    <th>{{ $t('global.email') }}</th>
                    <th>{{ $t('global.roles') }}</th>
                    <th>{{ $t('global.added-at') }}</th>
                    <th>{{ $t('global.updated-at') }}</th>
                    <th>{{ $t('global.deactivated-at') }}</th>
                    <th>{{ $t('global.options') }}</th>
                </tr>
            </thead>
            <tbody>
                <tr v-for="dUser in deletedUserList" :key="dUser.id">
                    <td>
                        {{ dUser.name }} ({{ dUser.nickname }})
                    </td>
                    <td>
                        {{ dUser.email }}
                    </td>
                    <td>
                        <multiselect
                            label="display_name"
                            track-by="id"
                            v-model="dUser.roles"
                            :disabled="true"
                            :multiple="true"
                            :options="[]"
                            :placeholder="$t('main.user.add-role-placeholder')"
                            :select-label="$t('global.select.select')"
                            :deselect-label="$t('global.select.deselect')">
                        </multiselect>
                    </td>
                    <td>
                        {{ dUser.created_at }}
                    </td>
                    <td>
                        {{ dUser.updated_at }}
                    </td>
                    <td>
                        {{ dUser.deleted_at }}
                    </td>
                    <td>
                        <div class="dropdown">
                            <span :id="`deactive-user-dropdown-${dUser.id}`" class="clickable" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                <i class="fas fa-fw fa-ellipsis-h"></i>
                            </span>
                            <div class="dropdown-menu" :aria-labelledby="`deactive-user-dropdown-${dUser.id}`">
                                <a class="dropdown-item" href="#" :disabled="!$can('delete_users')" @click.prevent="reactivateUser(dUser.id)">
                                    <i class="fas fa-fw fa-user-check text-success"></i> {{ $t('global.reactivate') }}
                                </a>
                            </div>
                        </div>
                    </td>
                </tr>
            </tbody>
        </table>
        <p class="alert alert-info" v-else>
            {{ $t('main.user.empty_list') }}
        </p>

        <modal name="new-user-modal" height="auto" :scrollable="true">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">{{ $t('main.user.modal.new.title') }}</h5>
                    <button type="button" class="close" aria-label="Close" @click="hideNewUserModal">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <form id="newUserForm" name="newUserForm" role="form" v-on:submit.prevent="onAddUser(newUser)">
                        <div class="form-group">
                            <label class="col-form-label col-md-3" for="name">
                                {{ $t('global.name') }}
                                <span class="text-danger">*</span>:
                            </label>
                            <div class="col-md-9">
                                <input class="form-control" :class="$getValidClass(error, 'name')" type="text" id="name" v-model="newUser.name" required />

                                <div class="invalid-feedback">
                                    <span v-for="msg in error.name">
                                        {{ msg }}
                                    </span>
                                </div>
                            </div>
                        </div>
                        <div class="form-group">
                            <label class="col-form-label col-md-3" for="nickname">
                                {{ $t('global.nickname') }}
                                <span class="text-danger">*</span>:
                            </label>
                            <div class="col-md-9">
                                <input class="form-control" :class="$getValidClass(error, 'nickname')" type="text" id="nickname" v-model="newUser.nickname" required />

                                <div class="invalid-feedback">
                                    <span v-for="msg in error.nickname">
                                        {{ msg }}
                                    </span>
                                </div>
                            </div>
                        </div>
                        <div class="form-group">
                            <label class="col-form-label col-md-4" for="display_name">
                                {{ $t('global.email') }}
                                <span class="text-danger">*</span>:
                            </label>
                            <div class="col-md-9">
                                <input class="form-control" :class="$getValidClass(error, 'email')" type="email" id="display_name" v-model="newUser.email" required />

                                <div class="invalid-feedback">
                                    <span v-for="msg in error.email">
                                        {{ msg }}
                                    </span>
                                </div>
                            </div>
                        </div>
                        <div class="form-group">
                            <label class="col-form-label col-md-3" for="description">
                                {{ $t('global.password') }}
                                <span class="text-danger">*</span>:
                            </label>
                            <div class="col-md-9">
                                <input class="form-control" :class="$getValidClass(error, 'password')" type="password" id="description" v-model="newUser.password" required />

                                <div class="invalid-feedback">
                                    <span v-for="msg in error.password">
                                        {{ msg }}
                                    </span>
                                </div>
                            </div>
                        </div>
                    </form>
                </div>
                <div class="modal-footer">
                    <button type="submit" class="btn btn-success" form="newUserForm">
                        <i class="fas fa-fw fa-plus"></i> {{ $t('global.add') }}
                    </button>
                    <button type="button" class="btn btn-danger"     @click="hideNewUserModal">
                        <i class="fas fa-fw fa-ban"></i> {{ $t('global.cancel') }}
                    </button>
                </div>
            </div>
        </modal>

        <modal name="confirm-delete-user-modal" height="auto" :scrollable="true">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">{{ $t('global.deactivate-name.title', {name: selectedUser.name}) }}</h5>
                    <button type="button" class="close" aria-label="Close" @click="hideDeleteUserModal">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    {{ $t('global.deactivate-name.desc', {name: selectedUser.name}) }}
                    <p class="alert alert-info mt-2">
                        {{ $t('global.deactivate-name.info') }}
                    </p>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-success" @click="deleteUser(selectedUser.id)">
                        <i class="fas fa-fw fa-check"></i> {{ $t('global.deactivate') }}
                    </button>
                    <button type="button" class="btn btn-danger" @click="hideDeleteUserModal">
                        <i class="fas fa-fw fa-ban"></i> {{ $t('global.cancel') }}
                    </button>
                </div>
            </div>
        </modal>
        <discard-changes-modal :name="discardModal"/>
    </div>
</template>

<script>
    import { mapFields } from 'vee-validate';

    export default {
        beforeRouteEnter(to, from, next) {
            $httpQueue.add(() => $http.get('user').then(response => {
                const users = response.data.users;
                const delUsers = response.data.deleted_users;
                const roles = response.data.roles;
                next(vm => vm.init(users, delUsers, roles));
            }));
        },
        beforeRouteLeave: function(to, from, next) {
            let loadNext = () => {
                next();
            }
            if(this.isOneDirty) {
                let discardAndContinue = () => {
                    loadNext();
                };
                let saveAndContinue = () => {
                    let patching = async _ => {
                        await this.$asyncFor(this.userList, async u => {
                            await this.onPatchUser(u.id);
                        });
                        loadNext();
                    };
                    patching();
                };
                this.$modal.show(this.discardModal, {reference: this.$t('global.settings.users'), onDiscard: discardAndContinue, onSave: saveAndContinue, onCancel: _ => next(false)})
            } else {
                loadNext();
            }
        },
        mounted() {},
        methods: {
            init(users, delUsers, roles) {
                this.userList = users;
                this.deletedUserList = delUsers;
                this.roles = roles;
            },
            showNewUserModal() {
                if(!this.$can('create_users')) return;
                this.$modal.show('new-user-modal');
            },
            hideNewUserModal() {
                this.$modal.hide('new-user-modal');
                this.newUser = {};
            },
            onAddUser(newUser) {
                if(!this.$can('create_users')) return;
                $http.post('user', newUser).then(response => {
                    this.userList.push(response.data);
                    this.hideNewUserModal();
                }).catch(e => {
                    this.$getErrorMessages(e, this.error);
                });
            },
            onPatchUser(id) {
                if(!this.$can('add_remove_role')) return new Promise(r => r());
                if(!this.userDirty(id)) return new Promise(r => r());
                let user = this.userList.find(u => u.id == id);
                if(!user.email) {
                    // TODO error message
                    return;
                }
                let data = {};
                if(this.isDirty(`roles_${id}`)) {
                    let roles = [];
                    for(let i=0; i<user.roles.length; i++) {
                        roles.push(user.roles[i].id);
                    }
                    data.roles = roles;
                }
                if(this.isDirty(`email_${id}`)) {
                    data.email = user.email;
                }
                return $httpQueue.add(() => $http.patch(`user/${id}`, data).then(response => {
                    this.setUserPristine(id);
                    user.updated_at = response.data.updated_at;
                    this.$showToast(
                        this.$t('main.user.toasts.updated.title'),
                        this.$t('main.user.toasts.updated.msg', {
                            name: user.name
                        }),
                        'success'
                    );
                }).catch(e => {
                    this.$getErrorMessages(e, this.error, `_${id}`);
                }));
            },
            showDeleteUserModal() {
                if(!this.$can('delete_users')) return;
                this.$modal.show('confirm-delete-user-modal');
            },
            hideDeleteUserModal() {
                this.$modal.hide('confirm-delete-user-modal');
                this.selectedUser = {};
            },
            requestDeleteUser(id) {
                if(!this.$can('delete_users')) return;
                this.selectedUser = this.userList.find(u => u.id == id);
                this.showDeleteUserModal();
            },
            deleteUser(id) {
                if(!this.$can('delete_users')) return;
                if(!id) return;
                $http.delete(`user/${id}`).then(response => {
                    const index = this.userList.findIndex(u => u.id == id);
                    if(index > -1) {
                        const delUsers = this.userList.splice(index, 1);
                        delUsers[0].deleted_at = response.data.deleted_at;
                        this.deletedUserList.push(delUsers[0]);
                    }
                    this.hideDeleteUserModal();
                });
            },
            reactivateUser(id) {
                if(!this.$can('delete_users')) return;
                if(!id) return;
                $http.patch(`user/restore/${id}`).then(response => {
                    const index = this.deletedUserList.findIndex(u => u.id == id);
                    if(index > -1) {
                        const reacUsers = this.deletedUserList.splice(index, 1);
                        this.userList.push(reacUsers[0]);
                    }
                });
            },
            updatePassword(email) {
                if(!this.$can('change_password')) return;
                const data = {
                    email: email
                };
                $httpQueue.add(() => $http.post(`user/reset/password`, data).then(response => {
                }).catch(e => {
                }));
            },
            isDirty(fieldname) {
                if(this.fields[fieldname]) {
                    return this.fields[fieldname].dirty;
                }
                return false;
            },
            userDirty(uid) {
                const u = this.userList.find(u => u.id == uid);
                return this.isDirty(`roles_${uid}`) ||
                    (this.isDirty(`email_${uid}`) && !!u.email);
            },
            setPristine(fieldname) {
                this.$validator.flag(fieldname, {
                    dirty: false,
                    pristine: true
                });
            },
            setUserPristine(uid) {
                this.setPristine(`roles_${uid}`);
                this.setPristine(`email_${uid}`);
            }
        },
        data() {
            return {
                userList: [],
                deletedUserList: [],
                roles: [],
                userRoles: {},
                newUser: {},
                error: {},
                selectedUser: {},
                discardModal: 'discard-changes-modal'
            }
        },
        computed: {
            isOneDirty() {
                return Object.keys(this.fields).some(key => this.fields[key].dirty);
            },
        }
    }
</script>
