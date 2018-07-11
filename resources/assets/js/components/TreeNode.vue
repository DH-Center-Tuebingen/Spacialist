<template>
    <div @dragenter="onDragEnter" @dragleave="onDragLeave">
        <i class="fas fa-fw fa-spa"></i>
        <span>{{data.name}}</span>
        <span class="pl-1 font-italic mb-0" v-if="data.context_type_id">
            {{ $translateConcept($getEntityType(data.context_type_id).thesaurus_url) }}
        </span>
        <span class="pl-1" v-show="data.children_count">
            ({{ data.children_count }})
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
                this.asyncToggle.clear();
                this.asyncToggle();
            },
            onDragLeave(item) {
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
        }
    }
</script>
