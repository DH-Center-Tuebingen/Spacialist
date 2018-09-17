<template>
    <div class="modal-content">
        <div class="modal-header">
            <h5 class="modal-title">{{ $t('main.entity.modal.title') }}</h5>
            <button type="button" class="close" aria-label="Close" @click="$emit('close')">
                <span aria-hidden="true">&times;</span>
            </button>
        </div>
        <div class="modal-body">
            <form id="newEntityForm" name="newEntityForm" role="form" v-on:submit.prevent="submit(newEntity)">
                <div class="form-group row">
                    <label class="col-form-label col-md-3" for="name">
                        {{ $t('global.name') }}:
                    </label>
                    <div class="col-md-9">
                        <input type="text" id="name" class="form-control" required v-model="newEntity.name" />
                    </div>
                </div>
                <div class="form-group row">
                    <label class="col-form-label col-md-3" for="type">
                        {{ $t('global.type') }}:
                    </label>
                    <multiselect class="col-md-9" style="box-sizing: border-box;"
                        :customLabel="translateLabel"
                        label="thesaurus_url"
                        track-by="id"
                        v-model="newEntity.type"
                        :allowEmpty="false"
                        :closeOnSelect="true"
                        :hideSelected="true"
                        :multiple="false"
                        :options="newEntity.selection"
                        :placeholder="$t('global.select.placehoder')"
                        :select-label="$t('global.select.select')"
                        :deselect-label="$t('global.select.deselect')">
                    </multiselect>
                </div>
            </form>
        </div>
        <div class="modal-footer">
            <button type="submit" class="btn btn-success" form="newEntityForm">
                <i class="fas fa-fw fa-plus"></i> {{ $t('global.add') }}
            </button>
            <button type="button" class="btn btn-secondary" @click="$emit('close')">
                <i class="fas fa-fw fa-times"></i> {{ $t('global.cancel') }}
            </button>
        </div>
    </div>
</template>

<script>
export default {
    name: 'AddNewEntity',
    props: {
        newEntity: {
            required: false,
            type: Object
        },
        onSubmit: {
            required: true,
            type: Function
        }
    },
    methods: {
        translateLabel(element, prop) {
            return this.$translateLabel(element, prop);
        },
        submit(entity) {
            this.onSubmit(entity);
            this.$emit('close');
        }
    },
    watch: {
        'newEntity.type': function(val) {
            this.newEntity.entity_type_id = val.id;
        }
    }
}
</script>
