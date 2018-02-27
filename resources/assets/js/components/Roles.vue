<template>
    <div>
        <table class="table table-striped table-hover">
            <thead class="thead-light">
                <tr>
                    <th>Name</th>
                    <th>Display Name</th>
                    <th>Description</th>
                    <th>Permissions</th>
                    <th>Created</th>
                    <th>Updated</th>
                    <th>Options</th>
                </tr>
            </thead>
            <tbody>
                <tr v-for="role in localRoles">
                    <td>
                        {{ role.name }}
                    </td>
                    <td>
                        {{ role.display_name }}
                    </td>
                    <td>
                        {{ role.description }}
                    </td>
                    <td>
                        <multiselect v-model="role.permissions" :options="permissions" label="display_name" :multiple="true" :hideSelected="true" :closeOnSelect="false" @input="updateSelection"></multiselect>
                    </td>
                    <td>
                        {{ role.created_at }}
                    </td>
                    <td>
                        {{ role.updated_at }}
                    </td>
                    <td>
                        <div class="dropdown">
                            <span id="dropdownMenuButton" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                <i class="fas fa-fw fa-ellipsis-h"></i>
                            </span>
                            <div class="dropdown-menu" aria-labelledby="dropdownMenuButton">
                                <a class="dropdown-item" href="#">
                                    <i class="fas fa-fw fa-edit text-info"></i> Edit
                                </a>
                                <a class="dropdown-item" href="#" v-on:click="requestDeleteRole(role.id)">
                                    <i class="fas fa-fw fa-trash text-danger"></i> Delete
                                </a>
                            </div>
                        </div>
                    </td>
                </tr>
            </tbody>
        </table>

        <button type="button" class="btn btn-success" v-on:click="showNewRoleModal">
            <i class="fas fa-fw fa-plus"></i> Add New Role
        </button>

        <modal name="new-role-modal" height="auto" :scrollable="true">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="newRoleModalLabel">Add new Role</h5>
                    <button type="button" class="close" aria-label="Close" v-on:click="hideNewRoleModal">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <form name="newRoleForm" class="form-horizontal" role="form" v-on:submit.prevent="onAddRole(newRole)">
                        <div class="form-group">
                            <label class="control-label col-md-3" for="name">
                                Role Name:
                            </label>
                            <div class="col-md-9">
                                <input class="form-control" type="text" id="name" v-model="newRole.name" required />
                            </div>
                        </div>
                        <div class="form-group">
                            <label class="control-label col-md-3" for="display_name">
                                Display Name:
                            </label>
                            <div class="col-md-9">
                                <input class="form-control" type="text" id="display_name" v-model="newRole.display_name" required />
                            </div>
                        </div>
                        <div class="form-group">
                            <label class="control-label col-md-3" for="description">
                                Role Description:
                            </label>
                            <div class="col-md-9">
                                <input class="form-control" type="text" id="description" v-model="newRole.description" required />
                            </div>
                        </div>
                        <button type="submit" class="btn btn-success">
                            <i class="fas fa-fw fa-plus"></i> Add
                        </button>
                    </form>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-danger" v-on:click="hideNewRoleModal">
                        <i class="fas fa-fw fa-ban"></i> Cancel
                    </button>
                </div>
            </div>
        </modal>

        <modal name="confirm-delete-role-modal" height="auto" :scrollable="true">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Delete Role {{ selectedRole.name }}</h5>
                    <button type="button" class="close" aria-label="Close" v-on:click="hideDeleteRoleModal">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    Do you really want to delete Role <strong>{{ selectedRole.display_name }}</strong> (<i>{{ selectedRole.name }}</i>)?
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-success" v-on:click="deleteRole(selectedRole.id)">
                        <i class="fas fa-fw fa-check"></i> Delete
                    </button>
                    <button type="button" class="btn btn-danger" v-on:click="hideDeleteRoleModal">
                        <i class="fas fa-fw fa-ban"></i> Cancel
                    </button>
                </div>
            </div>
        </modal>
    </div>
</template>

<script>
    export default {
        props: ['roles', 'permissions'],
        mounted() {},
        methods: {
            showNewRoleModal() {
                this.$modal.show('new-role-modal');
            },
            hideNewRoleModal() {
                this.$modal.hide('new-role-modal');
                this.newRole = {};
            },
            onAddRole(newRole) {
                let roles = this.newRoles;
                this.$http.post('/api/role', newRole).then(function(response) {
                    roles.push(response.data);
                });
                this.hideNewRoleModal()
            },
            showDeleteRoleModal() {
                this.$modal.show('confirm-delete-role-modal');
            },
            hideDeleteRoleModal() {
                this.$modal.hide('confirm-delete-role-modal');
                this.selectedRole = {};
            },
            requestDeleteRole(id) {
                this.selectedRole = this.localRoles.find(function(r) {
                    return r.id == id;
                });
                this.showDeleteRoleModal();
            },
            deleteRole(id) {
                if(!id) return;
                let roles;
                var index = this.localRoles.findIndex(function(r) {
                    return r.id == id;
                });
                if(index >= this.importedRoles.length) {
                    roles = this.newRoles;
                    index = index - this.importedRoles.length;
                } else {
                    roles = this.importedRoles;
                }
                this.$http.delete('/api/role/' + id).then(function(response) {
                    // TODO check response
                    if(index > -1) roles.splice(index, 1);
                });
                this.hideDeleteRoleModal()
            }
        },
        data() {
            return {
                userRoles: {},
                updateSelection: selection => (
                    console.log(selection)
                ),
                newRole: {},
                newRoles: [],
                importedRoles: this.roles.slice(),
                selectedRole: {}
            }
        },
        computed: {
            localRoles() {
                return this.importedRoles.concat(this.newRoles);
            }
        }
    }
</script>
