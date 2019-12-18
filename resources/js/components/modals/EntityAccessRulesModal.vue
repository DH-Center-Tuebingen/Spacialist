<template>
    <div class="modal-content">
        <div class="modal-header">
            <h5 class="modal-title">{{ $t('main.entity.modals.add.title') }}</h5>
            <button type="button" class="close" aria-label="Close" @click="$emit('close')">
                <span aria-hidden="true">&times;</span>
            </button>
        </div>
        <div class="modal-body">
            <div class="my-3 p-0 scroll-y-auto">
                <ul class="list-group mx-0 mt-2 flex-grow-1 scroll-y-auto" v-if="$hasAccessRules(entity)">
                    <li class="list-group-item d-flex justify-content-between" v-for="rule in entity.access_rules">
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
                        <a href="#" class="text-body" @click.prevent="removeAccessRule(entity, rule)" v-if="entity.hasWriteAccess">
                            <i class="fas fa-fw fa-xs fa-times" style="vertical-align: 0;"></i>
                        </a>
                    </li>
                </ul>
                <p class="alert alert-info" v-else>
                    {{ $t('global.access_restricted_no_groups') }}
                </p>
            </div>

            <h5>Restrict access to certain groups</h5>

            <form role="form" @submit.prevent="addAccessRule(entity, selectedAccessRule)">
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
        <div class="modal-footer">
            <button type="button" class="btn btn-secondary" @click="$emit('close')">
                {{ $t('global.close') }}
            </button>
        </div>
    </div>
</template>

<script>
export default {
    name: 'EntityAccessRules',
    props: {
        entity: {
            required: true,
            type: Object
        },
    },
    methods: {
        addAccessRule(entity, rule) {
            const data = {
                entity_id: entity.id,
                has_write: rule.writeAccess
            };
            $httpQueue.add(() => $http.patch(`restrict_to/${rule.group.id}`, data).then(response => {
                this.entity.access_rules.push(response.data);
                this.$showToast(
                    this.$t('main.entity.toasts.restriction_removed.title'),
                    this.$t('main.entity.toasts.restriction_removed.msg', {
                        name: entity.name,
                        group: this.$getGroup(rule.group_id).display_name
                    }),
                    'success'
                );
            }));
        },
        removeAccessRule(entity, rule) {
            $httpQueue.add(() => $http.delete(`/restrict_to/${rule.group_id}/entity/${entity.id}`).then(response => {
                const idx = entity.access_rules.findIndex(ar => ar.group_id == rule.group_id);
                if(idx) {
                    entity.access_rules.splice(idx, 1);
                    this.$showToast(
                        this.$t('main.entity.toasts.restriction_removed.title'),
                        this.$t('main.entity.toasts.restriction_removed.msg', {
                            name: entity.name,
                            group: this.$getGroup(rule.group_id).display_name
                        }),
                        'success'
                    );
                }
            }));
        },
    },
    data() {
        return {
            selectedAccessRule: {
                group: {},
                writeAccess: false
            },
        }
    },
    computed: {
        availableGroups() {
            return this.$getAvailableGroups(this.entity);
        },
    }
}
</script>
