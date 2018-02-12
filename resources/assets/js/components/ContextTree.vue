<template>
    <div class="container">
        <tree-view :model="contexts" :category="category" :selection="selection" :onSelect="onSelect" :strategies="strategies" :display="display"></tree-view>
        <h1 v-if="selection[0]">{{selection[0].name}}</h1>
        <h1 v-else>Nothing selected</h1>
    </div>
</template>

<script>
    export default {
        props: ['contexts'],
        mounted() {
            console.log('Component mounted.')
        },
        methods: {
            onSelect(newSelection) {
                this.selection = newSelection
            }
        },
        data() {
            return {
                selection: [],
                strategies: {
                    selection: ["single"],
                    click: ["select", "toggle-fold"],
                    fold: ["opener-control"]
                },
                category: "child_contexts",
                // Move 'display' here
                display: (item, inputs) => {
                    let content;
                    let prefix = <i class="svg-inline--fa fa-w-6"></i>
                    if(item.child_contexts && item.child_contexts.length) {
                        prefix = <i class="fas fa-caret-right"></i>
                    }
                    if(inputs.selection[0] && inputs.selection[0].id == item.id) {
                        content = <strong>{item.name}</strong>
                    } else {
                        content = item.name
                    }
                    return (
                        <span>
                            {prefix} {content}
                        </span>
                    )
                }
            }
        }
    }
</script>
