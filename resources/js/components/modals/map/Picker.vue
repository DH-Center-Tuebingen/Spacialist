<template>
    <vue-final-modal
        classes="modal-container"
        content-class="sp-modal-content sp-modal-content-xl h-100"
        v-model="state.show"
        name="map-picker-modal">
        <div class="modal-header">
            <h5 class="modal-title">
                {{
                    t('main.entity.attributes.set_location')
                }}
            </h5>
            <button type="button" class="btn-close" aria-label="Close" data-bs-dismiss="modal" @click="closeModal()">
            </button>
        </div>
        <div class="modal-body d-flex flex-column">
            <sp-map class="flex-grow-1 overflow-hidden"
                v-if="state.layersFetched"
                :layers="state.layers"
                :data="state.wktData"
                :projection="4326"
                :input-projection="4326"
                :reset-each="true"
                @added="updateData" />
            <!-- <sp-map class="flex-grow-1 overflow-hidden"
                :epsg="{epsg: '4326'}"
                :layers="wktLayers"
                :init-wkt="initialGeoValues"
                :init-projection="'EPSG:4326'"
                :on-deleteend="onGeoFeaturesDeleted"
                :on-drawend="onGeoFeatureAdded"
                :on-modifyend="onGeoFeaturesUpdated"
                :reset="true" /> -->
            <div class="mt-2">
                WKT: <pre class="m-0"><code>{{ state.wktValue }}</code></pre>
            </div>
        </div>
        <div class="modal-footer">
            <button type="button" class="btn btn-outline-success" data-bs-dismiss="modal" @click="confirmLocation()">
                <i class="fas fa-fw fa-times"></i> {{ t('global.set') }}
            </button>
            <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal" @click="closeModal()">
                <i class="fas fa-fw fa-times"></i> {{ t('global.close') }}
            </button>
        </div>
    </vue-final-modal>
</template>

<script>
    import {
        computed,
        onMounted,
        reactive,
        toRefs,
    } from 'vue';
    
    import { useI18n } from 'vue-i18n';
    
    import {
        getLayers
    } from '@/helpers/map.js';

    export default {
        props: {
            data: {
                required: true,
                type: Object,
            },
        },
        emits: ['confirm', 'closing'],
        setup(props, context) {
            const { t } = useI18n();

            const {
                data,
            } = toRefs(props);

            // FUNCTIONS
            const updateData = e => {
                const f = e.feature;
                const wkt = e.wkt;
                state.wktValue = wkt;
            };
            const confirmLocation = _ => {
                state.show = false;
                context.emit('confirm', state.wktValue);
            };
            const closeModal = _ => {
                state.show = false;
                context.emit('closing', false);
            };

            // DATA
            const state = reactive({
                show: false,
                layers: null,
                layersFetched: false,
                wktValue: data.value.value,
                wktData: computed(_ => {
                    return {
                        format: 'wkt',
                        features: [state.wktValue],
                    };
                }),
            });

            // ON MOUNTED
            onMounted(_ => {
                state.show = true;
                state.layersFetched = false;
                getLayers().then(layers => {
                    state.layers = layers;
                    state.layersFetched = true;
                });
            });

            // RETURN
            return {
                t,
                // HELPERS
                // PROPS
                data,
                // LOCAL
                updateData,
                confirmLocation,
                closeModal,
                // STATE
                state,
            }
        },
    }
</script>
