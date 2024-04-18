<template>
    <vue-final-modal
        class="modal-container modal"
        name="about-modal"
    >
        <div class="sp-modal-content sp-modal-content-sm">
            <div class="modal-header">
                <h5 class="modal-title">
                    {{
                        t('main.about.title')
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
                <div class="d-flex">
                    <div class="flex-shrink-0">
                        <img
                            class="me-3"
                            src="img/logo.png"
                            alt="spacialist logo"
                            width="64"
                        >
                    </div>
                    <div class="flex-grow-1 ps-3">
                        <h4>Spacialist</h4>
                        {{ t('main.about.desc') }}
                    </div>
                </div>
                <hr>
                <dl class="row">
                    <dt class="col-md-6 text-end">
                        {{ t('main.about.release.name') }}
                    </dt>
                    <dd class="col-md-6">
                        {{ state.version.name }}
                    </dd>
                    <dt class="col-md-6 text-end">
                        {{ t('main.about.release.time') }}
                    </dt>
                    <dd class="col-md-6">
                        <span
                            id="version-time"
                            data-bs-toggle="popover"
                            :data-content="datestring(state.version.time)"
                            data-trigger="hover"
                            data-placement="bottom"
                        >
                            {{ date(state.version.time) }}
                        </span>
                    </dd>
                    <dt class="col-md-6 text-end">
                        {{ t('main.about.release.full_name') }}
                    </dt>
                    <dd class="col-md-6">
                        {{ state.version.full }}
                    </dd>
                </dl>
                <hr>
                <h5 class="mb-1">
                    {{ t('main.about.contributor', 2) }}
                </h5>
                <div class="row gy-1">
                    <div
                        v-for="contributor in contributors.active"
                        :key="contributor.name"
                        class="col-md-6 d-flex flex-column align-items-start"
                    >
                        <span>
                            {{ contributor.name }}
                        </span>
                        <span class="badge bg-primary text-line">
                            {{ transJoin(contributor.roles) }}
                        </span>
                    </div>
                </div>
                <h5 class="mt-3 mb-1">
                    {{ t('main.about.former_contributor', 2) }}
                    <small>
                        <a
                            href=""
                            @click.prevent="toggleFormer()"
                        >
                            <span v-show="state.showFormer">
                                <i class="fas fa-fw fa-eye" />
                            </span>
                            <span v-show="!state.showFormer">
                                <i class="fas fa-fw fa-eye-slash" />
                            </span>
                        </a>
                    </small>
                </h5>
                <div
                    v-if="state.showFormer"
                    class="row gy-1"
                >
                    <div
                        v-for="contributor in contributors.former"
                        :key="contributor.name"
                        class="col-md-6 d-flex flex-column align-items-start"
                    >
                        <span>
                            {{ contributor.name }}
                        </span>
                        <span class="badge bg-secondary text-line">
                            {{ transJoin(contributor.roles) }}
                        </span>
                    </div>
                </div>
                <hr>
                <div class="d-flex flex-row justify-content-between">
                    <!-- eslint-disable-next-line vue/no-v-html -->
                    <span v-html="t('main.about.build_info')" />
                    <div>
                        <a
                            href="https://www.facebook.com/esciencecenter"
                            target="_blank"
                        >
                            <i class="fab fa-facebook fa-2x text-primary" />
                        </a>
                        <a
                            href="https://github.com/DH-Center-Tuebingen/Spacialist"
                            target="_blank"
                            class="ms-2"
                        >
                            <i class="fab fa-github fa-2x text-dark" />
                        </a>
                    </div>
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
    } from 'vue';
    import { useI18n } from 'vue-i18n';

    import store from '@/bootstrap/store.js';

    import {
        date,
        datestring,
        join,
    } from '@/helpers/filters.js';

    import {
        getContributors,
    } from '@/helpers/globals.js';

    export default {
        emits: ['closing'],
        setup(props, context) {
            const { t } = useI18n();

            // FUNCTIONS
            const toggleFormer = _ => {
                state.showFormer = !state.showFormer;
            };
            const transJoin = roles => {
                return join(roles.map(rn => t(`main.about.roles.${rn}`)));
            };
            const closeModal = _ => {
                context.emit('closing', false);
            };

            // DATA
            const contributors = getContributors();
            const state = reactive({
                showFormer: false,
                version: computed(_ => store.getters.version),
            });

            // RETURN
            return {
                t,
                // HELPERS
                date,
                datestring,
                // PROPS
                // LOCAL
                contributors,
                toggleFormer,
                transJoin,
                closeModal,
                // STATE
                state,
            }
        },
    }
</script>
