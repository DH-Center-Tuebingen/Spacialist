<template>
    <div @dragenter="onDragEnter" @dragleave="onDragLeave" :id="`tree-node-${data.id}`">
        <span style="width: 2em; display: inline-block; text-align: center;">
            <span v-show="data.children_count" class="badge badge-pill" style="font-size: 9px;" :style="colorStyles" :title="data.children_count">
                {{ data.children_count | numPlus(3) }}
            </span>
            <span v-show="!data.children_count" :style="colorStyles">
                <i class="fas fa-circle fa-sm"></i>
            </span>
        </span>
        <span>
            {{ data.name }}
        </span>
    </div>
</template>

<script>
    import debounce from 'debounce';

    export default {
        props: {
            data: {
                required: true,
                type: Object
            }
        },
        methods: {
            onDragEnter() {
                if(!this.data.dragAllowed()) return;
                this.asyncToggle.clear();
                this.asyncToggle();
            },
            onDragLeave(item) {
                this.asyncToggle.clear();
            },
            doToggle() {
                if(!this.data.state.opened && this.data.state.openable) {
                    this.data.onToggle({data: this.data});
                }
            }
        },
        data() {
            return {
            }
        },
        computed: {
            asyncToggle() {
                return debounce(this.doToggle, this.data.dragDelay || 500);
            },
            colorStyles() {
                const colors = this.$getEntityColors(this.data.entity_type_id);
                if(this.data.children_count) {
                    return colors;
                } else {
                    return {
                        color: colors.backgroundColor
                    };
                }
            }
        }
    }
</script>
