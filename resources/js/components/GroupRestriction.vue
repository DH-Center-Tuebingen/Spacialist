<template>
    <div>
        <div class="my-3 p-0 scroll-y-auto">
            <ul class="list-group mx-0 mt-2 flex-grow-1 scroll-y-auto" v-if="$hasAccessRules(model)">
                <li class="list-group-item d-flex justify-content-between" v-for="rule in model.access_rules">
                    <a href="#" @click.prevent="">
                        <i class="fas fa-fw fa-users-cog"></i> {{ $getGroup(rule.group_id).display_name }}
                    </a>
                    <template>
                        <span v-if="rule.rules == 'rw'">
                            Write Access
                        </span>
                        <span v-else>
                            Read-Only
                        </span>
                    </template>
                    <a href="#" class="text-body" @click.prevent="removeAccessRule(rule)" v-if="$hasWriteAccess(model)">
                        <i class="fas fa-fw fa-xs fa-times" style="vertical-align: 0;"></i>
                    </a>
                </li>
            </ul>
            <p class="alert alert-info" v-else>
                {{ $t('main.group.access_restriction_no_groups') }}
            </p>
        </div>

        <h5>Restrict access to certain groups</h5>

        <form role="form" @submit.prevent="addAccessRule(selectedAccessRule)">
            <div class="form-group row">
                <div class="col-md-9">
                    <multiselect
                        id="access-rule-select"
                        label="display_name"
                        track-by="id"
                        v-model="selectedAccessRule.group"
                        :allow-empty="true"
                        :close-on-select="false"
                        :hide-selected="true"
                        :multiple="false"
                        :options="availableGroups"
                        :placeholder="$t('global.select.placehoder')"
                        :select-label="$t('global.select.select')"
                        :deselect-label="$t('global.select.deselect')">
                    </multiselect>
                </div>
                <div class="col-md-3">
                    <input type="checkbox" v-model="selectedAccessRule.writeAccess" />
                </div>
            </div>

            <button type="submit" class="btn btn-success">
                <span class="fa-stack d-inline">
                    <i class="fas fa-lock"></i>
                    <i class="fas fa-plus" data-fa-transform="shrink-4 left-9 down-10"></i>
                </span>
                <span class="stacked-icon-text">
                    Add group
                </span>
            </button>
        </form>
    </div>
</template>

<script>
    export default {
        props: {
            model: {
                required: true,
                type: Object
            },
            type: {
                required: true,
                type: String
            }
        },
        methods: {
            getKeyByType() {
                switch(this.type) {
                    case 'entity':
                        return 'entity_id';
                    case 'file':
                        return 'file_id';
                    case 'geodata':
                        return 'geodata_id';
                }
            },
            getEndpointByType() {
                switch(this.type) {
                    case 'entity':
                        return 'entity';
                    case 'file':
                        return 'file';
                    case 'geodata':
                        return 'geodata';
                }
            },
            addAccessRule(rule) {
                const typeIdKey = this.getKeyByType();
                let data = {
                    has_write: rule.writeAccess
                };
                data[typeIdKey] = this.model.id;
                $httpQueue.add(() => $http.patch(`restrict_to/${rule.group.id}`, data).then(response => {
                    this.model.access_rules.push(response.data);
                    this.$showToast(
                        this.$t('main.group.toasts.restriction_removed.title'),
                        this.$t('main.group.toasts.restriction_removed.msg', {
                            name: this.model.name,
                            group: this.$getGroup(rule.group_id).display_name
                        }),
                        'success'
                    );
                }));
            },
            removeAccessRule(rule) {
                $httpQueue.add(() => $http.delete(`/restrict_to/${rule.group_id}/${this.getEndpointByType()}/${this.model.id}`).then(response => {
                    const idx = this.model.access_rules.findIndex(ar => ar.group_id == rule.group_id);
                    if(idx) {
                        this.model.access_rules.splice(idx, 1);
                        this.$showToast(
                            this.$t('main.group.toasts.restriction_removed.title'),
                            this.$t('main.group.toasts.restriction_removed.msg', {
                                name: this.model.name,
                                group: this.$getGroup(rule.group_id).display_name
                            }),
                            'success'
                        );
                    }
                }));
            },
        },
        data () {
            return {
                selectedAccessRule: {
                    group: {},
                    writeAccess: false
                },
            }
        },
        computed: {
            availableGroups() {
                return this.$getAvailableGroups(this.model);
            },
        }
    }
</script>
