<template>
    <div @dragenter="onDragEnter" @dragleave="onDragLeave" :id="`tree-node-${data.id}`">
        <a href="" @click.prevent="" @contextmenu.stop.prevent="togglePopup" class="text-body text-decoration-none">
            <span style="display: inline-block; text-align: center;" class="px-1">
                <span v-if="data.children_count" class="badge rounded-pill" style="font-size: 9px;" :style="state.colorStyles" :title="data.children_count">
                    {{ numPlus(data.children_count, 3) }}
                </span>
                <span v-else class="badge rounded-pill" style="font-size: 8px;" :style="state.colorStyles">
                    &nbsp;&nbsp;
                </span>
            </span>
            <span :class="{'fw-bold': state.isSelected}">
                {{ data.name }}
            </span>
        </a>
        <ul class="dropdown-menu" :id="`tree-node-${data.id}-contextmenu`">
            <li><a class="dropdown-item" href="#">Action for {{ data.name }}</a></li>
            <li><a class="dropdown-item" href="#">Another action</a></li>
            <li><a class="dropdown-item" href="#">Something else here</a></li>
            <li><hr class="dropdown-divider"></li>
            <li><a class="dropdown-item" href="#">Separated link</a></li>
        </ul>
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
            const togglePopup = _ => {
                const ddElem = document.getElementById(`tree-node-${data.value.id}-contextmenu`);
                ddElem.classList.toggle('show');
            };

            // DATA
            const state = reactive({
                colorStyles: computed(_ => getEntityColors(data.value.entity_type_id)),
                isSelected: computed(_ => store.getters.entity.id === data.value.id),
            });

            // ON MOUNTED
            onMounted(_ => {
                console.log("tree node component mounted");
            });

            // RETURN
            return {
                // HELPERS
                numPlus,
                // LOCAL
                togglePopup,
                // PROPS
                data,
                // STATE
                state,
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
