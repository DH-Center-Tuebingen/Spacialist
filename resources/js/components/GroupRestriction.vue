<template>
    <div v-if="rulesLoaded">
        <div class="mb-3 p-0 scroll-y-auto">
            <ul class="list-group mx-0 flex-grow-1 scroll-y-auto" v-if="rules.length > 0">
                <li class="list-group-item d-flex justify-content-between" v-for="rule in rules">
                    <a href="#" @click.prevent="">
                        <i class="fas fa-fw fa-users-cog"></i> {{ $getGroup(rule.group_id).display_name }}
                    </a>
                    <template>
                        <span v-if="rule.rules == 'rw'">
                            {{ $t('global.write_access') }}
                        </span>
                        <span v-else>
                            {{ $t('global.read_only') }}
                        </span>
                    </template>
                    <a href="#" class="text-body" @click.prevent="removeAccessRule(rule)" v-if="$hasWriteAccess(model)">
                        <i class="fas fa-fw fa-xs fa-times" style="vertical-align: 0;"></i>
                    </a>
                </li>
            </ul>
            <p class="alert alert-info my-0" v-else>
                {{ $t('main.group.access_restriction_no_groups') }}
            </p>
        </div>

        <h5>
            {{ $t('main.group.access_restriction_heading') }}
        </h5>

        <p class="alert alert-warning my-3" v-if="forceWriteAccess">
            {{ $t('main.group.access_restriction_first_write_access') }}
        </p>

        <form role="form" @submit.prevent="addAccessRule(selectedAccessRule)">
            <div class="form-group">
                <label for="access-rule-select">
                    {{ $tc('global.group') }}
                </label>
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
            <div class="form-group">
                <div class="form-check">
                    <input class="form-check-input" id="access-rule-input" type="checkbox" v-model="selectedAccessRule.writeAccess" :disabled="forceWriteAccess">
                    <label class="form-check-label" for="access-rule-input">
                        {{ $t('global.write_access') }}
                    </label>
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
        mounted() {
            this.rulesLoaded = false;
            $httpQueue.add(() => $http.get(`/access_rules/${this.model.id}/${this.getEndpointByType()}`).then(response => {
                this.rules = response.data.rules;
                this.rulesLoaded = true;
                this.checkForceWriteAccess();
            }));
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
                    write_access: rule.writeAccess
                };
                data[typeIdKey] = this.model.id;
                $httpQueue.add(() => $http.patch(`restrict_to/${rule.group.id}`, data).then(response => {
                    this.rules.push(response.data);
                    this.resetAccessRuleForm();
                    this.checkForceWriteAccess();
                    this.$emit('rules-modified', {
                        action: 'add',
                        rule: response.data
                    });
                }));
            },
            removeAccessRule(rule) {
                $httpQueue.add(() => $http.delete(`/restrict_to/${rule.group_id}/${this.model.id}/${this.getEndpointByType()}`).then(response => {
                    const idx = this.rules.findIndex(ar => ar.group_id == rule.group_id);
                    if(idx > -1) {
                        const removedRule = this.rules.splice(idx, 1);
                        this.$emit('rules-modified', {
                            action: 'delete',
                            rule: removedRule
                        });
                        this.checkForceWriteAccess();
                    }
                }));
            },
            resetAccessRuleForm() {
                this.selectedAccessRule.group = {};
                this.selectedAccessRule.writeAccess = false;
            },
            checkForceWriteAccess() {
                // if no rules defined, set write access to true
                // because first rule must have write access
                // otherwise the model would be locked "forever"
                if(this.rules.length === 0) {
                    this.selectedAccessRule.writeAccess = true;
                    this.forceWriteAccess = true;
                } else {
                    this.forceWriteAccess = false;
                }
            }
        },
        data () {
            return {
                rulesLoaded: false,
                rules: [],
                forceWriteAccess: false,
                selectedAccessRule: {
                    group: {},
                    writeAccess: false
                },
            }
        },
        computed: {
            availableGroups() {
                if(!this.rules) return [];
                return this.$getAvailableGroups(this.rules);
            },
        }
    }
</script>
