<template>
    <div class="w-100 h-100">
        <div class="d-flex flex-row h-100" id="file-container-image">
            <div class="col-md-2 text-left">
                <div class="custom-control custom-switch">
                    <input type="checkbox" class="custom-control-input" id="check-grayscale" v-model="filterList['grayscale'].active" @change="toggleFilter('grayscale')">
                    <label class="custom-control-label" for="check-grayscale">Grayscale</label>
                </div>
                <div class="custom-control custom-switch">
                    <input type="checkbox" class="custom-control-input" id="check-blackwhite" v-model="filterList['blackwhite'].active" @change="toggleFilter('blackwhite')">
                    <label class="custom-control-label" for="check-blackwhite">Black & White</label>
                </div>
                <div class="custom-control custom-switch">
                    <input type="checkbox" class="custom-control-input" id="check-invert" v-model="filterList['invert'].active" @change="toggleFilter('invert')">
                    <label class="custom-control-label" for="check-invert">Invert</label>
                </div>
                <div class="custom-control custom-switch">
                    <input type="checkbox" class="custom-control-input" id="check-sepia" v-model="filterList['sepia'].active" @change="toggleFilter('sepia')">
                    <label class="custom-control-label" for="check-sepia">Sepia</label>
                </div>
                <div class="custom-control custom-switch">
                    <input type="checkbox" class="custom-control-input" id="check-remove-color" v-model="filterList['remove-color'].active" @change="toggleFilter('remove-color')">
                    <label class="custom-control-label" for="check-remove-color">Remove Color</label>
                    <div>
                        <input type="color" v-model="filterList['remove-color'].value" @change="toggleFilter('remove-color')" />
                    </div>
                </div>
                <div class="custom-control custom-switch">
                    <input type="checkbox" class="custom-control-input" id="check-brightness" v-model="filterList['brightness'].active" @change="toggleFilter('brightness')">
                    <label class="custom-control-label" for="check-brightness">Brightness</label>
                    <input type="range" min="-1" max="1" step="0.003921" v-model="filterList['brightness'].value" @change="toggleFilter('brightness')" />
                </div>
                <div class="custom-control custom-switch">
                    <input type="checkbox" class="custom-control-input" id="check-contrast" v-model="filterList['contrast'].active" @change="toggleFilter('contrast')">
                    <label class="custom-control-label" for="check-contrast">Contrast</label>
                    <input type="range" min="-1" max="1" step="0.003921" v-model="filterList['contrast'].value" @change="toggleFilter('contrast')" />
                </div>
                <div class="custom-control custom-switch">
                    <input type="checkbox" class="custom-control-input" id="check-hue" v-model="filterList['hue'].active" @change="toggleFilter('hue')">
                    <label class="custom-control-label" for="check-hue">Hue</label>
                    <input type="range" min="-2" max="2" step="0.002" v-model="filterList['hue'].value" @change="toggleFilter('hue')" />
                </div>
                <div class="custom-control custom-switch">
                    <input type="checkbox" class="custom-control-input" id="check-saturation" v-model="filterList['saturation'].active" @change="toggleFilter('saturation')">
                    <label class="custom-control-label" for="check-saturation">Saturation</label>
                    <input type="range" min="-1" max="1" step="0.003921" v-model="filterList['saturation'].value" @change="toggleFilter('saturation')" />
                </div>
                <div class="custom-control custom-switch">
                    <input type="checkbox" class="custom-control-input" id="check-noise" v-model="filterList['noise'].active" @change="toggleFilter('noise')">
                    <label class="custom-control-label" for="check-noise">Noise</label>
                    <input type="range" min="0" max="1000" step="1" v-model="filterList['noise'].value" @change="toggleFilter('noise')" />
                </div>
                <div class="custom-control custom-switch">
                    <input type="checkbox" class="custom-control-input" id="check-pixelate" v-model="filterList['pixelate'].active" @change="toggleFilter('pixelate')">
                    <label class="custom-control-label" for="check-pixelate">Pixelate</label>
                    <input type="range" min="2" max="20" step="1" v-model="filterList['pixelate'].value" @change="toggleFilter('pixelate')" />
                </div>
                <div class="custom-control custom-switch">
                    <input type="checkbox" class="custom-control-input" id="check-blur" v-model="filterList['blur'].active" @change="toggleFilter('blur')">
                    <label class="custom-control-label" for="check-blur">Blur</label>
                    <input type="range" min="0" max="1" step="0.01" v-model="filterList['blur'].value" @change="toggleFilter('blur')" />
                </div>
                <div class="custom-control custom-switch">
                    <input type="checkbox" class="custom-control-input" id="check-sharpen" v-model="filterList['sharpen'].active" @change="toggleFilter('sharpen')">
                    <label class="custom-control-label" for="check-sharpen">Sharpen</label>
                </div>
                <div class="custom-control custom-switch">
                    <input type="checkbox" class="custom-control-input" id="check-emboss" v-model="filterList['emboss'].active" @change="toggleFilter('emboss')">
                    <label class="custom-control-label" for="check-emboss">Emboss</label>
                </div>
            </div>
            <div class="col-md-10">
                <canvas id="file-container-canvas" class="w-100 h-100"></canvas>
                <!-- <img :src="localUrl" id="file-container-image" class="modal-image" /> -->
                <button type="button" class="btn btn-sm btn-info position-absolute m-2" style="right: 0; top: 0;" v-if="fullscreenHandler" @click="toggleFullscreen">
                    <i class="fas fa-fw fa-expand"></i>
                </button>
            </div>
        </div>
    </div>
</template>

<script>
    import {fabric} from 'fabric';

    export default {
        props: {
            file: {
                required: true,
                type: Object
            },
            fullscreenHandler: {
                required: false,
                type: Function
            }
        },
        beforeMount() {
            this.initFilterLists();
        },
        mounted() {
            fabric.textureSize = 4096;
            fabric.filterBackend = fabric.initFilterBackend();

            this.canvas = new fabric.Canvas('file-container-canvas', {
                enableRetinaScaling: true
            });

            this.canvas.getElement().parentElement.classList.add('w-100');
            this.canvas.getElement().parentElement.classList.add('h-100');
            this.filters = fabric.Image.filters;

            fabric.Image.fromURL(this.localUrl, img => {
                img.set({
                    left: 0,
                    top: 0,
                    selectable: false,
                    scaleX: this.canvas.getWidth()/img.width,
                    scaleY: this.canvas.getHeight()/img.height
                });
                this.canvas.add(img);
                this.canvas.renderAll();
            });
        },
        methods: {
            initFilterLists() {
                const filters = [
                    'grayscale', 'invert', 'remove-color', 'sepia',
                    'brightness', 'contrast', 'saturation', 'noise',
                    'pixelate', 'blur', 'sharpen', 'emboss','blackwhite',
                    'hue'
                ];

                filters.forEach((f, i) => {
                    this.filterIds[f] = i;
                    this.filterList[f] = {
                        active: false,
                        value: ''
                    };
                });
            },
            toggleFilter(name) {
                const f = this.filters;
                const act = this.filterList[name].active;
                const val = this.filterList[name].value;
                let filter;
                switch(name) {
                    case 'grayscale':
                        filter = act && new f.Grayscale({
                            mode: 'average'
                        });
                        break;
                    case 'blackwhite':
                        filter = act && new f.BlackWhite();
                        break;
                    case 'invert':
                        filter = act && new f.Invert();
                        break;
                    case 'sepia':
                        filter = act && new f.Sepia();
                        break;
                    case 'remove-color':
                        filter = act && new f.RemoveColor({
                            distance: 0.25,
                            color: val
                        });
                        break;
                    case 'brightness':
                        filter = act && new f.Brightness({
                            brightness: val
                        });
                        break;
                    case 'contrast':
                        filter = act && new f.Contrast({
                            contrast: val
                        });
                        break;
                    case 'saturation':
                        filter = act && new f.Saturation({
                            saturation: val
                        });
                        break;
                    case 'hue':
                        filter = act && new f.HueRotation({
                            rotation: val
                        });
                        break;
                    case 'noise':
                        filter = act && new f.Noise({
                            noise: val
                        });
                        break;
                    case 'pixelate':
                        filter = act && new f.Pixelate({
                            blocksize: val
                        });
                        break;
                    case 'blur':
                        filter = act && new f.Blur({
                            value: val
                        });
                        break;
                    case 'sharpen':
                        filter = act && new f.Convolute({
                            matrix: [
                                 0, -1,  0,
                                -1,  5, -1,
                                 0, -1,  0
                             ]
                        });
                        break;
                    case 'emboss':
                        filter = act && new f.Convolute({
                            matrix: [
                                1,   1,  1,
                                1, 0.7, -1,
                               -1,  -1, -1
                           ]
                        });
                        break;
                }
                this.applyFilter(filter, name);
            },
            applyFilter(filter, name) {
                this.img = this.canvas.item(0);
                const idx = this.filterIds[name];
                this.img.filters[idx] = filter;
                this.img.applyFilters();
                this.canvas.renderAll();
            },
            toggleFullscreen() {
                if(!this.fullscreenHandler) return;
                const element = document.getElementById('file-container-image');
                this.fullscreenHandler(element)
            }
        },
        data() {
            return {
                filterList: {},
                filterIds: {},
                canvas: null,
                filters: null,
                img: null
            }
        },
        computed: {
            // update url if file changes, to force image reload
            localUrl() {
                const now = new Date();
                return `${this.file.url}?t=${now.getTime()}`;
            }
        }
    }
</script>
