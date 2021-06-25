<template>
    <div class="row h-100" v-if="initFinished">
        <div class="col-md-2 h-100 d-flex flex-column border-end">
            <h5>{{ $tc('main.map.baselayer', 2) }}</h5>
            <layer-list
                class="flex-grow-1 scroll-y-auto"
                :add-new="_ => onAddNewLayer(false)"
                :layer="baselayer"
                :on-select="onLayerSelect"
                :on-delete="requestDeleteLayer">
            </layer-list>
            <h5 class="mt-3">{{ $tc('main.map.overlay', 2) }}</h5>
            <layer-list
                class="flex-grow-1 scroll-y-auto"
                :add-new="_ => onAddNewLayer(true)"
                :layer="overlays"
                :on-select="onLayerSelect"
                :on-delete="requestDeleteLayer">
            </layer-list>
        </div>
        <div class="col-md-6 h-100">
            <router-view @request-delete="handleLayerDeleteRequest"></router-view>
        </div>

        <modal name="delete-layer-modal" height="auto" :scrollable="true">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">{{ $t('global.delete_name.title', {name: getTitle(selectedLayer)}) }}</h5>
                    <button type="button" class="btn-close" aria-label="Close" @click="hideDeleteLayerModal">
                    </button>
                </div>
                <div class="modal-body">
                    <p class="alert alert-info">
                        {{ $t('global.delete_name.desc', {name: getTitle(selectedLayer)}) }}</i>
                    </p>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-danger" @click="deleteLayer(selectedLayer)">
                        <i class="fas fa-fw fa-check"></i> {{ $t('global.delete') }}
                    </button>
                    <button type="button" class="btn btn-secondary" @click="hideDeleteLayerModal">
                        <i class="fas fa-fw fa-times"></i> {{ $t('global.cancel') }}
                    </button>
                </div>
            </div>
        </modal>
    </div>
</template>

<script>
    export default {
        beforeRouteEnter(to, from, next) {
            $httpQueue.add(() => $http.get('map/layer').then(response => {
                next(vm => vm.init(response.data));
            }));
        },
        mounted() {},
        methods: {
            init(layers) {
                this.initFinished = false;
                this.baselayer = layers.baselayers;
                this.overlays = layers.overlays;
                this.initFinished = true;
            },
            onAddNewLayer(is_overlay) {
                const vm = this;
                const data = {
                    name: vm.$t('plugins.map.layer-editor.unnamed-layer'),
                    is_overlay: is_overlay
                };
                $httpQueue.add(() => $http.post('/map/layer', data).then(response => {
                    if(is_overlay) {
                        this.overlays.push(response.data);
                    } else {
                        this.baselayer.push(response.data);
                    }
                    this.onLayerSelect(response.data);
                }));
            },
            onLayerSelect(layer) {
                this.$router.push({
                    name: 'ldetail',
                    params: {
                        id: layer.id
                    }
                });
            },
            handleLayerDeleteRequest(e) {
                this.requestDeleteLayer(e.layer);
            },
            requestDeleteLayer(layer) {
                this.selectedLayer = {...layer};
                this.$modal.show('delete-layer-modal');
            },
            deleteLayer(layer) {
                if(layer.entity_type_id || layer.type == 'unlinked') {
                    return;
                }
                $http.delete(`map/layer/${layer.id}`).then(response => {
                    let arr;
                    if(layer.is_overlay) {
                        arr = this.overlays;
                    } else {
                        arr = this.baselayer;
                    }
                    const index = arr.findIndex(l => l.id == layer.id);
                    if(index > -1) {
                        arr.splice(index, 1);
                    }
                    this.$showToast('Layer deleted', 'Layer successfully deleted.', 'success');
                    this.hideDeleteLayerModal();
                    // Redirect to Layer Editor, if deleted layer is also
                    // selected layer
                    if(layer.id == this.$route.params.id) {
                        this.$router.push({
                            name: 'layeredit'
                        });
                    }
                });
            },
            hideDeleteLayerModal() {
                this.$modal.hide('delete-layer-modal');
                this.selectedLayer = {};
            },
            getTitle(layer) {
                if(layer.name) {
                    return layer.name
                }
                if(layer.entity_type) {
                    return this.$translateConcept(layer.entity_type.thesaurus_url);
                }
                return this.$t('plugins.map.untitled');
            }
        },
        data() {
            return {
                initFinished: false,
                selectedLayer: {},
                baselayer: [],
                overlays: []
            }
        }
    }
</script>
