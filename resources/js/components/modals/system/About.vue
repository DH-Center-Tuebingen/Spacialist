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
                <h5 class="mb-3">
                    {{ t('main.about.contributor', 2) }}
                </h5>
                <div class="row gy-2">
                    <Contributor
                        v-for="contributor in contributors.active"
                        :key="contributor.name"
                        :value="contributor"
                    />
                </div>
                <hr>
                <Collapsible
                    :header-classes="['pt-2', 'pb-2']"
                    :body-classes="['row']"
                >
                    <template #title>
                        <h5>
                            {{ t('main.about.former_contributor', 2) }}
                        </h5>
                    </template>

                    <Contributor
                        v-for="contributor in contributors.former"
                        :key="contributor.name"
                        :value="contributor"
                    />
                </Collapsible>
            </div>
            <div class="modal-footer d-flex justify-content-between">
                <div class="d-flex align-items-center gap-2">
                    <a
                        href="https://github.com/DH-Center-Tuebingen/Spacialist"
                        target="_blank"
                        class="ms-2"
                    >
                        <i class="fab fa-github fa-2x text-dark" />
                    </a>
                    <span v-html="t('main.about.build_info')" />
                </div>
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

    import Collapsible from '@/components/structure/Collapsible.vue';
    import Contributor from '@/components/user/Contributor.vue';

    export default {
        components: {
            Collapsible,
            Contributor
        },
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
            };
        }
    };
</script>