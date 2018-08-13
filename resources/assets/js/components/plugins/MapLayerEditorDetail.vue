<template>
    <div class="h-100 d-flex flex-column">
        <div class="d-flex flex-row justify-content-between mb-2">
            <h5>Properties of {{ title }}</h5>
            <button type="submit" form="layer-form" class="btn btn-success">
                <i class="fas fa-fw fa-save"></i> Save
            </button>
        </div>
        <form role="form" id="layer-form" class="col pl-0 scroll-y-auto scroll-x-hidden" @submit.prevent="updateLayer(layer)">
            <div v-if="isEntityLayer">
                <div class="form-group row">
                    <label for="color" class="col-md-4 col-form-label">
                        Color:
                    </label>
                    <div class="col-md-8">
                        <input type="color" id="color" class="form-control" v-model="layer.color" />
                    </div>
                </div>
            </div>
            <div v-else>
                <div class="form-group row">
                    <label for="name" class="col-md-4 col-form-label">
                        Name:
                    </label>
                    <div class="col-md-8">
                        <input type="text" id="name" class="form-control" v-model="layer.name" />
                    </div>
                </div>
                <div class="form-group row">
                    <label for="url" class="col-md-4 col-form-label">
                        URL:
                    </label>
                    <div class="col-md-8">
                        <input type="text" id="url" class="form-control" v-model="layer.url" />
                    </div>
                </div>
                <div class="form-group row">
                    <label for="type" class="col-md-4 col-form-label">
                        Type:
                    </label>
                    <div class="col-md-8">
                        <input type="text" id="type" class="form-control" v-model="layer.type" />
                    </div>
                </div>
                <div class="form-group row">
                    <label for="subdomains" class="col-md-4 col-form-label">
                        Subdomains:
                    </label>
                    <div class="col-md-8">
                        <input type="text" id="subdomains" class="form-control" v-model="layer.subdomains" />
                    </div>
                </div>
                <div class="form-group row">
                    <label for="attribution" class="col-md-4 col-form-label">
                        Attribution:
                    </label>
                    <div class="col-md-8">
                        <textarea id="attribution" class="form-control" v-model="layer.attribution"></textarea>
                    </div>
                </div>
                <div class="form-group row">
                    <label for="layers" class="col-md-4 col-form-label">
                        Layers:
                    </label>
                    <div class="col-md-8">
                        <input type="text" id="layers" class="form-control" v-model="layer.layers" />
                    </div>
                </div>
                <div class="form-group row">
                    <label for="styles" class="col-md-4 col-form-label">
                        Styles:
                    </label>
                    <div class="col-md-8">
                        <textarea id="styles" class="form-control" v-model="layer.styles"></textarea>
                    </div>
                </div>
                <div class="form-group row">
                    <label for="format" class="col-md-4 col-form-label">
                        Format:
                    </label>
                    <div class="col-md-8">
                        <textarea id="format" class="form-control" v-model="layer.format"></textarea>
                    </div>
                </div>
                <div class="form-group row">
                    <label for="version" class="col-md-4 col-form-label">
                        Version:
                    </label>
                    <div class="col-md-8">
                        <input type="text" id="version" class="form-control" v-model="layer.version" />
                    </div>
                </div>
                <div class="form-group row">
                    <label for="overlay" class="col-md-4 col-form-label">
                        Overlay:
                    </label>
                    <div class="col-md-8">
                        <input type="checkbox" id="overlay" class="form-control" v-model="layer.is_overlay" />
                    </div>
                </div>
                <div class="form-group row">
                    <label for="api-key" class="col-md-4 col-form-label">
                        API-Key:
                    </label>
                    <div class="col-md-8">
                        <input type="text" id="api-key" class="form-control" v-model="layer.api_key" />
                    </div>
                </div>
                <div class="form-group row">
                    <label for="layer-type" class="col-md-4 col-form-label">
                        Layer Type:
                    </label>
                    <div class="col-md-8">
                        <input type="text" id="layer-type" class="form-control" v-model="layer.layer_type" />
                    </div>
                </div>
                <div class="form-group row">
                    <label for="color" class="col-md-4 col-form-label">
                        Color:
                    </label>
                    <div class="col-md-8">
                        <input type="color" id="color" class="form-control" v-model="layer.color" />
                    </div>
                </div>
            </div>
            <div class="form-group row">
                <label for="visible" class="col-md-4 col-form-label">
                    Visible:
                </label>
                <div class="col-md-8">
                    <input type="checkbox" id="visible" class="form-control" v-model="layer.visible" />
                </div>
            </div>
            <div class="form-group row">
                <label for="opacity" class="col-md-4 col-form-label">
                    Opacity:
                </label>
                <div class="col-md-8 d-flex">
                    <input type="range" id="opacity" class="form-control" step="0.01" min="0" max="1" value="0" v-model="layer.opacity"/>
                    <span class="ml-3">{{ layer.opacity }}</span>
                </div>
            </div>
        </form>
    </div>
</template>

<script>
    export default {
        beforeRouteEnter(to, from, next) {
            $http.get(`map/layer/${to.params.id}`).then(response => {
                next(vm => vm.init(response.data));
            });
        },
        beforeRouteUpdate(to, from, next) {
            $http.get(`map/layer/${to.params.id}`).then(response => {
                this.init(response.data);
                next();
            });
        },
        mounted() {},
        methods: {
            init(layer) {
                this.layer = layer;
            },
            updateLayer(layer, orgLayer) {
                const vm = this;
                let tmpLayer = Object.assign({}, layer);
                let data = {};
                const lid = tmpLayer.id;
                delete tmpLayer.id;
                for(let k in tmpLayer) {
                    if(this.isEntityLayer && k != 'color' && k != 'visible' && k != 'opacity') {
                        continue;
                    }
                    data[k] = tmpLayer[k];
                }
                vm.$http.patch(`map/layer/${lid}`, data).then(function(response) {
                    vm.$showToast('Layer updated', `Layer ${layer.name} successfully updated.`, 'success');
                });
            }
        },
        data() {
            return {
                layer: {}
            }
        },
        computed: {
            title: function() {
                if(this.layer.name) {
                    return this.layer.name
                }
                if(this.layer.context_type) {
                    return this.$translateConcept(this.layer.context_type.thesaurus_url);
                }
                return 'No Title';
            },
            isEntityLayer: function() {
                return this.layer.context_type_id || this.layer.type == 'unlinked';
            }
        }
    }
</script>
