<template>
    <modal :name="id" width="60%" height="80%" :draggable="true" :resizable="true" classes="of-visible" @before-open="init">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">{{ $t('plugins.map.gis.props.title') }}</h5>
                <button type="button" class="btn-close" aria-label="Close" @click.prevent="hide">
                </button>
            </div>
            <div class="modal-body row col">
                <div class="col-md-2">
                    <ul class="nav nav-pills flex-column">
                        <li class="nav-item">
                            <a class="nav-link" :class="{active: activeTab == 'style'}" @click.prevent="setActiveTab('style')" href="#">
                                <i class="fas fa-fw fa-palette"></i> {{ $t('plugins.map.gis.props.style.title') }}
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" :class="{active: activeTab == 'labels'}" @click.prevent="setActiveTab('labels')" href="#">
                                <i class="fas fa-fw fa-tags"></i> {{ $t('plugins.map.gis.props.labels.title') }}
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" :disabled="!isEntityLayer" :class="{active: activeTab == 'charts'}" @click.prevent="setActiveTab('charts')" href="#">
                                <i class="fas fa-fw fa-chart-pie"></i> {{ $t('plugins.map.gis.props.diagrams.title') }}
                            </a>
                        </li>
                    </ul>
                </div>
                <div class="col-md-10">
                    <keep-alive>
                        <component
                            :attributes="possibleAttributes"
                            :is-entity-layer="isEntityLayer"
                            :is="activeTabComponent"
                            :layer="layer"
                            :on-update="onUpdate">
                        </component>
                    </keep-alive>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" @click="hide">
                    <i class="fas fa-fw fa-times"></i> {{ $t('global.close') }}
                </button>
            </div>
        </div>
    </modal>
</template>

<script>
    import MapGisPropertiesStyle from './MapGisPropertiesStyle.vue';
    import MapGisPropertiesLabels from './MapGisPropertiesLabels.vue';
    import MapGisPropertiesCharts from './MapGisPropertiesCharts.vue';

    export default {
        props: {
            id: {
                required: false,
                type: String,
                default: 'map-gis-properties-modal'
            },
            onUpdate: {
                required: false,
                type: Function,
                default: _ => {}
            }
        },
        components: {
            'map-gis-properties-style': MapGisPropertiesStyle,
            'map-gis-properties-labels': MapGisPropertiesLabels,
            'map-gis-properties-charts': MapGisPropertiesCharts,
        },
        beforeMount() {},
        mounted() {},
        methods: {
            init(event) {
                this.layer = event.params.layer;
                if(this.isEntityLayer) {
                    $httpQueue.add(() => $http.get(`editor/entity_type/${this.layer.entity_type.id}/attribute`).then(response => {
                        this.attributes = response.data.attributes;
                    }));
                }
            },
            setActiveTab(id) {
                if(id === 'charts' && !this.isEntityLayer) return;
                this.activeTab = id;
            },
            hide() {
                this.$modal.hide(this.id);
            }
        },
        data() {
            return {
                activeTab: 'style',
                layer: {},
                attributes: []
            }
        },
        computed: {
            activeTabComponent: function() {
                return `map-gis-properties-${this.activeTab}`;
            },
            isEntityLayer: function() {
                return !!this.layer.entity_type;
            },
            possibleAttributes() {
                switch(this.activeTab) {
                    case 'charts':
                        return this.attributes.filter(a => {
                            return a.datatype === 'sql' || a.datatype === 'table';
                        });
                    default:
                        return this.attributes;
                }
            }
        }
    }
</script>
