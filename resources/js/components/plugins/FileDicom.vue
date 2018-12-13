<template>
    <div class="d-flex flex-column h-100">
        <div class="d-flex flex-row col pl-0">
            <div id="dicom-wrapper" class="col pl-0">
                <div id="dicom-image" class="h-100" oncontextmenu="return false;">
                </div>
            </div>
            <div class="text-left col d-flex flex-column">
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
                            VOI (Values of Interest)
                        </dt>
                        <dd v-html="$t('plugins.files.modal.detail.dicom.controls.voi-desc')">
                        </dd>
                    </dl>
                </div>
                <div id="dicom-metadata" class="col scroll-y-auto">
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
                    <ul>
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
    import * as cornerstone from 'cornerstone-core';
    import * as cornerstoneMath from 'cornerstone-math';
    import * as cornerstoneTools from 'cornerstone-tools';
    import * as cornerstoneWADOImageLoader from 'cornerstone-wado-image-loader';
    import * as dicomParser from 'dicom-parser';
    import dicomUids from '../../plugins/dicomUids';
    import dicomTags from '../../plugins/dicomDict';

    cornerstoneTools.external.cornerstone = cornerstone;
    cornerstoneTools.external.cornerstoneMath = cornerstoneMath;
    cornerstoneWADOImageLoader.external.cornerstone = cornerstone;
    cornerstoneWADOImageLoader.external.dicomParser = dicomParser;

    const config = {
        webWorkerPath : '../../js/cornerstoneWADOImageLoaderWebWorker.min.js',
        taskConfiguration: {
            'decodeTask' : {
                codecsPath: '../js/cornerstoneWADOImageLoaderCodecs.min.js'
            }
        }
    };
    cornerstoneWADOImageLoader.webWorkerManager.initialize(config);

    export default {
        props: {
            file: {
                required: true,
                type: Object
            }
        },
        mounted() {
            this.$nextTick(function() {
                this.elem = document.getElementById('dicom-image');
                cornerstone.enable(this.elem, {
                    renderer: 'webgl'
                });

                this.elem.addEventListener('cornerstoneimagerendered', this.onImageRendered);

                this.loadImage(`wadouri:${this.file.url}`);
            });
        },
        methods: {
            loadImage(url) {
                cornerstone.loadAndCacheImage(url).then((image) => {
                    const viewport = cornerstone.getViewport(this.elem);
                    cornerstone.displayImage(this.elem, image, viewport);
                    cornerstoneTools.mouseInput.enable(this.elem);
                    // cornerstoneTools.touchInput.enable(this.elem);
                    cornerstoneTools.mouseWheelInput.enable(this.elem);
                    cornerstoneTools.keyboardInput.enable(this.elem);

                    cornerstoneTools.wwwc.activate(this.elem, 1) // left click
                    cornerstoneTools.pan.activate(this.elem, 2) // middle click
                    cornerstoneTools.zoom.activate(this.elem, 4) // right click
                    cornerstoneTools.zoomWheel.activate(this.elem);

                    cornerstoneTools.scaleOverlayTool.enable(this.elem);

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
            saveAsImage() {
                // saveAs returns png, thus replace file extension with png
                const filename = this.file.name.substr(0, this.file.name.lastIndexOf('.')) + '.png';
                cornerstoneTools.saveAs(this.elem, filename);
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
                metadataQuery: ''
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
