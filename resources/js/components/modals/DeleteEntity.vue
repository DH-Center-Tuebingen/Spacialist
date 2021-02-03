<template>
    <div class="modal-content">
        <div class="modal-header">
            <h5 class="modal-title">{{ $t('global.delete-name.title', {name: entity.name}) }}</h5>
            <button type="button" class="btn-close" aria-label="Close" @click="$emit('close')">
            </button>
        </div>
        <div class="modal-body">
            <p class="alert alert-info">
                {{ $t('global.delete-name.desc', {name: entity.name}) }}</i>
            </p>
        </div>
        <div class="modal-footer">
            <button type="button" class="btn btn-danger" @click="remove(entity)">
                <i class="fas fa-fw fa-check"></i> {{ $t('global.delete') }}
            </button>
            <button type="button" class="btn btn-secondary" @click="$emit('close')">
                <i class="fas fa-fw fa-times"></i> {{ $t('global.cancel') }}
            </button>
        </div>
    </div>
</template>

<script>
export default {
    props: {
        entity: {
            required: true,
            type: Object
        },
        onDelete: {
            required: true,
            type: Function
        }
    },
    methods: {
        remove(entity) {
            this.onDelete(entity);
            this.$emit('close');
        }
    }
}
</script>
