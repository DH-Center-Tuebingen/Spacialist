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
        <span @contextmenu.prevent="$refs.menu.open($event, data)">
            {{ data.name }}
        </span>

        <vue-context
            ref="menu"
            :close-on-click="true"
            :lazy="true">
            <template slot-scope="child">
                <li>
                    <a href="#" class="dropdown-item" :class="actionClassStyles" @click.stop.prevent="actionWrapper(child.data.onContextMenuAdd, child.data)" @dblclick.stop.prevent>
                        <i class="fas fa-fw fa-plus text-success"></i> Add new Sub-Entity to <span class="font-italic">{{ child.data.name }}</span>
                    </a>
                </li>
                <li>
                    <a href="#" class="dropdown-item" :class="actionClassStyles" @click.stop.prevent="actionWrapper(child.data.onContextMenuDuplicate, child.data)" @dblclick.stop.prevent>
                        <i class="fas fa-fw fa-copy text-info"></i> Duplicate <span class="font-italic">{{ child.data.name }}</span>
                    </a>
                </li>
                <li>
                    <a href="#" class="dropdown-item" :class="actionClassStyles" @click.stop.prevent="actionWrapper(child.data.onContextMenuDelete, child.data)" @dblclick.stop.prevent>
                        <i class="fas fa-fw fa-trash text-danger"></i> Delete <span class="font-italic">{{ child.data.name }}</span>
                    </a>
                </li>
            </template>
        </vue-context>
    </div>
</template>

<script>
    import debounce from 'debounce';
    import { VueContext } from 'vue-context';

    export default {
        components: { VueContext },
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
            },
            actionWrapper(action, data) {
                if(!data.hasWriteAccess) return;

                return action(data);
            },
            doToggle() {
                if(!this.data.state.opened && this.data.state.openable) {
                    this.data.onToggle({data: this.data});
                }
            },
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
            },
            actionClassStyles() {
                return {
                    'text-muted': !this.data.hasWriteAccess
                };
            }
        }
    }
</script>
