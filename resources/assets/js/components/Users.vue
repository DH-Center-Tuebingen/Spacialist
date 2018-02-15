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
                <tr v-for="user in users">
                    <td>
                        {{ user.name }}
                    </td>
                    <td>
                        {{ user.email }}
                    </td>
                    <td>
                        <multiselect v-model="user.roles" :options="roles" label="display_name" :multiple="true" :hideSelected="true" :closeOnSelect="false" @input="updateSelection"></multiselect>
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
                            </span>
                            <div class="dropdown-menu" aria-labelledby="dropdownMenuButton">
                                <a class="dropdown-item" href="#" v-on:click="updatePassword(user.id)">
                                    <i class="fas fa-fw fa-paper-plane text-info"></i> Send Reset-Mail
                                </a>
                                <a class="dropdown-item" href="#" v-on:click="deleteUser(user.id)">
                                    <i class="fas fa-fw fa-trash text-danger"></i> Delete
                                </a>
                            </div>
                        </div>
                    </td>
                </tr>
            </tbody>
        </table>

        <button type="button" class="btn btn-success" data-toggle="modal" data-target="#newUserModal">
            <i class="fas fa-fw fa-plus"></i> Add New User
        </button>

        <div class="modal fade" id="newUserModal" tabindex="-1" role="dialog"   aria-labelledby="newUserModalLabel" aria-hidden="true" ref="newUserModal">
            <div class="modal-dialog" role="document">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="newUserModalLabel">Add new User</h5>
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                    <div class="modal-body">
                        <form name="newUserForm" class="form-horizontal" role="form" v-on:submit.prevent="onAddUser">
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
        props: ['users', 'roles'],
        mounted() {},
        methods: {
        },
        data() {
            return {
                userRoles: {},
                updateSelection: selection => (
                    console.log(selection)
                ),
                deleteUser: id => (
                    console.log(id)
                ),
                updatePassword: id => (
                    console.log(id)
                ),
                newUser: {},
                onAddUser: _ => {
                    console.log("Adding user: " + this.$data.newUser.toString());
                    console.log(this.$refs.newUserModal);
                }
            }
        },
    }
</script>
