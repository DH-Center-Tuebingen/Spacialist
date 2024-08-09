<template>
    <div>
        <div
            v-if="item.result_type == 'u'"
            class="d-flex flex-row gap-1 align-items-center"
        >
            <i class="fas fa-fw fa-user opacity-50 me-2" />

            <a
                href="#"
                style="line-height: 0;"
                @click.prevent="showUserInfo(state.user)"
            >
                <user-avatar
                    :user="state.user"
                    :size="24"
                />
            </a>

            <span>{{ item.name }}</span>
            <span class="text-muted">{{ item.nickname }}</span>
        </div>
        <div
            v-else-if="item.result_type == 'wg'"
            class="d-flex flex-row gap-2 align-items-center"
        >
            <i class="fas fa-fw fa-users opacity-50 me-2" />

            <span :title="item.description">
                {{ item.display_name }}
            </span>
        </div>
    </div>
</template>

<script>
    import {
        computed,
        reactive,
        toRefs,
    } from 'vue';

    import { useI18n } from 'vue-i18n';

    import {
        getUserBy,
    } from '@/helpers/helpers.js';

    import {
        showUserInfo,
    } from '@/helpers/modal.js';

    export default {
        props: {
            item: {
                required: true,
                type: Object,
            },
        },
        setup(props, context) {
            const { t } = useI18n();
            const {
                item,
            } = toRefs(props);

            // FUNCTIONS

            // DATA
            const state = reactive({
                isUser: computed(_ => item.value.result_type == 'u'),
                user: computed(_ => {
                    if(!state.isUser) return {};

                    return getUserBy(item.value.id);
                }),
            });

            // RETURN
            return {
                t,
                // HELPERS
                showUserInfo,
                // LOCAL
                // STATE
                state,
            }
        },
    }
</script>