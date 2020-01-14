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
                :type="'entity'"
                @rules-modified="updateAccessRules">
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
        updateAccessRules(event) {
            switch(event.action) {
                case 'add':
                    this.entity.access_rules_count++;
                    break;
                case 'delete':
                    this.entity.access_rules_count--;
                    break;
            }
        }
    },
    data() {
        return {
        }
    }
}
</script>
