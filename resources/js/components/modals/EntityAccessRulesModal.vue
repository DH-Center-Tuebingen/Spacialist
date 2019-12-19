<template>
    <div class="modal-content">
        <div class="modal-header">
            <h5 class="modal-title">{{ $t('main.group.modal.define.title') }}</h5>
            <button type="button" class="close" aria-label="Close" @click="$emit('close')">
                <span aria-hidden="true">&times;</span>
            </button>
        </div>
        <div class="modal-body">
            <group-restrictions
                :model="entity"
                :type="'entity'">
            </group-restrictions>
        </div>
        <div class="modal-footer">
            <button type="button" class="btn btn-secondary" @click="$emit('close')">
                {{ $t('global.close') }}
            </button>
        </div>
    </div>
</template>

<script>
export default {
    name: 'EntityAccessRules',
    props: {
        entity: {
            required: true,
            type: Object
        },
    },
    methods: {
    },
    data() {
        return {
        }
    }
}
</script>
