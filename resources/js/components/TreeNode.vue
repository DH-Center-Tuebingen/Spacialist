<template>
    <div @dragenter="onDragEnter" @dragleave="onDragLeave" :id="`tree-node-${data.id}`">
        <span style="width: 2em; display: inline-block; text-align: center;">
            <span class="badge rounded-pill" style="font-size: 9px;" :style="state.colorStyles" :title="data.children_count">
                <template v-if="data.children_count">
                    {{ numPlus(data.children_count, 3) }}
                </template>
                <template v-else>
                    &nbsp;
                </template>
            </span>
        </span>
        <span :class="{'fw-bold': state.isSelected}">
            {{ data.name }}
        </span>
    </div>
</template>

<script>
    import {
        computed,
        onMounted,
        reactive,
        toRefs,
    } from 'vue';

    import store from '../bootstrap/store.js';

    import {
        getEntityColors
    } from '../helpers/helpers.js';
    import {
        numPlus
    } from '../helpers/filters.js';

    export default {
        props: {
            data: {
                required: true,
                type: Object
            }
        },
        setup(props) {
            const {
                data
            } = toRefs(props);
            // FETCH

            // FUNCTIONS

            // DATA
            const state = reactive({
                colorStyles: getEntityColors(data.value.entity_type_id),
                isSelected: computed(_ => store.getters.entity.id === data.value.id),
            });

            // ON MOUNTED
            onMounted(_ => {
                console.log("tree node component mounted");
            });

            // RETURN
            return {
                numPlus,
                state,
                data,
            };
        }
        // methods: {
        //     onDragEnter() {
        //         if(!this.data.dragAllowed()) return;
        //         this.asyncToggle.cancel();
        //         this.asyncToggle();
        //     },
        //     onDragLeave(item) {
        //         this.asyncToggle.cancel();
        //     },
        //     doToggle() {
        //         if(!this.data.state.opened && this.data.state.openable) {
        //             this.data.onToggle({data: this.data});
        //         }
        //     }
        // },
        // data() {
        //     return {
        //     }
        // },
        // computed: {
        //     asyncToggle() {
        //         return _debounce(this.doToggle, this.data.dragDelay || 500);
        //     },
        //     colorStyles() {
        //         const colors = this.$getEntityColors(this.data.entity_type_id);
        //         if(this.data.children_count) {
        //             return colors;
        //         } else {
        //             return {
        //                 color: colors.backgroundColor
        //             };
        //         }
        //     }
        // }
    }
</script>
