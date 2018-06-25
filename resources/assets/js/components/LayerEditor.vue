<template>
    <div class="row h-100">
        <div class="col-md-3 h-100 d-flex flex-column">
            <h5>Baselayer</h5>
            <layer
                class="flex-grow-1 scroll-y-auto"
                :add-new="_ => onAddNewLayer(false)"
                :layer="baselayer"
                :on-select="onLayerSelect">
            </layer>
            <h5 class="mt-3">Overlays</h5>
            <layer
                class="flex-grow-1 scroll-y-auto"
                :add-new="_ => onAddNewLayer(true)"
                :layer="overlays"
                :on-select="onLayerSelect">
            </layer>
        </div>
        <div class="col-md-6 h-100">
            <p class="alert alert-info" v-if="!selectedLayer.id">
                Please select a layer
            </p>
            <div v-else class="h-100 d-flex flex-column">
                <div class="d-flex flex-row justify-content-between mb-2">
                    <h5>Properties of {{ getTitle(selectedLayer) }}</h5>
                    <button type="submit" form="layer-form" class="btn btn-success">
                        <i class="fas fa-fw fa-save"></i> Save
                    </button>
                </div>
                <form role="form" id="layer-form" class="col pl-0 scroll-y-auto scroll-x-hidden" @submit.prevent="updateLayer(selectedLayer, originalLayer)">
                    <div v-if="selectedLayer.context_type_id || selectedLayer.type == 'unlinked'">
                        <div class="form-group row">
                            <label for="color" class="col-md-4 col-form-label">
                                Color:
                            </label>
                            <div class="col-md-8">
                                <input type="color" id="color" class="form-control" v-model="selectedLayer.color" />
                            </div>
                        </div>
                    </div>
                    <div v-else>
                        <div class="form-group row">
                            <label for="name" class="col-md-4 col-form-label">
                                Name:
                            </label>
                            <div class="col-md-8">
                                <input type="text" id="name" class="form-control" v-model="selectedLayer.name" />
                            </div>
                        </div>
                        <div class="form-group row">
                            <label for="url" class="col-md-4 col-form-label">
                                URL:
                            </label>
                            <div class="col-md-8">
                                <input type="text" id="url" class="form-control" v-model="selectedLayer.url" />
                            </div>
                        </div>
                        <div class="form-group row">
                            <label for="type" class="col-md-4 col-form-label">
                                Type:
                            </label>
                            <div class="col-md-8">
                                <input type="text" id="type" class="form-control" v-model="selectedLayer.type" />
                            </div>
                        </div>
                        <div class="form-group row">
                            <label for="subdomains" class="col-md-4 col-form-label">
                                Subdomains:
                            </label>
                            <div class="col-md-8">
                                <input type="text" id="subdomains" class="form-control" v-model="selectedLayer.subdomains" />
                            </div>
                        </div>
                        <div class="form-group row">
                            <label for="attribution" class="col-md-4 col-form-label">
                                Attribution:
                            </label>
                            <div class="col-md-8">
                                <textarea id="attribution" class="form-control" v-model="selectedLayer.attribution"></textarea>
                            </div>
                        </div>
                        <div class="form-group row">
                            <label for="layers" class="col-md-4 col-form-label">
                                Layers:
                            </label>
                            <div class="col-md-8">
                                <input type="text" id="layers" class="form-control" v-model="selectedLayer.layers" />
                            </div>
                        </div>
                        <div class="form-group row">
                            <label for="styles" class="col-md-4 col-form-label">
                                Styles:
                            </label>
                            <div class="col-md-8">
                                <textarea id="styles" class="form-control" v-model="selectedLayer.styles"></textarea>
                            </div>
                        </div>
                        <div class="form-group row">
                            <label for="format" class="col-md-4 col-form-label">
                                Format:
                            </label>
                            <div class="col-md-8">
                                <textarea id="format" class="form-control" v-model="selectedLayer.format"></textarea>
                            </div>
                        </div>
                        <div class="form-group row">
                            <label for="version" class="col-md-4 col-form-label">
                                Version:
                            </label>
                            <div class="col-md-8">
                                <input type="text" id="version" class="form-control" v-model="selectedLayer.version" />
                            </div>
                        </div>
                        <div class="form-group row">
                            <label for="overlay" class="col-md-4 col-form-label">
                                Overlay:
                            </label>
                            <div class="col-md-8">
                                <input type="checkbox" id="overlay" class="form-control" v-model="selectedLayer.is_overlay" />
                            </div>
                        </div>
                        <div class="form-group row">
                            <label for="api-key" class="col-md-4 col-form-label">
                                API-Key:
                            </label>
                            <div class="col-md-8">
                                <input type="text" id="api-key" class="form-control" v-model="selectedLayer.api_key" />
                            </div>
                        </div>
                        <div class="form-group row">
                            <label for="layer-type" class="col-md-4 col-form-label">
                                Layer Type:
                            </label>
                            <div class="col-md-8">
                                <input type="text" id="layer-type" class="form-control" v-model="selectedLayer.layer_type" />
                            </div>
                        </div>
                        <div class="form-group row">
                            <label for="color" class="col-md-4 col-form-label">
                                Color:
                            </label>
                            <div class="col-md-8">
                                <input type="color" id="color" class="form-control" v-model="selectedLayer.color" />
                            </div>
                        </div>
                    </div>
                    <div class="form-group row">
                        <label for="visible" class="col-md-4 col-form-label">
                            Visible:
                        </label>
                        <div class="col-md-8">
                            <input type="checkbox" id="visible" class="form-control" v-model="selectedLayer.visible" />
                        </div>
                    </div>
                    <div class="form-group row">
                        <label for="opacity" class="col-md-4 col-form-label">
                            Opacity:
                        </label>
                        <div class="col-md-8 d-flex">
                            <input type="range" id="opacity" class="form-control" step="0.01" min="0" max="1" value="0" v-model="selectedLayer.opacity"/>
                            <span class="ml-3">{{ selectedLayer.opacity }}</span>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
</template>

<script>
    export default {
        props: {
            baselayer: {
                type: Array,
                required: true
            },
            overlays: {
                type: Array,
                required: true
            }
        },
        mounted() {},
        methods: {
            onAddNewLayer(is_overlay) {
                const vm = this;
                const data = {
                    name: 'Unnamed Layer',
                    is_overlay: is_overlay
                };
                vm.$http.post('/api/map/layer', data).then(function(response) {
                    if(is_overlay) vm.overlays.push(response.data);
                    else vm.baselayer.push(response.data);
                }).catch(function(error) {
                    vm.$throwError(error);
                });
            },
            onLayerSelect(layer) {
                this.selectedLayer = Object.assign({}, layer);
                this.originalLayer = Object.assign({}, layer);
            },
            updateLayer(layer, orgLayer) {
                const vm = this;
                let tmpLayer = Object.assign({}, layer);
                let data = {};
                const lid = tmpLayer.id;
                delete tmpLayer.id;
                let updated = orgLayer.colior != tmpLayer.color;
                for(let k in tmpLayer) {
                    if(vm.isPropUpdated(orgLayer[k], tmpLayer[k])) {
                        data[k] = tmpLayer[k];
                        updated = true;
                    }
                }
                if(!updated) {
                    console.log("No update needed");
                    return;
                }
                vm.$http.patch(`/api/map/layer/${lid}`, data).then(function(response) {
                    let activeBaseLayerChanged = false;
                    let layerGroup;
                    if(!tmpLayer.is_overlay) {
                        layerGroup = vm.baselayer;
                        activeBaseLayerChanged = tmpLayer.visible != orgLayer.visible;
                        // Only update other visible states, if this one
                        // is now visible
                        if(activeBaseLayerChanged && tmpLayer.visible) {
                            vm.baselayer.forEach(bl => {
                                if(bl.id != tmpLayer.id) {
                                    bl.visible = false;
                                }
                            });
                        }
                    } else {
                        layerGroup = vm.overlays;
                    }
                    let updateLayer = layerGroup.find(l => l.id == orgLayer.id);
                    if(updateLayer) {
                        for(let k in tmpLayer) {
                            if(updateLayer[k] != tmpLayer[k]) {
                                updateLayer[k] = tmpLayer[k];
                            }
                        }
                    }
                    vm.$showToast('Layer updated', `Layer ${layer.name} successfully updated.`, 'success');
                }).catch(function(error) {
                    vm.$throwError(error);
                });
            },
            isPropUpdated(oldValue, newValue) {
                return oldValue != newValue;
            },
            getTitle(layer) {
                if(layer.name) {
                    return layer.name
                }
                if(layer.context_type) {
                    this.$translateConcept(layer.context_type.thesaurus_url);
                }
                return 'No Title';
            }
        },
        data() {
            return {
                selectedLayer: {},
                originalLayer: {}
            }
        }
    }
</script>
