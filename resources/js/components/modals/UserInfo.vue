<template>
    <div class="modal-content">
        <div class="modal-header">
            <h5 class="modal-title">
                {{
                    $t('global.user.info_title')
                }}
            </h5>
            <button type="button" class="close" aria-label="Close" @click="$emit('close')">
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
                        <span id="user-member-since" data-toggle="tooltip" :title="user.created_at" data-trigger="hover" data-placement="bottom">
                            {{ user.created_at | date('DD.MM.YYYY', true, true) }}
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
                    <dt class="col-md-6">
                        {{ $t('global.phonenumber') }}
                    </dt>
                    <dd class="col-md-6">
                        <a :href="`tel:${user.phonenumber}`">
                            {{ user.phonenumber }}
                        </a>
                    </dd>
                </dl>
            </div>
        </div>
        <div class="modal-footer">
            <button type="button" class="btn btn-secondary" @click="$emit('close')">
                <i class="fas fa-fw fa-times"></i> {{ $t('global.close') }}
            </button>
        </div>
    </div>
</template>

<script>
    export default {
        name: 'UserInfoModal',
        props: {
            user: {
                required: true,
                type: Object
            }
        },
        mounted() {
            // Enable popovers
            $(function () {
                $('[data-toggle="tooltip"]').tooltip()
            });
        },
        methods: {
        },
        data() {
            return {
            }
        },
    }
</script>
