<template>
    <div class="h-100 row of-hidden">
        <div class="col-md-2 h-100 d-flex flex-column">
            <button type="button" class="btn btn-default">
                <i class="fas fa-fw fa-download"></i> Import Geodata
            </button>
            <div class="col d-flex flex-column h-100 pt-2">
                <div class="col of-hidden d-flex flex-column">
                    <h4>Available Layers</h4>
                    <div class="list-group scroll-y-auto col">
                        <a href="#" class="list-group-item list-group-item-action" v-for="l in layers" @dblclick="addLayerToSelection(l)">
                            <span v-if="l.thesaurus_url">
                                {{ $translateConcept(l.thesaurus_url) }}
                            </span>
                            <span v-else>
                                {{ l.name }}
                            </span>
                        </a>
                    </div>
                </div>
                <div class="col of-hidden d-flex flex-column pt-2">
                    <h4>Selected Layers</h4>
                    <p class="alert alert-info" v-show="!selectedLayers.length">
                        Use <kbd>Double Click</kbd> to add an <i>Available Layer</i> and again to remove it.
                    </p>
                    <div class="list-group scroll-y-auto col">
                        <a href="#" class="list-group-item list-group-item-action" v-for="(l, i) in selectedLayers" @dblclick="removeLayerFromSelection(i)">
                            <span v-if="l.thesaurus_url">
                                {{ $translateConcept(l.thesaurus_url) }}
                            </span>
                            <span v-else>
                                {{ l.name }}
                            </span>
                        </a>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-10">
            <ol-map></ol-map>
        </div>
    </div>
</template>

<script>
    export default {
        props: {
            contextTypes: {
                validator: Vue.$validateObject,
                required: false
            },
            layers: {
                type: Array,
                required: true
            }
        },
        mounted() {},
        methods: {
            addLayerToSelection(layer) {
                this.selectedLayers.push(Object.assign({}, layer));
            },
            removeLayerFromSelection(index) {
                this.selectedLayers.splice(index, 1);
            }
        },
        data() {
            return {
                selectedLayers: []
            }
        }
    }
</script>
