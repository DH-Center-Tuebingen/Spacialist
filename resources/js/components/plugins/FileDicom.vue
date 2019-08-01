<template>
    <div class="d-flex flex-column h-100">
        <!-- <div class="d-flex flex-row col px-0"> -->
        <div class="row flex-grow-1 overflow-hidden">
            <!-- <div id="dicom-wrapper" class="col px-0"> -->
            <div id="dicom-wrapper" class="col-6 h-100">
                <div id="dicom-image" class="h-100" oncontextmenu="return false;">
                </div>
                <div class="position-absolute top-0 w-100 p-2">
                    <button type="button" class="btn btn-sm btn-outline-secondary" @click="activateTool('Wwwc')">
                        <i class="fas fa-fw fa-adjust"></i>
                    </button>
                    <button type="button" class="btn btn-sm btn-outline-secondary" @click="activateTool('Length')">
                        <i class="fas fa-fw fa-ruler"></i>
                    </button>
                    <button type="button" class="btn btn-sm btn-outline-secondary" @click="activateTool('Magnify')">
                        <i class="fas fa-fw fa-search-plus"></i>
                    </button>
                    <button type="button" class="btn btn-sm btn-outline-secondary" @click="activateTool('CircleRoi')">
                        <i class="fas fa-fw fa-dot-circle"></i>
                    </button>
                </div>
            </div>
            <!-- <div class="text-left col d-flex flex-column"> -->
            <div class="text-left col-6 d-flex flex-column h-100">
                <div id="dicom-controls">
                    <h4>
                        {{ $t('plugins.files.modal.detail.dicom.controls.title') }}
                    </h4>
                    <dl class="alert alert-info">
                        <dt>
                            {{ $t('plugins.files.modal.detail.dicom.controls.zoom') }}
                        </dt>
                        <dd v-html="$t('plugins.files.modal.detail.dicom.controls.zoom-desc')">
                        </dd>
                        <dt>
                            {{ $t('plugins.files.modal.detail.dicom.controls.move') }}
                        </dt>
                        <dd v-html="$t('plugins.files.modal.detail.dicom.controls.move-desc')">
                        </dd>
                        <dt>
                            {{ $t('plugins.files.modal.detail.dicom.controls.voi') }}
                        </dt>
                        <dd v-html="$t('plugins.files.modal.detail.dicom.controls.voi-desc')">
                        </dd>
                    </dl>
                </div>
                <div id="dicom-metadata" class="d-flex flex-column flex-grow-1 overflow-hidden">
                    <h4>{{ $t('plugins.files.modal.detail.dicom.metadata.title') }}</h4>
                    <form>
                        <div class="form-group">
                            <div class="input-group">
                                <input type="search" class="form-control" :placeholder="$t('plugins.files.modal.detail.dicom.metadata.search-placeholder')" v-model="metadataQuery"/>
                                <div class="input-group-append">
                                    <span class="input-group-text">
                                        <i class="fas fa-fw fa-search" aria-hidden="true"></i>
                                    </span>
                                </div>
                            </div>
                        </div>
                    </form>
                    <ul class="text-break scroll-y-auto mb-0">
                        <li v-for="(data, name) in filteredMetadata">
                            <strong>{{name}}</strong>: {{data}}
                        </li>
                    </ul>
                </div>
            </div>
        </div>
        <div class="d-flex justify-content-between mt-2">
            <span>
                {{ $t('plugins.files.modal.detail.dicom.controls.zoom') }}: {{ zoom }}%
            </span>
            <button type="button" class="btn btn-outline-secondary" @click="saveAsImage">
                {{ $t('plugins.files.modal.detail.dicom.save') }}
            </button>
            <span>
                {{ $t('plugins.files.modal.detail.dicom.wwwc') }}: {{ ww }}/{{ wc }}
            </span>
        </div>
    </div>
</template>

<script>
    import 'hammerjs';
    import * as cornerstone from 'cornerstone-core';
    import * as cornerstoneMath from 'cornerstone-math';
    import * as cornerstoneTools from 'cornerstone-tools';
    import * as dicomParser from 'dicom-parser';
    import * as cornerstoneWADOImageLoader from 'cornerstone-wado-image-loader';
    import dicomUids from '../../plugins/dicomUids';
    import dicomTags from '../../plugins/dicomDict';

    cornerstoneTools.external.cornerstone = cornerstone;
    cornerstoneTools.external.cornerstoneMath = cornerstoneMath;
    cornerstoneWADOImageLoader.external.cornerstone = cornerstone;
    cornerstoneWADOImageLoader.external.dicomParser = dicomParser;

    const config = {
        maxWebWorkers: navigator.hardwareConcurrency || 1,
        startWebWorkersOnDemand : true,
        taskConfiguration: {
            'decodeTask' : {
                initializeCodecsOnStartup: false,
                usePDFJS: false
            }
        }
    };
    cornerstoneWADOImageLoader.webWorkerManager.initialize(config);

    export default {
        tools: {
            WwwcTool: cornerstoneTools.WwwcTool,
            PanTool: cornerstoneTools.PanTool,
            ZoomTool: cornerstoneTools.ZoomTool,
            ZoomMouseWheelTool: cornerstoneTools.ZoomMouseWheelTool,
            ScaleOverlayTool: cornerstoneTools.ScaleOverlayTool,
            CircleRoiTool: cornerstoneTools.CircleRoiTool,
            LengthTool: cornerstoneTools.LengthTool,
            MagnifyTool: cornerstoneTools.MagnifyTool,
            available: ['Wwwc', 'Length', 'Magnify', 'CircleRoi']
        },
        props: {
            file: {
                required: true,
                type: Object
            }
        },
        mounted() {
            this.$nextTick(function() {
                cornerstoneTools.init();

                this.elem = document.getElementById('dicom-image');
                cornerstone.enable(this.elem, {
                    renderer: 'webgl'
                });

                cornerstoneTools.addTool(this.$options.tools.WwwcTool);
                cornerstoneTools.addTool(this.$options.tools.PanTool);
                cornerstoneTools.addTool(this.$options.tools.ZoomTool);
                cornerstoneTools.addTool(this.$options.tools.ZoomMouseWheelTool);
                cornerstoneTools.addTool(this.$options.tools.ScaleOverlayTool);
                cornerstoneTools.addTool(this.$options.tools.LengthTool);
                cornerstoneTools.addTool(this.$options.tools.MagnifyTool);
                cornerstoneTools.addTool(this.$options.tools.CircleRoiTool);

                cornerstoneTools.setToolActive("Wwwc", { mouseButtonMask: 1 }); // Left & Touch
                cornerstoneTools.setToolActive("Pan", { mouseButtonMask: 4 }); // Middle
                cornerstoneTools.setToolActive("Zoom", { mouseButtonMask: 2 }); // Right
                cornerstoneTools.setToolActive("ZoomMouseWheel", {});
                cornerstoneTools.setToolActive("ScaleOverlay", {});

                this.elem.addEventListener('cornerstoneimagerendered', this.onImageRendered);

                this.loadImage(`wadouri:${this.file.url}`);
            });
        },
        methods: {
            loadImage(url) {
                cornerstone.loadAndCacheImage(url).then((image) => {
                    const viewport = cornerstone.getViewport(this.elem);
                    cornerstone.displayImage(this.elem, image, viewport);

                    this.parseMetadata(image.data);
                });
            },
            onImageRendered(e) {
                const eventData = e.detail;
                cornerstone.setToPixelCoordinateSystem(eventData.enabledElement, eventData.canvasContext);

                this.renderTime = eventData.renderTimeInMs;
                this.ww = Math.round(eventData.viewport.voi.windowWidth);
                this.wc = Math.round(eventData.viewport.voi.windowCenter);
                this.zoom = parseInt(eventData.viewport.scale*100);
            },
            activateTool(name) {
                cornerstoneTools.setToolPassive(this.activeTool);
                this.activeTool = name;
                cornerstoneTools.setToolActive(this.activeTool, {
                    mouseButtonMask: 1
                });
            },
            saveAsImage() {
                // saveAs returns png, thus replace file extension with png
                const filename = this.file.name.substr(0, this.file.name.lastIndexOf('.')) + '.png';
                cornerstoneTools.SaveAs(this.elem, filename);
            },
            parseMetadata(metadata) {
                const elems = metadata.elements;
                for(let k in elems) {
                    let tag = elems[k].tag;
                    let dicomTag = dicomTags[tag];
                    if(dicomTag) {
                        this.metadata[dicomTag.name] = this.getDicomValue(metadata, elems[k]);
                    }
                }
            },
            getDicomValue(metadata, entry) {
                let value = '';
                const tag = entry.tag;
                if(!entry.vr) {
                    let tmpValue;
                    if(entry.length == 2) {
                        tmpValue = metadata.uint16(tag);
                    } else if(entry.length == 4) {
                        tmpValue = metadata.uint32(tag);
                    }
                    const strValue = metadata.string(tag);
                    if(!!strValue && /^[\x00-\x7F]+$/.test(strValue)) {
                        const sopValue = dicomUids[strValue];
                        if(sopValue) {
                            tmpValue = `${sopValue} [${strValue}]`;
                        } else {
                            tmpValue = strValue;
                        }
                    }
                    value = tmpValue;
                } else {
                    switch(entry.vr) {
                        case 'US':
                            value = metadata.uint16(tag);
                            break;
                        case 'SS':
                            value = metadata.int16(tag);
                            break;
                        case 'UL':
                            value = metadata.uint32(tag);
                            break;
                        case 'SL':
                            value = metadata.int32(tag);
                            break;
                        case 'FD':
                            value = metadata.double(tag);
                            break;
                        case 'FL':
                            value = metadata.float(tag);
                            break;
                        case 'OB':
                        case 'OW':
                        case 'UN':
                        case 'OF':
                        case 'UT':
                            if(entry.length == 2) {
                                value = metadata.uint16(tag);
                            } else if(entry.length == 4) {
                                value = metadata.uint32(tag);
                            }
                            break;
                        case 'AT':
                            const group = metadata.uint16(tag, 0);
                            const grpHex = group.toString(16);
                            const elemHex = group.toString(16);
                            const prop = dicomTags[`x${grpHex}${elemHex}`];
                            if(prop) {
                                value = `Prop: ${prop.name}`;
                            }
                            break;
                        case 'SQ':
                            break;
                        default:
                            const strValue = metadata.string(tag);
                            if(!!strValue && /^[\x00-\x7F]+$/.test(strValue)) {
                                const sopValue = dicomUids[strValue];
                                if(sopValue) {
                                    value = `${sopValue} [${strValue}]`;
                                } else {
                                    value = strValue;
                                }
                            }
                            break;
                    }
                }

                return value;
            }
        },
        data() {
            return {
                elem: {},
                renderTime: 0,
                ww: 0,
                wc: 0,
                zoom: 100,
                metadata: {},
                metadataQuery: '',
                activeTool: 'Wwwc'
            }
        },
        computed: {
            filteredMetadata() {
                if(!this.metadataQuery.length) return this.metadata;

                let filtered = {};
                let lk, lv;
                const q = this.metadataQuery.toLowerCase();
                for(let k in this.metadata) {
                    lk = k.toLowerCase();
                    lv = this.metadata[k].toString().toLowerCase();
                    if(lk.includes(q) || lv.includes(q)) {
                        filtered[k] = this.metadata[k];
                    }
                }
                return filtered;
            }
        },
        watch: {
            file(newFile, oldFile) {
                this.loadImage(`wadouri:${newFile.url}`);
            }
        }
    }
</script>
