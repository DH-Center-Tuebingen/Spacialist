<template>
    <modal name="user-info-modal" height="auto" @before-open="getUser">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">
                    {{
                        $t('global.user.info_title')
                    }}
                </h5>
                <button type="button" class="close" aria-label="Close" @click="hideModal">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <div class="text-center">
                    <user-avatar :user="user" :size="128"></user-avatar>
                    <h3 class="mb-0 mt-2">
                        {{ user.name }}
                    </h3>
                    <h6 class="font-weight-normal text-muted">
                        {{ user.nickname }}
                    </h6>
                    <dl class="row">
                        <dt class="col-md-6">
                            {{ $t('global.user.member_since') }}
                        </dt>
                        <dd class="col-md-6">
                            <span id="user-member-since" :title="user.created_at">
                                {{ user.created_at | date('DD.MM.YYYY', true, true) }}
                            </span>
                            <br/>
                            <span v-if="isDeactivated" class="small text-muted bg-warning rounded px-2 py-1" :title="user.deleted_at" v-html="$t('global.user.deactivated_since', {dt: parsedDate(user.deleted_at)})">
                            </span>
                        </dd>
                        <dt class="col-md-6">
                            {{ $t('global.email') }}
                        </dt>
                        <dd class="col-md-6">
                            <a :href="`mailto:${user.email}`">
                                {{ user.email }}
                            </a>
                        </dd>
                        <dt class="col-md-6" v-if="hasPhone">
                            {{ $t('global.phonenumber') }}
                        </dt>
                        <dd class="col-md-6" v-if="hasPhone">
                            <a :href="`tel:${user.metadata.phonenumber}`">
                                {{ user.metadata.phonenumber }}
                            </a>
                        </dd>
                        <dt class="col-md-6" v-if="hasOrcid">
                            {{ $t('global.orcid') }}
                        </dt>
                        <dd class="col-md-6" v-if="hasOrcid">
                            <a :href="`https://orcid.org/${user.metadata.orcid}`" target="_blank">
                                {{ user.metadata.orcid }}
                            </a>
                        </dd>
                    </dl>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" @click="hideModal">
                    <i class="fas fa-fw fa-times"></i> {{ $t('global.close') }}
                </button>
            </div>
        </div>
    </modal>
</template>

<script>
    export default {
        name: 'UserInfoModal',
        mounted() {
            // Enable popovers
            $(function () {
                $('[data-bs-toggle="tooltip"]').tooltip()
            });
        },
        methods: {
            getUser(event) {
                this.user = event.params.user;
            },
            parsedDate(d) {
                return Vue.filter('date')(d, 'DD.MM.YYYY', true, true);
            },
            hideModal() {
                this.$modal.hide('user-info-modal');
            }
        },
        data() {
            return {
                user: {},
            }
        },
        computed: {
            isDeactivated() {
                return !!this.user.deleted_at;
            },
            hasMetadata() {
                return !!this.user.metadata;
            },
            hasPhone() {
                return this.hasMetadata && !!this.user.metadata.phonenumber;
            },
            hasOrcid() {
                return this.hasMetadata && !!this.user.metadata.orcid;
            }
        }
    }
</script>
