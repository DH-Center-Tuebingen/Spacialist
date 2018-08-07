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
                <tr v-for="role in roleList">
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
                        <multiselect
                            label="display_name"
                            track-by="id"
                            v-model="role.permissions"
                            v-validate=""
                            :closeOnSelect="false"
                            :disabled="!$can('add_remove_permission')"
                            :hideSelected="true"
                            :multiple="true"
                            :name="'perms_'+role.id"
                            :options="permissions">
                        </multiselect>
                    </td>
                    <td>
                        {{ role.created_at }}
                    </td>
                    <td>
                        {{ role.updated_at }}
                    </td>
                    <td>
                        <div class="dropdown">
                            <span id="dropdownMenuButton" class="clickable" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                <i class="fas fa-fw fa-ellipsis-h"></i>
                                <sup class="notification-info" v-if="isDirty('perms_'+role.id)">
                                    <i class="fas fa-fw fa-xs fa-circle text-warning"></i>
                                </sup>
                            </span>
                            <div class="dropdown-menu" aria-labelledby="dropdownMenuButton">
                                <a class="dropdown-item" href="#" v-if="isDirty('perms_'+role.id)" :disabled="!$can('add_remove_permission')" @click="onPatchRole(role.id)">
                                    <i class="fas fa-fw fa-check text-success"></i> Save
                                </a>
                                <!-- <a class="dropdown-item" href="#">
                                    <i class="fas fa-fw fa-edit text-info"></i> Edit
                                </a> -->
                                <a class="dropdown-item" href="#" @click.prevent="requestDeleteRole(role.id)" :disabled="!$can('delete_role')">
                                    <i class="fas fa-fw fa-trash text-danger"></i> Delete
                                </a>
                            </div>
                        </div>
                    </td>
                </tr>
            </tbody>
        </table>

        <button type="button" class="btn btn-success" @click="showNewRoleModal" :disabled="!$can('add_edit_role')">
            <i class="fas fa-fw fa-plus"></i> Add New Role
        </button>

        <modal name="new-role-modal" height="auto" :scrollable="true" v-can="'add_edit_role'">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="newRoleModalLabel">Add new Role</h5>
                    <button type="button" class="close" aria-label="Close" @click="hideNewRoleModal">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <form id="newRoleForm" name="newRoleForm" role="form" v-on:submit.prevent="onAddRole(newRole)">
                        <div class="form-group">
                            <label class="col-form-label col-md-3" for="name">
                                Role Name:
                            </label>
                            <div class="col-md-9">
                                <input class="form-control" type="text" id="name" v-model="newRole.name" required />
                            </div>
                        </div>
                        <div class="form-group">
                            <label class="col-form-label col-md-3" for="display_name">
                                Display Name:
                            </label>
                            <div class="col-md-9">
                                <input class="form-control" type="text" id="display_name" v-model="newRole.display_name" required />
                            </div>
                        </div>
                        <div class="form-group">
                            <label class="col-form-label col-md-3" for="description">
                                Role Description:
                            </label>
                            <div class="col-md-9">
                                <input class="form-control" type="text" id="description" v-model="newRole.description" required />
                            </div>
                        </div>
                    </form>
                </div>
                <div class="modal-footer">
                    <button type="submit" class="btn btn-success" form="newRoleForm">
                        <i class="fas fa-fw fa-plus"></i> Add
                    </button>
                    <button type="button" class="btn btn-danger" @click="hideNewRoleModal">
                        <i class="fas fa-fw fa-ban"></i> Cancel
                    </button>
                </div>
            </div>
        </modal>

        <modal name="confirm-delete-role-modal" height="auto" :scrollable="true" v-can="'delete_role'">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Delete Role {{ selectedRole.name }}</h5>
                    <button type="button" class="close" aria-label="Close" @click="hideDeleteRoleModal">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    Do you really want to delete Role <strong>{{ selectedRole.display_name }}</strong> (<i>{{ selectedRole.name }}</i>)?
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-success" @click="deleteRole(selectedRole.id)">
                        <i class="fas fa-fw fa-check"></i> Delete
                    </button>
                    <button type="button" class="btn btn-danger" @click="hideDeleteRoleModal">
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
        beforeRouteEnter(to, from, next) {
            $http.get('role').then(response => {
                const roles = response.data.roles;
                const permissions = response.data.permissions;
                next(vm => vm.init(roles, permissions));
            });
        },
        mounted() {},
        methods: {
            init(roles, permissions) {
                this.roleList = roles;
                this.permissions = permissions;
            },
            showNewRoleModal() {
                if(!this.$can('add_edit_role')) return;
                this.$modal.show('new-role-modal');
            },
            hideNewRoleModal() {
                this.$modal.hide('new-role-modal');
                this.newRole = {};
            },
            onAddRole(newRole) {
                const vm = this;
                if(!vm.$can('add_edit_role')) return;
                vm.$http.post('/api/role', newRole).then(function(response) {
                    vm.roleList.push(response.data);
                    vm.hideNewRoleModal();
                });
            },
            onPatchRole(id) {
                const vm = this;
                if(!vm.$can('add_edit_role')) return;
                if(vm.isDirty(`perms_${id}`)) {
                    let role = vm.roleList.find(r => r.id == id);
                    let permissions = [];
                    for(let i=0; i<role.permissions.length; i++) {
                        permissions.push(role.permissions[i].id);
                    }
                    const data = {
                        permissions: JSON.stringify(permissions)
                    };
                    vm.$http.patch(`/api/role/${id}/permission`, data).then(function(response) {
                        vm.setPristine(`perms_${id}`);
                        vm.$showToast('Role updated', `${role.display_name} successfully updated.`, 'success');
                    });
                }
            },
            showDeleteRoleModal() {
                if(!this.$can('delete_role')) return;
                this.$modal.show('confirm-delete-role-modal');
            },
            hideDeleteRoleModal() {
                this.$modal.hide('confirm-delete-role-modal');
                this.selectedRole = {};
            },
            requestDeleteRole(id) {
                if(!this.$can('delete_role')) return;
                this.selectedRole = this.roleList.find(r => r.id == id);
                this.showDeleteRoleModal();
            },
            deleteRole(id) {
                const vm = this;
                if(!vm.$can('delete_role')) return;
                if(!id) return;
                vm.$http.delete(`/api/role/${id}`).then(function(response) {
                    const index = vm.roleList.findIndex(r => r.id == id);
                    if(index > -1) vm.roleList.splice(index, 1);
                    vm.hideDeleteRoleModal();
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
                roleList: [],
                permissions: [],
                userRoles: {},
                newRole: {},
                selectedRole: {}
            }
        }
    }
</script>
