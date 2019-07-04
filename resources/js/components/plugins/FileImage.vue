<template>
    <div class="w-100 h-100">
        <div class="d-flex flex-row h-100" id="file-container-image">
            <div v-if="fabricSupported" class="text-left pl-0 scroll-y-auto" :class="panelClasses.filters">
                <div class="custom-control custom-switch">
                    <input type="checkbox" class="custom-control-input" id="check-grayscale" v-model="filterList['grayscale'].active" @change="toggleFilter('grayscale')">
                    <label class="custom-control-label" for="check-grayscale">
                        {{ $t('plugins.files.image_filters.grayscale') }}
                    </label>
                </div>
                <div class="custom-control custom-switch">
                    <input type="checkbox" class="custom-control-input" id="check-blackwhite" v-model="filterList['blackwhite'].active" @change="toggleFilter('blackwhite')">
                    <label class="custom-control-label" for="check-blackwhite">
                        {{ $t('plugins.files.image_filters.bw') }}
                    </label>
                </div>
                <div class="custom-control custom-switch">
                    <input type="checkbox" class="custom-control-input" id="check-invert" v-model="filterList['invert'].active" @change="toggleFilter('invert')">
                    <label class="custom-control-label" for="check-invert">
                        {{ $t('plugins.files.image_filters.invert') }}
                    </label>
                </div>
                <div class="custom-control custom-switch">
                    <input type="checkbox" class="custom-control-input" id="check-sepia" v-model="filterList['sepia'].active" @change="toggleFilter('sepia')">
                    <label class="custom-control-label" for="check-sepia">
                        {{ $t('plugins.files.image_filters.sepia') }}
                    </label>
                </div>
                <div class="custom-control custom-switch">
                    <input type="checkbox" class="custom-control-input" id="check-remove-color" v-model="filterList['remove-color'].active" @change="toggleFilter('remove-color')">
                    <label class="custom-control-label" for="check-remove-color">
                        {{ $t('plugins.files.image_filters.remove_color') }}
                    </label>
                    <div v-show="filterList['remove-color'].active">
                        <input type="color" class="form-control" v-model="filterList['remove-color'].value" @input="toggleFilter('remove-color')" />
                        <input type="range" class="form-control px-0" min="0" max="1" step="0.01" v-model="filterList['remove-color'].distance" @change="toggleFilter('remove-color')" />
                    </div>
                </div>
                <div class="custom-control custom-switch">
                    <input type="checkbox" class="custom-control-input" id="check-brightness" v-model="filterList['brightness'].active" @change="toggleFilter('brightness')">
                    <label class="custom-control-label" for="check-brightness">
                        {{ $t('plugins.files.image_filters.brightness') }}
                    </label>
                    <div v-show="filterList['brightness'].active">
                        <input type="range" class="form-control px-0" min="-1" max="1" step="0.003921" v-model="filterList['brightness'].value" @change="toggleFilter('brightness')" />
                    </div>
                </div>
                <div class="custom-control custom-switch">
                    <input type="checkbox" class="custom-control-input" id="check-contrast" v-model="filterList['contrast'].active" @change="toggleFilter('contrast')">
                    <label class="custom-control-label" for="check-contrast">
                        {{ $t('plugins.files.image_filters.contrast') }}
                    </label>
                    <div v-show="filterList['contrast'].active">
                        <input type="range" class="form-control px-0" min="-1" max="1" step="0.003921" v-model="filterList['contrast'].value" @change="toggleFilter('contrast')" />
                    </div>
                </div>
                <div class="custom-control custom-switch">
                    <input type="checkbox" class="custom-control-input" id="check-hue" v-model="filterList['hue'].active" @change="toggleFilter('hue')">
                    <label class="custom-control-label" for="check-hue">
                        {{ $t('plugins.files.image_filters.hue') }}
                    </label>
                    <div v-show="filterList['hue'].active">
                        <input type="range" class="form-control px-0" min="-2" max="2" step="0.002" v-model="filterList['hue'].value" @change="toggleFilter('hue')" />
                    </div>
                </div>
                <div class="custom-control custom-switch">
                    <input type="checkbox" class="custom-control-input" id="check-saturation" v-model="filterList['saturation'].active" @change="toggleFilter('saturation')">
                    <label class="custom-control-label" for="check-saturation">
                        {{ $t('plugins.files.image_filters.saturation') }}
                    </label>
                    <div v-show="filterList['saturation'].active">
                        <input type="range" class="form-control px-0" min="-1" max="1" step="0.003921" v-model="filterList['saturation'].value" @change="toggleFilter('saturation')" />
                    </div>
                </div>
                <div class="custom-control custom-switch">
                    <input type="checkbox" class="custom-control-input" id="check-noise" v-model="filterList['noise'].active" @change="toggleFilter('noise')">
                    <label class="custom-control-label" for="check-noise">
                        {{ $t('plugins.files.image_filters.noise') }}
                    </label>
                    <div v-show="filterList['noise'].active">
                        <input type="range" class="form-control px-0" min="0" max="1000" step="1" v-model="filterList['noise'].value" @change="toggleFilter('noise')" />
                    </div>
                </div>
                <div class="custom-control custom-switch">
                    <input type="checkbox" class="custom-control-input" id="check-pixelate" v-model="filterList['pixelate'].active" @change="toggleFilter('pixelate')">
                    <label class="custom-control-label" for="check-pixelate">
                        {{ $t('plugins.files.image_filters.pixelate') }}
                    </label>
                    <div v-show="filterList['pixelate'].active">
                        <input type="range" class="form-control px-0" min="2" max="20" step="1" v-model="filterList['pixelate'].value" @change="toggleFilter('pixelate')" />
                    </div>
                </div>
                <div class="custom-control custom-switch">
                    <input type="checkbox" class="custom-control-input" id="check-blur" v-model="filterList['blur'].active" @change="toggleFilter('blur')">
                    <label class="custom-control-label" for="check-blur">
                        {{ $t('plugins.files.image_filters.blur') }}
                    </label>
                    <div v-show="filterList['blur'].active">
                        <input type="range" class="form-control px-0" min="0" max="1" step="0.01" v-model="filterList['blur'].value" @change="toggleFilter('blur')" />
                    </div>
                </div>
                <div class="custom-control custom-switch">
                    <input type="checkbox" class="custom-control-input" id="check-sharpen" v-model="filterList['sharpen'].active" @change="toggleFilter('sharpen')">
                    <label class="custom-control-label" for="check-sharpen">
                        {{ $t('plugins.files.image_filters.sharpen') }}
                    </label>
                </div>
                <div class="custom-control custom-switch">
                    <input type="checkbox" class="custom-control-input" id="check-emboss" v-model="filterList['emboss'].active" @change="toggleFilter('emboss')">
                    <label class="custom-control-label" for="check-emboss">
                        {{ $t('plugins.files.image_filters.emboss') }}
                    </label>
                </div>
                <button type="button" class="btn btn-outline-success mt-2" @click="storeFiltered">
                    {{ $t('plugins.files.image_filters.save') }}
                </button>
            </div>
            <div class="px-0":class="panelClasses.image">
                <canvas v-if="fabricSupported" id="file-container-container" class="w-100 h-100"></canvas>
                <img v-else :src="localUrl" class="modal-image" id="file-container-container" />
                <div class="d-flex justify-content-between w-100 position-absolute p-2" style="top: 0;">
                    <div>
                        <button v-if="fabricSupported" type="button" class="btn btn-sm btn-secondary" @click="toggleFilterPanel">
                            <i class="fas fa-fw fa-magic"></i>
                        </button>
                    </div>
                    <div>
                        <button type="button" class="btn btn-sm btn-secondary" @click="onOCR">
                            OCR
                        </button>
                        <button type="button" class="btn btn-sm btn-secondary" v-if="fullscreenHandler" @click="toggleFullscreen">
                            <i class="fas fa-fw fa-expand"></i>
                        </button>
                    </div>
                </div>
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
                type: Object
            }
        },
        beforeMount() {
            this.initFilterLists();
        },
        mounted() {
            if(this.fullscreenHandler) {
                this.fullscreenHandler.add(this.onFullscreenChange);
            }

            if(this.fabricSupported) {
                this.canvas = new fabric.Canvas('file-container-container', {
                    enableRetinaScaling: true
                });

                const el = this.canvas.getElement().parentElement;
                el.classList.add('w-100');
                el.classList.add('h-100');
                this.resizeCanvasTo(el);

                this.filters = fabric.Image.filters;

                this.loadImageFromUrl(this.localUrl);
            }
        },
        destroyed() {
            if(this.fullscreenHandler) {
                this.fullscreenHandler.remove(this.onFullscreenChange);
            }
            if(this.fabricSupported) {
                this.canvas.clear();
            }
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
                    Vue.set(this.filterList, f, {
                        active: false,
                        value: ''
                    });
                    if(f === 'remove-color') {
                        Vue.set(this.filterList[f], 'distance', 0.0);
                    }
                });
            },
            toggleFilterPanel() {
                this.filterPanelActive = !this.filterPanelActive;
                this.$nextTick(_ => {
                    this.onFullscreenChange();
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
                            distance: this.filterList[name].distance,
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
            storeFiltered() {
                const oldScale = this.scaledImg.scaleX;
                this.scaledImg.scaleX = 1;
                this.scaledImg.scaleY = 1;
                const format = this.isPng() ? 'png' : 'jpeg';
                const data = this.scaledImg.toDataURL({
                    format: format,
                    quality: 1,
                    multiplier: 1
                });
                this.scaledImg.scaleX = oldScale;
                this.scaledImg.scaleY = oldScale;
                fetch(data).then(r => r.blob()).then(blob => {
                    this.$emit('update-file-content', {
                        file: this.file,
                        content: blob
                    });
                });
            },
            resizeCanvasTo(el) {
                this.canvas.setDimensions({
                    width: el.clientWidth,
                    height: el.clientHeight
                });
            },
            setOriginalImage(img) {
                this.originalImg = img;
            },
            getScaledImage() {
                // Resize/scale image to the lower scaling factor (in %)
                // to fit the image onto the canvas
                const scaleX = this.canvas.width/this.originalImg.width;
                const scaleY = this.canvas.height/this.originalImg.height;
                const scale = Math.min(scaleX, scaleY);
                return this.originalImg.set({
                    left: 0,
                    top: 0,
                    selectable: false,
                    scaleX: scale,
                    scaleY: scale
                });
            },
            setImage(img) {
                this.canvas.add(img);
            },
            unsetImage(img) {
                this.canvas.remove(img);
            },
            loadImageFromUrl(url) {
                fabric.Image.fromURL(url, img => {
                    const maxSize = Math.max(img.width, img.height);
                    if(maxSize > fabric.textureSize) {
                        fabric.textureSize = maxSize;
                    }
                    this.setOriginalImage(img);
                    this.scaledImg = this.getScaledImage();
                    fabric.filterBackend = fabric.initFilterBackend();
                    this.setImage(this.scaledImg);
                    this.canvas.renderAll();
                });
            },
            onOCR() {
                const elem = document.getElementById('file-container-container');
                this.$emit('handle-ocr', {
                    image: elem
                });
            },
            toggleFullscreen() {
                if(!this.fullscreenHandler) return;
                this.fullscreenHandler.toggle(document.getElementById('file-container-image'));
            },
            onFullscreenChange() {
                this.unsetImage(this.scaledImg);
                this.resizeCanvasTo(this.canvas.getElement().parentElement);
                this.scaledImg = this.getScaledImage();
                this.setImage(this.scaledImg);
            },
            isJpeg() {
                switch(this.file.mime_type) {
                    case 'image/jpg':
                    case 'image/jpeg':
                        return true;
                    default:
                        return false;
                }
            },
            isPng() {
                return this.file.mime_type === 'image/png';
            }
        },
        data() {
            return {
                filterList: {},
                filterIds: {},
                filterPanelActive: false,
                canvas: null,
                filters: null,
                img: null,
                originalImg: null,
                scaledImg: null
            }
        },
        computed: {
            // update url if file changes, to force image reload
            localUrl() {
                const now = this.$getTs();
                return `${this.file.url}?t=${now}`;
            },
            fabricSupported() {
                return this.isJpeg() || this.isPng();
            },
            panelClasses() {
                if(this.filterPanelActive) {
                    return {
                        filters: {
                            'col-md-2': true
                        },
                        image: {
                            'col-md-10': true
                        }
                    };
                } else {
                    return {
                        filters: {
                            'd-none': true
                        },
                        image: {
                            'col-md-12': true
                        }
                    };
                }
            }
        },
        watch: {
            file(newFile, oldFile) {
                // if neither file nor content changed
                if(newFile.id == oldFile.id && newFile.modified == oldFile.modified) {
                    return;
                }
                if(this.fabricSupported) {
                    this.canvas.clear();
                    this.loadImageFromUrl(this.localUrl);
                }
            }
        }
    }
</script>
