<template>
    <div class="collapsible">
        <header
            class="card-header d-flex justify-content-between user-select-none clickable"
            :class="headerClasses"
            @click="() => collapsed = !collapsed"
        >
            <span>
                <slot name="title" />
            </span>
            <span>
                <span v-show="collapsed">
                    <i class="fas fa-fw fa-plus" />
                </span>
                <span v-show="!collapsed">
                    <i class="fas fa-fw fa-minus" />
                </span>

            </span>
        </header>
        <slot name="between" />
        <div
            v-show="!collapsed"
            :class="bodyClasses"
        >
            <slot />
        </div>
    </div>
</template>

<script>
    import {
        ref,
    } from 'vue';

    export default {
        props: {
            bodyClasses: {
                type: Array,
                required: false,
                default: () => ([]),
            },
            headerClasses: {
                type: Array,
                required: false,
                default: () => ([]),
            },
        },
        setup() {
            const collapsed = ref(true);

            return {
                collapsed,
            };
        }
    };
</script>