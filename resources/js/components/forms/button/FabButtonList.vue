<template>
    <div
        class="ms-auto btn-fab-list btn-fab-list-sm bg-white z-1 rounded px-2 py-1 d-flex flex-align-center"
    >
        <button
            v-for="(value, index) in buttons"
            :key="index"
            class="btn btn-sm btn-fab"
            :class="getButtonClasses(value.classes)"
            @click="value.action(entry)"
        >
            <i
                class="fa-fw"
                :class="value.icon"
                :title="value.title"
                style="vertical-align: 0;"
            ></i>
        </button>
    </div>
</template>

<script>
    export default {
        props: {
            /**
             * Array of button definitions
             * 
             * @param {Object[]} buttons - Each button definition should include:
             * @param {string} buttons[].icon - FontAwesome icon class
             * @param {string} buttons[].title - Tooltip text for the button
             * @param {string} buttons[].classes - CSS classes for styling the button
             * @param {Function} buttons[].action - Function to execute on button click
             */
            buttons: {
                type: Array,
                required: true,
                default: () => ([])
            }
        },
        setup() {
            const getButtonClasses = (classes) => {
                if(!classes || classes.length === 0) {
                    return 'btn-outline-secondary';
                }
                return classes;
            };

            return {
                getButtonClasses,
            };
        },
    };
</script>

<style lang='scss' scoped></style>