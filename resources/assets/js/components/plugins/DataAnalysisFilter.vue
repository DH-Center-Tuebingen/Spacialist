<template>
    <div>
        <div class="text-right">
            <button type="button" class="close" aria-label="Close" @click="closePopover">
                <span aria-hidden="true">&times;</span>
            </button>
        </div>
        <form role="form" class="mt-2" @submit.prevent="addFilter(filter)">
            <div class="form-group row">
                <label class="col-form-label col-md-3 text-right">Comp</label>
                <div class="col-md-9">
                    <input class="form-control" type="text" v-model="filter.comp" />
                </div>
            </div>
            <div class="form-group row">
                <label class="col-form-label col-md-3 text-right">Value</label>
                <div class="col-md-9">
                    <input class="form-control" type="text" v-model="filter.value" />
                </div>
            </div>
            <button type="submit" class="btn btn-outline-success">
                Add Filter
            </button>
        </form>
    </div>
</template>

<script>
    export default {
        props: {
            concepts: {
                required: true,
                validator: Vue.$validateObject
            },
            onAdd: {
                required: false,
                type: Function,
                default: _ => {}
            }
        },
        mounted() {},
        methods: {
            closePopover() {

            },
            addFilter(filter) {
                this.onAdd(filter);
            }
        },
        data() {
            return {
                filter: {

                }
            }
        },
        computed: {
        }
    }
</script>
