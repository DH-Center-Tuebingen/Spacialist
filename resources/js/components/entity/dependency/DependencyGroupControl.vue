<template>
    <div
        v-if="groupCount > 1"
        class="row d-flex flex-row justify-content-end"
    >
        <div class="input-group input-group-sm w-auto">
            <DependencyToggle
                :model-value="isOr"
                @click="toggleState"
            />
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
                {{ activeGroup + 1 }} / {{ groupCount }}
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
        class="d-flex flex-row justify-content-end"
    >
        <button
            class="btn btn-sm btn-outline-success text-nowrap"
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
    import DependencyToggle from './DependencyToggle.vue';

    export default {
        components: {
            DependencyToggle,
        },
        props: {
            isOr: {
                type: Boolean,
                default: true
            },
            activeGroup: {
                type: Number,
                default: 0
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
            const { t } = useI18n();

            const groupCount = computed(_ => props.groups.length);
            const lastGroupEmpty = computed(_ => props.groups[groupCount.value - 1].rules.length == 0);

            return {
                t,
                groupCount,
                lastGroupEmpty,
                addGroup: _ => emit('add'),
                gotoPrevGroup: _ => emit('prev'),
                gotoNextGroup: _ => emit('next'),
                remove: _ => emit('remove', props.activeGroup),
                toggleState: _ => emit('toggleState'),
            };
        }
    };
</script>