<template>
    <multiselect
        v-model="v.value"
        :classes="multiselectResetClasslist"
        :value-prop="'id'"
        :label="'name'"
        :track-by="['nickname', 'name', 'email']"
        :object="false"
        :mode="'tags'"
        :disabled="disabled"
        :options="users"
        :close-on-select="false"
        :searchable="true"
        :name="name"
        :placeholder="t('global.select.placeholder')"
        @change="v.handleChange"
    >
        <template #option="{ option }">
            <user-avatar
                class="align-middle"
                :user="option"
                :size="20"
            />
            <span class="align-middle ms-2">
                {{ option.name }} <span class="text-muted">{{ option.nickname }}</span>
            </span>
        </template>
        <template #tag="{ option, handleTagRemove, disabled: tagDisabled }">
            <div class="multiselect-tag multiselect-tag-user bg-opacity-25 text-muted px-1">
                <a
                    href="#"
                    class="text-nowrap text-reset text-decoration-none"
                    :title="option.nickname"
                    @click.prevent="showUserInfo(option)"
                >
                    <user-avatar
                        class="align-middle"
                        :user="option"
                        :size="18"
                    />
                    <span class="align-middle ms-2">
                        {{ option.name }}
                    </span>
                </a>
                <span
                    v-if="!tagDisabled"
                    class="multiselect-tag-remove"
                    @click.prevent
                    @mousedown.prevent.stop="handleTagRemove(option, $event)"
                >
                    <span class="multiselect-tag-remove-icon" />
                </span>
            </div>
        </template>
    </multiselect>
</template>

<script>
    import {
        computed,
        reactive,
        watch,
    } from 'vue';

    import { useI18n } from 'vue-i18n';

    import { useField } from 'vee-validate';

    import * as yup from 'yup';

    import useUserStore from '@/bootstrap/stores/user.js';

    import {
        multiselectResetClasslist,
        sortAlphabeticallyBy,
    } from '@/helpers/helpers.js';

    import {
        showUserInfo,
    } from '@/helpers/modal.js';

    export default {
        props: {
            name: {
                type: String,
                required: true,
            },
            disabled: {
                type: Boolean,
                required: false,
                default: false,
            },
            value: {
                type: Object,
                required: true,
            },
        },
        emits: ['change'],
        setup(props, context) {
            const { t } = useI18n();
            const userStore = useUserStore();
            // FETCH

            // FUNCTIONS
            const resetFieldState = _ => {
                v.resetField({
                    value: props.value || []
                });
            };
            const undirtyField = _ => {
                v.resetField({
                    value: v.value,
                });
            };

            // DATA
            const {
                handleChange,
                value: fieldValue,
                meta,
                resetField,
            } = useField(`userlist_${name.value}`, yup.mixed(), {
                initialValue: props.value || [],
            });
            const users = userStore.users.toSorted(sortAlphabeticallyBy());
            const v = reactive({
                value: fieldValue,
                handleChange,
                meta,
                resetField,
            });

            watch(_ => props.value, (newValue, oldValue) => {
                resetFieldState();
            });
            watch(_ => v.value, (newValue, oldValue) => {
                // only emit @change event if field is validated (required because Entity.vue components)
                // trigger this watcher several times even if another component is updated/validated
                if(!v.meta.validated) return;
                context.emit('change', {
                    dirty: v.meta.dirty,
                    valid: v.meta.valid,
                    value: v.value,
                });
            });

            // RETURN
            return {
                t,
                // HELPERS
                multiselectResetClasslist,
                showUserInfo,
                // LOCAL
                resetFieldState,
                undirtyField,
                // PROPS
                // STATE
                users,
                v,
            };
        },
    };
</script>
