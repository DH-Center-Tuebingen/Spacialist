<template>
    <vue-final-modal
        class="modal-container modal"
        content-class="sp-modal-content sp-modal-content-xs"
        name="user-info-modal"
    >
        <div class="sp-modal-content sp-modal-content-xs">
            <div class="modal-header">
                <h5 class="modal-title">
                    {{
                        t('global.user.info_title')
                    }}
                </h5>
                <button
                    type="button"
                    class="btn-close"
                    aria-label="Close"
                    data-bs-dismiss="modal"
                    @click="closeModal()"
                />
            </div>
            <div class="modal-body">
                <div class="text-center">
                    <user-avatar
                        :user="user"
                        :size="128"
                    />
                    <h3 class="mb-0 mt-1">
                        {{ user.name }}
                    </h3>
                    <h6 class="fw-normal text-muted">
                        {{ user.nickname }}
                    </h6>
                </div>
                <div class="d-flex flex-row justify-content-center gap-5">
                    <dl class="mb-0 flex-grow-1 text-end">
                        <dt>
                            {{ t('global.user.member_since') }}
                        </dt>
                        <dd>
                            <span
                                id="user-member-since"
                                :title="user.created_at"
                            >
                                {{ date(user.created_at, 'DD.MM.YYYY', true, true) }}
                            </span>
                            <br>
                            <!-- eslint-disable-->
                            <span
                                v-if="state.isDeactivated"
                                class="small text-muted bg-warning rounded px-2 py-1"
                                :title="user.deleted_at"
                                v-html="t('global.user.deactivated_since', {dt: date(user.deleted_at, 'DD.MM.YYYY', true, true)})"
                            />
                            <!-- eslint-enable-->
                        </dd>
                    </dl>
                    <div class="border" />
                    <dl class="mb-0 flex-grow-1">
                        <dt>
                            {{ t('global.email') }}
                        </dt>
                        <dd>
                            <a :href="`mailto:${user.email}`">
                                {{ user.email }}
                            </a>
                        </dd>
                        <dt
                            v-if="state.hasPhone"
                        >
                            {{ t('global.phonenumber') }}
                        </dt>
                        <dd
                            v-if="state.hasPhone"
                        >
                            <a :href="`tel:${user.metadata?.phonenumber}`">
                                {{ user.metadata?.phonenumber }}
                            </a>
                        </dd>
                        <dt
                            v-if="state.hasOrcid"
                        >
                            {{ t('global.orcid') }}
                        </dt>
                        <dd
                            v-if="state.hasOrcid"
                        >
                            <a
                                :href="`https://orcid.org/${user.metadata?.orcid}`"
                                target="_blank"
                            >
                                {{ user.metadata?.orcid }}
                            </a>
                        </dd>
                    </dl>
                </div>
                <hr>
                <div class="d-flex flex-row justify-content-center gap-5">
                    <dl class="mb-0 flex-grow-1 text-end">
                        <dt>
                            {{ t('global.user.role') }}
                            <i class="fas fa-fw fa-id-card-clip" />
                        </dt>
                        <dd>
                            {{ user.metadata?.role || t('global.user.not_assigned') }}
                        </dd>
                        <dt>
                            {{ t('global.user.field') }}
                            <i class="fas fa-fw fa-chalkboard-user" />
                        </dt>
                        <dd>
                            {{ user.metadata?.field || t('global.user.not_assigned') }}
                        </dd>
                    </dl>
                    <div class="border" />
                    <dl class="mb-0 flex-grow-1">
                        <dt>
                            <i class="fas fa-fw fa-school" />
                            {{ t('global.user.institution') }}
                        </dt>
                        <dd>
                            {{ user.metadata?.institution || t('global.user.not_assigned') }}
                        </dd>
                        <dt>
                            <i class="fas fa-fw fa-users-between-lines" />
                            {{ t('global.user.department') }}
                        </dt>
                        <dd>
                            {{ user.metadata?.department || t('global.user.not_assigned') }}
                        </dd>
                    </dl>
                </div>
            </div>
            <div class="modal-footer">
                <button
                    type="button"
                    class="btn btn-outline-secondary"
                    data-bs-dismiss="modal"
                    @click="closeModal()"
                >
                    <i class="fas fa-fw fa-times" /> {{ t('global.close') }}
                </button>
            </div>
        </div>
    </vue-final-modal>
</template>

<script>
    import {
        computed,
        reactive,
        toRefs,
    } from 'vue';
    import { useI18n } from 'vue-i18n';

    import {
        date,
    } from '@/helpers/filters.js';

    export default {
        props: {
            user: {
                required: true,
                type: Object,
            },
        },
        emits: ['closing'],
        setup(props, context) {
            const { t } = useI18n();
            const {
                user
            } = toRefs(props);

            // FUNCTIONS
            const closeModal = _ => {
                context.emit('closing', false);
            }

            // DATA
            const state = reactive({
                isDeactivated: !!user.value.deleted_at,
                hasMetadata: !!user.value.metadata,
                hasPhone: computed(_ => state.hasMetadata && !!user.value.metadata.phonenumner),
                hasOrcid: computed(_ => state.hasMetadata && !!user.value.metadata.orcid),
            });

            // RETURN
            return {
                t,
                // HELPERS
                date,
                // LOCAL
                closeModal,
                // STATE
                state,
            }
        },
    }
</script>
