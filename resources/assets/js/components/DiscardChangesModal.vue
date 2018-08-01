<template>
    <modal :name="name" height="auto" :scrollable="true" @before-open="init">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Unsaved Changes</h5>
                <button type="button" class="close" aria-label="Close" @click="cancel">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <p class="alert alert-info">
                    There are unsaved changes in {{ entityName }}. Do you really want to continue and discard these changes?
                </p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-warning" @click="discard">
                    <i class="fas fa-fw fa-undo"></i> Yes, Discard Changes
                </button>
                <button type="button" class="btn btn-success" @click="save">
                    <i class="fas fa-fw fa-check"></i> No, Save and continue
                </button>
                <button type="button" class="btn btn-secondary" @click="cancel">
                    <i class="fas fa-fw fa-times"></i> Cancel
                </button>
            </div>
        </div>
    </modal>
</template>

<script>
    export default {
        props: {
            name: {
                required: true,
                type: String
            }
        },
        methods: {
            init(event) {
                Vue.set(this, 'onDiscard', event.params.onDiscard);
                Vue.set(this, 'onSave', event.params.onSave);
                Vue.set(this, 'onCancel', event.params.onCancel);
                Vue.set(this, 'entityName', event.params.entityName);
            },
            discard() {
                if(this.onDiscard) this.onDiscard();
                this.hide();
            },
            save() {
                if(this.onSave) this.onSave();
                this.hide();
            },
            cancel() {
                if(this.onCancel) this.onCancel();
                this.hide();
            },
            hide() {
                this.$modal.hide(this.name);
            }
        },
        data() {
            return {
                onDiscard: undefined,
                onSave: undefined,
                onCancel: undefined,
                entityName: "",
            }
        }
    }
</script>
