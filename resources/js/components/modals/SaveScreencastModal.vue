<template>
    <div class="modal-content">
        <div class="modal-header">
            <h5 class="modal-title">
                {{
                    $t('main.entity.modals.screencast.title')
                }}
            </h5>
            <button type="button" class="close" aria-label="Close" @click="$emit('close')">
                <span aria-hidden="true">&times;</span>
            </button>
        </div>
        <div class="modal-body">
            <form id="storeScreencastModal" name="storeScreencastModal" role="form" v-on:submit.prevent="submitLocal">
                <p class="alert alert-info">
                    {{ $t('main.entity.modals.screencast.info') }}
                    <dl class="row">
                        <dt class="col-md-6 text-right">
                            {{ $t('global.duration') }}
                        </dt>
                        <dd class="col-md-6">
                            {{ duration | time }}
                        </dd>
                        <dt class="col-md-6 text-right">
                            {{ $t('global.size') }}
                        </dt>
                        <dd class="col-md-6">
                            {{ content.size | bytes }}
                        </dd>
                        <dt class="col-md-6 text-right">
                            {{ $t('global.type') }}
                        </dt>
                        <dd class="col-md-6">
                            {{ content.type }}
                        </dd>
                    </dl>
                </p>
            </form>
        </div>
        <div class="modal-footer">
            <button type="submit" class="btn btn-success" form="storeScreencastModal">
                <i class="fas fa-fw fa-file-download"></i> {{ $t('main.entity.modals.screencast.actions.local.button') }}
            </button>
            <button type="button" class="btn btn-success" @click="submitServer" v-if="$hasPlugin('files')">
                <i class="fas fa-fw fa-file-upload"></i> {{ $t('main.entity.modals.screencast.actions.server.button') }}
            </button>
            <button type="button" class="btn btn-secondary" @click="$emit('close')">
                <i class="fas fa-fw fa-times"></i> {{ $t('global.cancel') }}
            </button>
        </div>
    </div>
</template>

<script>
export default {
    name: 'SaveScreencastModal',
    props: {
        content: {
            required: true,
            type: File
        },
        duration: {
            required: true,
            type: Number
        },
        storeLocal: {
            required: true,
            type: Function
        },
        storeServer: {
            required: true,
            type: Function
        }
    },
    methods: {
        submitLocal() {
            this.storeLocal();
            this.$emit('close');
        },
        submitServer() {
            this.storeServer();
            this.$emit('close');
        }
    }
}
</script>
