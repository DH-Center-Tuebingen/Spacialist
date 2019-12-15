<template>
    <div>
        <table class="table table-striped table-hover">
            <thead class="thead-light">
                <tr>
                    <th>{{ $t('global.name') }}</th>
                    <th>{{ $t('global.display-name') }}</th>
                    <th>{{ $t('global.description') }}</th>
                    <th>{{ $t('global.created-at') }}</th>
                    <th>{{ $t('global.updated-at') }}</th>
                    <th>{{ $t('global.options') }}</th>
                </tr>
            </thead>
            <tbody>
                <tr v-for="group in groupList">
                    <td>
                        {{ group.name }}
                    </td>
                    <td>
                        <input type="text" class="form-control" v-model="group.display_name" v-validate="" :name="`disp_${group.id}`" />
                    </td>
                    <td>
                        <input type="text" class="form-control" v-model="group.description" v-validate="" :name="`desc_${group.id}`" />
                    </td>
                    <td>
                        {{ group.created_at }}
                    </td>
                    <td>
                        {{ group.updated_at }}
                    </td>
                    <td>
                        <div class="dropdown">
                            <span id="dropdownMenuButton" class="clickable" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                <i class="fas fa-fw fa-ellipsis-h"></i>
                                <sup class="notification-info" v-if="groupDirty(group.id)">
                                    <i class="fas fa-fw fa-xs fa-circle text-warning"></i>
                                </sup>
                            </span>
                            <div class="dropdown-menu" aria-labelledby="dropdownMenuButton">
                                <a class="dropdown-item" href="#" v-if="groupDirty(group.id)" :disabled="!$can('add_edit_group')" @click.prevent="onPatchGroup(group.id)">
                                    <i class="fas fa-fw fa-check text-success"></i> {{ $t('global.save') }}
                                </a>
                                <a class="dropdown-item" href="#" @click.prevent="requestDeleteGroup(group.id)" :disabled="!$can('delete_group')">
                                    <i class="fas fa-fw fa-trash text-danger"></i> {{ $t('global.delete') }}
                                </a>
                            </div>
                        </div>
                    </td>
                </tr>
            </tbody>
        </table>

        <button type="button" class="btn btn-success" @click="showNewGroupModal" :disabled="!$can('add_edit_group')">
            <i class="fas fa-fw fa-plus"></i> {{ $t('main.group.add-button') }}
        </button>

        <modal name="new-group-modal" height="auto" :scrollable="true" v-can="'add_edit_group'">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="newGroupModalLabel">{{ $t('main.group.modal.new.title') }}</h5>
                    <button type="button" class="close" aria-label="Close" @click="hideNewGroupModal">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <form id="newGroupForm" name="newGroupForm" role="form" v-on:submit.prevent="onAddGroup(newGroup)">
                        <div class="form-group">
                            <label class="col-form-label col-md-3" for="name">
                                {{ $t('global.name') }}
                                <span class="text-danger">*</span>:
                            </label>
                            <div class="col-md-9">
                                <input class="form-control" :class="$getValidClass(error, 'name')" type="text" id="name" v-model="newGroup.name" required autofocus />

                                <div class="invalid-feedback">
                                    <span v-for="msg in error.name">
                                        {{ msg }}
                                    </span>
                                </div>
                            </div>
                        </div>
                        <div class="form-group">
                            <label class="col-form-label col-md-3" for="display_name">
                                {{ $t('global.display-name') }}:
                            </label>
                            <div class="col-md-9">
                                <input class="form-control" type="text" id="display_name" v-model="newGroup.display_name" />
                            </div>
                        </div>
                        <div class="form-group">
                            <label class="col-form-label col-md-3" for="description">
                                {{ $t('global.description') }}:
                            </label>
                            <div class="col-md-9">
                                <input class="form-control" type="text" id="description" v-model="newGroup.description" />
                            </div>
                        </div>
                    </form>
                </div>
                <div class="modal-footer">
                    <button type="submit" class="btn btn-success" form="newGroupForm">
                        <i class="fas fa-fw fa-plus"></i> {{ $t('global.add') }}
                    </button>
                    <button type="button" class="btn btn-danger" @click="hideNewGroupModal">
                        <i class="fas fa-fw fa-ban"></i> {{ $t('global.cancel') }}
                    </button>
                </div>
            </div>
        </modal>

        <modal name="confirm-delete-group-modal" height="auto" :scrollable="true" v-can="'delete_group'">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">{{ $t('global.delete-name.title', {name: selectedGroup.display_name}) }}</h5>
                    <button type="button" class="close" aria-label="Close" @click="hideDeleteGroupModal">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    {{ $t('global.delete-name.desc', {name: selectedGroup.display_name}) }}
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-success" @click="deleteGroup(selectedGroup.id)">
                        <i class="fas fa-fw fa-check"></i> {{ $t('global.delete') }}
                    </button>
                    <button type="button" class="btn btn-danger" @click="hideDeleteGroupModal">
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
            $httpQueue.add(() => $http.get('group').then(response => {
                const groups = response.data.groups;
                next(vm => vm.init(groups));
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
                        await this.$asyncFor(this.groupList, async g => {
                            await this.onPatchGroup(g.id);
                        });
                        loadNext();
                    };
                    patching();
                };
                this.$modal.show(this.discardModal, {reference: this.$t('global.settings.groups'), onDiscard: discardAndContinue, onSave: saveAndContinue, onCancel: _ => next(false)})
            } else {
                loadNext();
            }
        },
        mounted() {},
        methods: {
            init(groups) {
                this.groupList = groups;
            },
            showNewGroupModal() {
                if(!this.$can('add_edit_group')) return;
                this.$modal.show('new-group-modal');
            },
            hideNewGroupModal() {
                this.$modal.hide('new-group-modal');
                this.newGroup = {};
            },
            onAddGroup(newGroup) {
                if(!this.$can('add_edit_group')) return;
                $httpQueue.add(() => $http.post('group', newGroup).then(response => {
                    this.groupList.push(response.data);
                    this.hideNewGroupModal();
                }).catch(e => {
                    this.$getErrorMessages(e, this.error);
                }));
            },
            onPatchGroup(id) {
                if(!this.$can('add_edit_group')) return new Promise(r => r());
                if(!this.groupDirty(id)) return new Promise(r => r());
                let group = this.groupList.find(g => g.id == id);
                let data = {};
                if(this.isDirty(`disp_${id}`)) {
                    data.display_name = group.display_name;
                }
                if(this.isDirty(`desc_${id}`)) {
                    data.description = group.description;
                }
                return $httpQueue.add(() => $http.patch(`group/${id}`, data).then(response => {
                    this.setGroupPristine(id);
                    group.updated_at = response.data.updated_at;
                    this.$showToast(
                        this.$t('main.group.toasts.updated.title'),
                        this.$t('main.group.toasts.updated.msg', {
                            name: group.display_name
                        }),
                        'success'
                    );
                }));
            },
            showDeleteGroupModal() {
                if(!this.$can('delete_group')) return;
                this.$modal.show('confirm-delete-group-modal');
            },
            hideDeleteGroupModal() {
                this.$modal.hide('confirm-delete-group-modal');
                this.selectedGroup = {};
            },
            requestDeleteGroup(id) {
                if(!this.$can('delete_group')) return;
                this.selectedGroup = this.groupList.find(g => g.id == id);
                this.showDeleteGroupModal();
            },
            deleteGroup(id) {
                const vm = this;
                if(!vm.$can('delete_group')) return;
                if(!id) return;
                $httpQueue.add(() => $http.delete(`group/${id}`).then(response => {
                    const index = this.groupList.findIndex(g => g.id == id);
                    if(index > -1) this.groupList.splice(index, 1);
                    vm.hideDeleteGroupModal();
                }));
            },
            isDirty(fieldname) {
                if(this.fields[fieldname]) {
                    return this.fields[fieldname].dirty;
                }
                return false;
            },
            groupDirty(gid) {
                return this.isDirty(`disp_${gid}`) || this.isDirty(`desc_${gid}`);
            },
            setPristine(fieldname) {
                this.$validator.flag(fieldname, {
                    dirty: false,
                    pristine: true
                });
            },
            setGroupPristine(gid) {
                this.setPristine(`disp_${gid}`);
                this.setPristine(`desc_${gid}`);
            }
        },
        data() {
            return {
                groupList: [],
                newGroup: {},
                error: {},
                selectedGroup: {},
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
