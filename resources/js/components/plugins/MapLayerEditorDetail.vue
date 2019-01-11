<template>
    <div class="h-100 d-flex flex-column">
        <div class="d-flex flex-row justify-content-between mb-2">
            <h5>
                {{ $t('plugins.map.layer-editor.properties-of', {name: title}) }}
            </h5>
            <div class="">
                <button type="submit" form="layer-form" class="btn btn-success">
                    <i class="fas fa-fw fa-save"></i> {{ $t('global.save') }}
                </button>
                <button type="button" class="btn btn-danger" v-if="layer.type != 'unlinked' && !layer.entity_type_id" @click="requestDeleteLayer()">
                    <i class="fas fa-fw fa-trash"></i> {{ $t('global.delete') }}
                </button>
            </div>
        </div>
        <form role="form" id="layer-form" class="col pl-0 scroll-y-auto scroll-x-hidden" @submit.prevent="updateLayer(layer)">
            <div v-if="isEntityLayer">
                <div class="form-group row">
                    <label for="color" class="col-md-4 col-form-label">
                        {{ $t('global.color') }}:
                    </label>
                    <div class="col-md-8">
                        <input type="color" id="color" class="form-control" v-model="layer.color" />
                    </div>
                </div>
            </div>
            <div v-else>
                <div class="form-group row">
                    <label for="name" class="col-md-4 col-form-label">
                        {{ $t('global.name') }}:
                    </label>
                    <div class="col-md-8">
                        <input type="text" id="name" class="form-control" v-model="layer.name" required />
                    </div>
                </div>
                <div class="form-group row">
                    <label for="url" class="col-md-4 col-form-label">
                        {{ $t('global.url') }}:
                    </label>
                    <div class="col-md-8">
                        <input type="text" id="url" class="form-control" v-model="layer.url" required />
                    </div>
                </div>
                <div class="form-group row">
                    <label for="type" class="col-md-4 col-form-label">
                        {{ $t('global.type') }}:
                    </label>
                    <div class="col-md-8">
                        <input type="text" id="type" class="form-control" v-model="layer.type" required />
                    </div>
                </div>
                <div class="form-group row">
                    <label for="subdomains" class="col-md-4 col-form-label">
                        {{ $t('plugins.map.layer-editor.properties.subdomains') }}:
                    </label>
                    <div class="col-md-8">
                        <input type="text" id="subdomains" class="form-control" v-model="layer.subdomains" />
                    </div>
                </div>
                <div class="form-group row">
                    <label for="attribution" class="col-md-4 col-form-label">
                        {{ $t('plugins.map.layer-editor.properties.attribution') }}:
                    </label>
                    <div class="col-md-8">
                        <textarea id="attribution" class="form-control" v-model="layer.attribution"></textarea>
                    </div>
                </div>
                <div class="form-group row">
                    <label for="layers" class="col-md-4 col-form-label">
                        {{ $tc('main.map.layer', 2) }}:
                    </label>
                    <div class="col-md-8">
                        <input type="text" id="layers" class="form-control" v-model="layer.layers" />
                    </div>
                </div>
                <div class="form-group row">
                    <label for="styles" class="col-md-4 col-form-label">
                        {{ $t('plugins.map.layer-editor.properties.styles') }}:
                    </label>
                    <div class="col-md-8">
                        <textarea id="styles" class="form-control" v-model="layer.styles"></textarea>
                    </div>
                </div>
                <div class="form-group row">
                    <label for="format" class="col-md-4 col-form-label">
                        {{ $t('global.format') }}:
                    </label>
                    <div class="col-md-8">
                        <textarea id="format" class="form-control" v-model="layer.format"></textarea>
                    </div>
                </div>
                <div class="form-group row">
                    <label for="version" class="col-md-4 col-form-label">
                        {{ $t('global.version') }}:
                    </label>
                    <div class="col-md-8">
                        <input type="text" id="version" class="form-control" v-model="layer.version" />
                    </div>
                </div>
                <div class="form-group row">
                    <label for="overlay" class="col-md-4 col-form-label">
                        {{ $tc('main.map.overlay', 1) }}:
                    </label>
                    <div class="col-md-8">
                        <input type="checkbox" id="overlay" class="form-control" v-model="layer.is_overlay" />
                    </div>
                </div>
                <div class="form-group row">
                    <label for="api-key" class="col-md-4 col-form-label">
                        {{ $t('plugins.map.layer-editor.properties.api-key') }}:
                    </label>
                    <div class="col-md-8">
                        <input type="text" id="api-key" class="form-control" v-model="layer.api_key" />
                    </div>
                </div>
                <div class="form-group row">
                    <label for="layer-type" class="col-md-4 col-form-label">
                        {{ $t('plugins.map.layer-editor.properties.layer-type') }}:
                    </label>
                    <div class="col-md-8">
                        <input type="text" id="layer-type" class="form-control" v-model="layer.layer_type" />
                    </div>
                </div>
                <div class="form-group row">
                    <label for="color" class="col-md-4 col-form-label">
                        {{ $t('global.color') }}:
                    </label>
                    <div class="col-md-8">
                        <input type="color" id="color" class="form-control" v-model="layer.color" />
                    </div>
                </div>
            </div>
            <div class="form-group row">
                <label for="visible" class="col-md-4 col-form-label">
                    {{ $t('global.visible') }}:
                </label>
                <div class="col-md-8">
                    <input type="checkbox" id="visible" class="form-control" v-model="layer.visible" />
                </div>
            </div>
            <div class="form-group row">
                <label for="opacity" class="col-md-4 col-form-label">
                    {{ $t('global.opacity') }}:
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
            $httpQueue.add(() => $http.get(`map/layer/${to.params.id}`).then(response => {
                next(vm => vm.init(response.data));
            }));
        },
        beforeRouteUpdate(to, from, next) {
            $httpQueue.add(() => $http.get(`map/layer/${to.params.id}`).then(response => {
                this.init(response.data);
                next();
            }));
        },
        mounted() {},
        methods: {
            init(layer) {
                this.layer = layer;
                this.originalLayer = {...layer};
            },
            isEntityKey(k) {
                return k == 'color' || k == 'visible' || k == 'opacity';
            },
            updateLayer(layer) {
                let tmpLayer = {...layer};
                let data = {};
                const lid = tmpLayer.id;
                delete tmpLayer.id;
                for(let k in tmpLayer) {
                    if(this.isEntityLayer && !this.isEntityKey(k)) {
                        continue;
                    }
                    if(tmpLayer[k] == this.originalLayer[k]) {
                        continue;
                    }
                    data[k] = tmpLayer[k];
                }
                $http.patch(`map/layer/${lid}`, data).then(response => {
                    this.$showToast(
                        this.$t('plugins.map.layer-editor.toasts.updated.title'),
                        this.$t('plugins.map.layer-editor.toasts.updated.msg', {
                            name: layer.name
                        }),
                        'success'
                    );
                });
            },
            requestDeleteLayer() {
                this.$emit('request-delete', {
                    layer: this.layer
                });
            }
        },
        data() {
            return {
                layer: {},
                originalLayer: {}
            }
        },
        computed: {
            title: function() {
                if(this.layer.name) {
                    return this.layer.name
                }
                if(this.layer.entity_type) {
                    return this.$translateConcept(this.layer.entity_type.thesaurus_url);
                }
                return this.$t('plugins.map.untitled');
            },
            isEntityLayer: function() {
                return this.layer.entity_type_id || this.layer.type == 'unlinked';
            }
        }
    }
</script>
