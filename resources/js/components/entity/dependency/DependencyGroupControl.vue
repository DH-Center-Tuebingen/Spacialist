<template>
    <div
        v-if="groupCount > 1"
        class="mb-2 row d-flex flex-row justify-content-end"
    >
        <div class="input-group input-group-sm w-auto">
            <button
                id="dependency-mode-toggle-btn"
                class="btn btn-sm btn-outline-secondary"
                type="button"
                @click="toggleState"
            >
                <span
                    v-show="isAnd"
                    :title="t('global.dependency.modes.union_desc')"
                >
                    <i class="fas fa-fw fa-object-ungroup" />
                    {{ t('global.dependency.modes.union') }}
                </span>
                <span
                    v-show="!isAnd"
                    :title="t('global.dependency.modes.intersect_desc')"
                >
                    <i class="fas fa-fw fa-object-group" />
                    {{ t('global.dependency.modes.intersect') }}
                </span>
            </button>
            <button
                class="btn btn-sm btn-outline-danger"
                type="button"
                :title="t('global.dependency.remove_group')"
                @click="remove()"
            >
                <i class="fas fa-fw fa-minus" />
            </button>
            <button
                class="btn btn-sm btn-outline-success"
                type="button"
                :disabled="lastGroupEmpty"
                :title="t('global.dependency.add_group')"
                @click="addGroup"
            >
                <i class="fas fa-fw fa-plus" />
            </button>
        </div>
        <div class="input-group input-group-sm w-auto">
            <button
                class="btn btn-sm btn-outline-secondary"
                type="button"
                @click="gotoPrevGroup"
            >
                <i class="fas fa-fw fa-chevron-left" />
            </button>
            <div class="input-group-text d-flex flex-row gap-2">
                {{ t('global.dependency.group') }}
                {{ state.currentDependencyGroupId + 1 }} / {{ groupCount }}
            </div>
            <button
                class="btn btn-sm btn-outline-secondary"
                type="button"
                @click="gotoNextGroup"
            >
                <i class="fas fa-fw fa-chevron-right" />
            </button>
        </div>
    </div>
    <div
        v-else
        class="mb-2 d-flex flex-row justify-content-end"
    >
        <button
            class="btn btn-sm btn-outline-success"
            type="button"
            :disabled="lastGroupEmpty"
            @click="addGroup"
        >
            <i class="fas fa-fw fa-plus" />
            {{ t('global.dependency.add_group') }}
        </button>
    </div>
</template>

<script>
    import { computed } from 'vue';
    import { useI18n } from 'vue-i18n';

    export default {
        props: {
            isAnd: {
                type: Boolean,
                default: true
            },
            groups: {
                type: Array,
                default: () => []
            }
        },
        emits: [
            'add',
            'next',
            'prev',
            'remove',
            'toggleState'
        ],
        setup(props, { emit }) {

            const groupCount = computed(_ => props.groups.length);
            const lastGroupEmpty = computed(_ => props.groups[groupCount.value - 1].rules.length == 0);

            return {
                t: useI18n().t,
                groupCount,
                lastGroupEmpty,
                addGroup: _ => emit('add'),
                gotoPrevGroup: _ => emit('prev'),
                gotoNextGroup: _ => emit('next'),
                remove: groupId => emit('remove'),
                toggleState: _ => emit('toggleState'),
            };
        }
    };
</script>