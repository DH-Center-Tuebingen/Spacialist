<template>
    <div class="modal-content">
        <div class="modal-header">
            <h5 class="modal-title">
                {{
                    $t('main.entity.modals.edit.title', {
                        name: translateLabel(entityType, 'thesaurus_url')
                    })
                }}
            </h5>
            <button type="button" class="btn-close" aria-label="Close" @click="$emit('close')">
            </button>
        </div>
        <div class="modal-body">
            <form id="editEntityTypeForm" name="editEntityTypeForm" role="form" v-on:submit.prevent="submit(entityType)">
                <div class="form-group row">
                    <label class="col-form-label col-md-3" for="label">
                        {{ $t('global.label') }}:
                    </label>
                    <div class="col-md-9">
                        <label-search
                            id="label"
                            :on-select="setLabel">
                        </label-search>
                    </div>
                </div>
            </form>
        </div>
        <div class="modal-footer">
            <button type="submit" class="btn btn-success" form="editEntityTypeForm">
                <i class="fas fa-fw fa-plus"></i> {{ $t('global.save') }}
            </button>
            <button type="button" class="btn btn-secondary" @click="$emit('close')">
                <i class="fas fa-fw fa-times"></i> {{ $t('global.cancel') }}
            </button>
        </div>
    </div>
</template>

<script>
export default {
    name: 'EditEntityType',
    props: {
        entityType: {
            required: true,
            type: Object
        },
        onSubmit: {
            required: true,
            type: Function
        }
    },
    methods: {
        setLabel(label) {
            if(!label || !label.concept) return;
            this.entityType.thesaurus_url = label.concept.concept_url;
        },
        translateLabel(element, prop) {
            return this.$translateLabel(element, prop);
        },
        submit(entity) {
            this.onSubmit(entity);
            this.$emit('close');
        }
    }
}
</script>
