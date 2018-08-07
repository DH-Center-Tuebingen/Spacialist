<template>
    <modal :name="id" width="60%" height="80%" :scrollable="true" :draggable="true" classes="of-visible" @before-open="init">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Layer Properties</h5>
                <button type="button" class="close" aria-label="Close" @click.prevent="hide">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body row col">
                <div class="col-md-2 h-100">
                    <ul class="nav nav-pills flex-column">
                        <li class="nav-item">
                            <a class="nav-link" :class="{active: activeTab == 'style'}" @click.prevent="setActiveTab('style')" href="#">
                                <i class="fas fa-fw fa-palette"></i> Style
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" :class="{active: activeTab == 'labels'}" @click.prevent="setActiveTab('labels')" href="#">
                                <i class="fas fa-fw fa-tags"></i> Labels
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" :class="{active: activeTab == 'diagrams'}" @click.prevent="setActiveTab('diagrams')" href="#">
                                <i class="fas fa-fw fa-chart-pie"></i> Diagrams
                            </a>
                        </li>
                    </ul>
                </div>
                <div class="col-md-10 h-100">
                    <keep-alive>
                        <component
                            :is="activeTabComponent"
                            :layer="layer"
                            :on-update="onUpdate">
                        </component>
                    </keep-alive>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" @click="hide">
                    <i class="fas fa-fw fa-times"></i> Cancel
                </button>
            </div>
        </div>
    </modal>
</template>

<script>
    Vue.component('map-gis-properties-style', require('./MapGisPropertiesStyle.vue'))
    Vue.component('map-gis-properties-labels', require('./MapGisPropertiesLabels.vue'))
    Vue.component('map-gis-properties-diagrams', require('./MapGisPropertiesDiagrams.vue'))

    export default {
        props: {
            id: {
                required: false,
                type: String,
                default: 'map-gis-properties-modal'
            },
            onUpdate: {
                required: false,
                type: Function
            }
        },
        beforeMount() {},
        mounted() {},
        methods: {
            init(event) {
                this.layer = event.params.layer;
            },
            setActiveTab(id) {
                this.activeTab = id;
            },
            hide() {
                this.$modal.hide(this.id);
            }
        },
        data() {
            return {
                activeTab: 'style',
                layer: {}
            }
        },
        computed: {
            activeTabComponent: function() {
                return `map-gis-properties-${this.activeTab}`;
            }
        }
    }
</script>
