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
                <tr v-for="role in roles">
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
                                <a class="dropdown-item" href="#">
                                    <i class="fas fa-fw fa-trash text-danger"></i> Delete
                                </a>
                            </div>
                        </div>
                    </td>
                </tr>
            </tbody>
        </table>

        <button type="button" class="btn btn-success" data-toggle="modal" data-target="#newRoleModal">
            <i class="fas fa-fw fa-plus"></i> Add New Role
        </button>

        <div class="modal fade" id="newRoleModal" tabindex="-1" role="dialog"   aria-labelledby="newRoleModalLabel" aria-hidden="true" ref="newRoleModal">
            <div class="modal-dialog" role="document">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="newRoleModalLabel">Add new Role</h5>
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                    <div class="modal-body">
                        <form name="newRoleForm" class="form-horizontal" role="form" v-on:submit.prevent="onAddRole">
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
                        <button type="button" class="btn btn-danger"     data-dismiss="modal">
                            <i class="fas fa-fw fa-ban"></i> Cancel
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>

<script>
    export default {
        props: ['roles', 'permissions'],
        mounted() {},
        methods: {
        },
        data() {
            return {
                userRoles: {},
                updateSelection: selection => (
                    console.log(selection)
                ),
                newRole: {},
                onAddRole: _ => {
                    console.log("Adding role: " + this.$data.newRole.toString());
                    console.log(this.$refs.newRoleModal);
                }
            }
        },
    }
</script>
