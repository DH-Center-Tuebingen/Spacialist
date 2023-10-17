<template>
    <div class="h-100 d-flex flex-column align-items-center justify-content-center">
        <div>
            <i class="far fa-fw fa-5x fa-sad-cry" />
        </div>
        <h2 class="display-2 fw-medium">
            {{ t('main.app.not_found.title') }}
        </h2>
        <!-- eslint-disable vue/no-v-html -->
        <p
            class="lead"
            v-html="t('main.app.not_found.msg', {site: currentRoute.path})"
        />
        <!-- eslint-enable vue/no-v-html -->
        <div class="mt-2 d-flex flex-row gap-4">
            <router-link
                class="btn btn-outline-primary"
                :to="{name: 'home'}"
            >
                <i class="fas fa-fw fa-house" />
                {{ t('main.app.not_found.go_to') }}
            </router-link>
            <router-link
                class="btn btn-outline-warning"
                :to="{path: currentRoute.path, params: currentRoute.params, query: currentRoute.query, force: true}"
            >
                <i class="fas fa-fw fa-redo" />
                {{ t('main.app.not_found.retry') }}
            </router-link>
        </div>
    </div>
</template>

<script>
    import { useI18n } from 'vue-i18n';
    import {
        useRoute,
    } from 'vue-router';

    export default {
        setup() {
            const { t } = useI18n();
            const currentRoute = useRoute();

            return {
                t,
                // HELPERS
                // LOCAL
                currentRoute,
                // PROPS
                // STATE
            };
        }
    }
</script>
