<template>
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
                )
            }
        },
    }
</script>
