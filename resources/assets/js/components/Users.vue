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
                        <multiselect v-model="user.roles" :options="roles" label="display_name" :multiple="true" :hideSelected="true" :closeOnSelect="false" track-by="id" :name="'user'+user.id+'roles'" v-validate=""></multiselect>
                    </td>
                    <td>
                        {{ user.created_at }}
                    </td>
                    <td>
                        {{ user.updated_at }}
                    </td>
                    <td>
                        <div class="dropdown">
                            <span id="dropdownMenuButton" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                <i class="fas fa-fw fa-ellipsis-h"></i>
                                <sup style="margin-left:-0.5rem;">
                                    <i class="fas fa-fw fa-xs fa-circle text-warning" v-if="isDirty('user'+user.id+'roles')"></i>
                                </sup>
                            </span>
                            <div class="dropdown-menu" aria-labelledby="dropdownMenuButton">
                                <a class="dropdown-item" href="#" v-if="isDirty('user'+user.id+'roles')" v-on:click="onPatchUser(user.id)">
                                    <i class="fas fa-fw fa-check text-success"></i> Save
                                </a>
                                <a class="dropdown-item" href="#" v-on:click="updatePassword(user.id)">
                                    <i class="fas fa-fw fa-paper-plane text-info"></i> Send Reset-Mail
                                </a>
                                <a class="dropdown-item" href="#" v-on:click="requestDeleteUser(user.id)">
                                    <i class="fas fa-fw fa-trash text-danger"></i> Delete
                                </a>
                            </div>
                        </div>
                    </td>
                </tr>
            </tbody>
        </table>

        <button type="button" class="btn btn-success" v-on:click="showNewUserModal">
            <i class="fas fa-fw fa-plus"></i> Add New User
        </button>

        <modal name="new-user-modal" height="auto" :scrollable="true">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Add new User</h5>
                    <button type="button" class="close" aria-label="Close" v-on:click="hideNewUserModal">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <form name="newUserForm" class="form-horizontal" role="form" v-on:submit.prevent="onAddUser(newUser)">
                        <div class="form-group">
                            <label class="control-label col-md-3" for="name">
                                Username:
                            </label>
                            <div class="col-md-9">
                                <input class="form-control" type="text" id="name" v-model="newUser.name" required />
                            </div>
                        </div>
                        <div class="form-group">
                            <label class="control-label col-md-3" for="display_name">
                                E-Mail Address:
                            </label>
                            <div class="col-md-9">
                                <input class="form-control" type="email" id="display_name" v-model="newUser.email" required />
                            </div>
                        </div>
                        <div class="form-group">
                            <label class="control-label col-md-3" for="description">
                                Password:
                            </label>
                            <div class="col-md-9">
                                <input class="form-control" type="password" id="description" v-model="newUser.password" required />
                            </div>
                        </div>
                        <button type="submit" class="btn btn-success">
                            <i class="fas fa-fw fa-plus"></i> Add
                        </button>
                    </form>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-danger"     v-on:click="hideNewUserModal">
                        <i class="fas fa-fw fa-ban"></i> Cancel
                    </button>
                </div>
            </div>
        </modal>

        <modal name="confirm-delete-user-modal" height="auto" :scrollable="true">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Delete User {{ selectedUser.name }}</h5>
                    <button type="button" class="close" aria-label="Close" v-on:click="hideDeleteUserModal">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    Do you really want to delete User <strong>{{ selectedUser.name }}</strong> (<i>{{ selectedUser.email }}</i>)?
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-success" v-on:click="deleteUser(selectedUser.id)">
                        <i class="fas fa-fw fa-check"></i> Delete
                    </button>
                    <button type="button" class="btn btn-danger" v-on:click="hideDeleteUserModal">
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
                this.$http.post('/api/user', newUser).then(function(response) {
                    users.push(response.data);
                });
                this.hideNewUserModal()
            },
            onPatchUser(id) {
                //TODO
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
                this.$http.delete('/api/user/' + id).then(function(response) {
                    // TODO check response
                    if(index > -1) users.splice(index, 1);
                });
                this.hideDeleteUserModal()
            },
            isDirty(fieldname) {
                if(this.fields[fieldname]) {
                    return this.fields[fieldname].dirty;
                }
                return false;
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
            },
            mapFields() {
                let fields = {};
                for (let i = 0; i < this.userList.length; i++){
                    let id = this.userList[i].id;
                    fields['user'+id+'roles'] = 'user'+id+'roles';
                }
                return Object.assign({}, mapFields, fields);
            }
        }
    }
</script>
