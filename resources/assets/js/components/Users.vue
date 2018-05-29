<template>
    <div>
        <table class="table table-striped table-hover">
            <thead class="thead-light">
                <tr>
                    <th>Name</th>
                    <th>E-Mail-Address</th>
                    <th>Roles</th>
                    <th>Added</th>
                    <th>Updated</th>
                    <th>Options</th>
                </tr>
            </thead>
            <tbody>
                <tr v-for="user in userList">
                    <td>
                        {{ user.name }}
                    </td>
                    <td>
                        {{ user.email }}
                    </td>
                    <td>
                        <multiselect
                            label="display_name"
                            track-by="id"
                            v-model="user.roles"
                            v-validate=""
                            :closeOnSelect="false"
                            :hideSelected="true"
                            :multiple="true"
                            :name="'roles_'+user.id"
                            :options="roles">
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
                                <sup class="notification-info" v-if="isDirty('roles_'+user.id)">
                                    <i class="fas fa-fw fa-xs fa-circle text-warning"></i>
                                </sup>
                            </span>
                            <div class="dropdown-menu" aria-labelledby="dropdownMenuButton">
                                <a class="dropdown-item" href="#" v-if="isDirty('roles_'+user.id)" @click="onPatchUser(user.id)">
                                    <i class="fas fa-fw fa-check text-success"></i> Save
                                </a>
                                <a class="dropdown-item" href="#" @click="updatePassword(user.id)">
                                    <i class="fas fa-fw fa-paper-plane text-info"></i> Send Reset-Mail
                                </a>
                                <a class="dropdown-item" href="#" @click="requestDeleteUser(user.id)">
                                    <i class="fas fa-fw fa-trash text-danger"></i> Delete
                                </a>
                            </div>
                        </div>
                    </td>
                </tr>
            </tbody>
        </table>

        <button type="button" class="btn btn-success" @click="showNewUserModal">
            <i class="fas fa-fw fa-plus"></i> Add New User
        </button>

        <modal name="new-user-modal" height="auto" :scrollable="true">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Add new User</h5>
                    <button type="button" class="close" aria-label="Close" @click="hideNewUserModal">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <form id="newUserForm" name="newUserForm" role="form" v-on:submit.prevent="onAddUser(newUser)">
                        <div class="form-group">
                            <label class="col-form-label col-md-3" for="name">
                                Username:
                            </label>
                            <div class="col-md-9">
                                <input class="form-control" type="text" id="name" v-model="newUser.name" required />
                            </div>
                        </div>
                        <div class="form-group">
                            <label class="col-form-label col-md-3" for="display_name">
                                E-Mail Address:
                            </label>
                            <div class="col-md-9">
                                <input class="form-control" type="email" id="display_name" v-model="newUser.email" required />
                            </div>
                        </div>
                        <div class="form-group">
                            <label class="col-form-label col-md-3" for="description">
                                Password:
                            </label>
                            <div class="col-md-9">
                                <input class="form-control" type="password" id="description" v-model="newUser.password" required />
                            </div>
                        </div>
                    </form>
                </div>
                <div class="modal-footer">
                    <button type="submit" class="btn btn-success" form="newUserForm">
                        <i class="fas fa-fw fa-plus"></i> Add
                    </button>
                    <button type="button" class="btn btn-danger"     @click="hideNewUserModal">
                        <i class="fas fa-fw fa-ban"></i> Cancel
                    </button>
                </div>
            </div>
        </modal>

        <modal name="confirm-delete-user-modal" height="auto" :scrollable="true">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Delete User {{ selectedUser.name }}</h5>
                    <button type="button" class="close" aria-label="Close" @click="hideDeleteUserModal">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    Do you really want to delete User <strong>{{ selectedUser.name }}</strong> (<i>{{ selectedUser.email }}</i>)?
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-success" @click="deleteUser(selectedUser.id)">
                        <i class="fas fa-fw fa-check"></i> Delete
                    </button>
                    <button type="button" class="btn btn-danger" @click="hideDeleteUserModal">
                        <i class="fas fa-fw fa-ban"></i> Cancel
                    </button>
                </div>
            </div>
        </modal>
    </div>
</template>

<script>
    import { mapFields } from 'vee-validate';

    export default {
        props: ['users', 'roles'],
        mounted() {},
        methods: {
            showNewUserModal() {
                this.$modal.show('new-user-modal');
            },
            hideNewUserModal() {
                this.$modal.hide('new-user-modal');
                this.newUser = {};
            },
            onAddUser(newUser) {
                let users = this.userList;
                let hideModal = this.hideNewUserModal;
                this.$http.post('/api/user', newUser).then(function(response) {
                    users.push(response.data);
                    hideModal();
                });
            },
            onPatchUser(id) {
                if(this.isDirty('roles_'+id)) {
                    let setPristine = this.setPristine;
                    let user = this.userList.find(u => u.id == id);
                    let roles = [];
                    for(let i=0; i<user.roles.length; i++) {
                        roles.push(user.roles[i].id);
                    }
                    let data = {};
                    data.roles = JSON.stringify(roles);
                    this.$http.patch('/api/user/'+id+'/role', data).then(function(response) {
                        setPristine('roles_'+id);
                    });
                }
            },
            showDeleteUserModal() {
                this.$modal.show('confirm-delete-user-modal');
            },
            hideDeleteUserModal() {
                this.$modal.hide('confirm-delete-user-modal');
                this.selectedUser = {};
            },
            requestDeleteUser(id) {
                this.selectedUser = this.userList.find(u => u.id == id);
                this.showDeleteUserModal();
            },
            deleteUser(id) {
                if(!id) return;
                let users = this.userList;
                let index = this.userList.findIndex(u => u.id == id);
                let hideModal = this.hideDeleteUserModal;
                this.$http.delete('/api/user/' + id).then(function(response) {
                    // TODO check response
                    if(index > -1) users.splice(index, 1);
                    hideModal();
                });
            },
            isDirty(fieldname) {
                if(this.fields[fieldname]) {
                    return this.fields[fieldname].dirty;
                }
                return false;
            },
            setPristine(fieldname) {
                this.$validator.flag(fieldname, {
                    dirty: false,
                    pristine: true
                });
            }
        },
        data() {
            return {
                userRoles: {},
                updatePassword: id => (
                    console.log(id)
                ),
                newUser: {},
                selectedUser: {},
                localUsers: this.users.slice()
            }
        },
        computed: {
            userList() {
                return this.localUsers;
            }
        }
    }
</script>
