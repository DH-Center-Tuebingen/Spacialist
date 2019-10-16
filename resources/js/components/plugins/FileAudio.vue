<template>
    <div class="w-100 h-100 d-flex flex-column justify-content-center">
        <div id="waveform"></div>
        <div class="d-flex flex-row justify-content-between mt-2" v-if="initFinished">
            <div class="col-md-4 px-0 d-flex justify-content-start align-items-center">
                <span>
                    {{ progress | time }}
                </span>
                /
                <span>
                    {{ duration | time }}
                </span>
            </div>
            <div class="col-md-4 px-1 d-flex justify-content-around align-items-center">
                <button class="btn btn-sm btn-outline-secondary" @click="skipBack">
                    <i class="fas fa-fw fa-angle-left"></i>
                </button>
                <button class="btn btn-sm btn-outline-secondary" @click="playPause">
                    <span v-show="isPlaying">
                        <i class="fas fa-fw fa-pause"></i>
                    </span>
                    <span v-show="!isPlaying">
                        <i class="fas fa-fw fa-play"></i>
                    </span>
                </button>
                <button class="btn btn-sm btn-outline-secondary" @click="skipFor">
                    <i class="fas fa-fw fa-angle-right"></i>
                </button>
            </div>
            <div class="col-md-4 px-0 d-flex justify-content-center align-items-center">
                <input type="range" min="0" max="1" step="0.01" v-model="currentVolume" @input="setVolume" />
                <div @click="toggleMute" class="ml-1">
                    <span v-show="isMute || currentVolume == 0">
                        <i class="fas fa-fw fa-volume-mute"></i>
                    </span>
                    <span v-show="!isMute && currentVolume > 0">
                        <i class="fas fa-fw fa-volume-up"></i>
                    </span>
                </div>
            </div>
        </div>
        <div id="equalizer" class="d-flex justify-content-between mt-2">
            <input v-for="filter in filters" type="range" min="-40" max="40" :title="filter.frequency.value" v-model="filter.gain.value" orient="vertical" />
        </div>
    </div>
</template>

<script>
    import WaveSurfer from 'wavesurfer.js';

    export default {
        props: {
            file: {
                required: true,
                type: Object
            }
        },
        beforeDestroy() {
            this.wavesurfer.destroy();
        },
        mounted() {
            this.init();
        },
        methods: {
            init() {
                this.initFinished = false;
                const colorIndex =  Math.floor(Math.random()*this.colors.length);
                const activeColor = this.colors[colorIndex];
                this.wavesurfer = WaveSurfer.create({
                    container: '#waveform',
                    waveColor: activeColor.wave,
                    progressColor: activeColor.progress,
                    closeAudioContext: true,
                    responsive: true
                });

                this.filters = this.EQs.map(band => {
                    let filter = this.wavesurfer.backend.ac.createBiquadFilter();
                    filter.type = band.type;
                    filter.gain.value = 0;
                    filter.Q.value = 1;
                    filter.frequency.value = band.f;
                    return filter;
                });

                this.wavesurfer.load(this.file.url);

                this.wavesurfer.on('ready', _ => {
                    this.wavesurfer.backend.setFilters(this.filters);
                    this.wavesurfer.filters = this.filters;

                    this.progress = this.wavesurfer.getCurrentTime();
                    this.duration = this.wavesurfer.getDuration();
                    this.currentVolume = this.wavesurfer.getVolume();
                    this.isPlaying = this.wavesurfer.isPlaying();
                    this.isMute = this.wavesurfer.getMute();
                });
                this.wavesurfer.on('audioprocess', _ => {
                    this.progress = this.wavesurfer.getCurrentTime();
                });
                this.initFinished = true;
            },
            playPause() {
                this.wavesurfer.playPause();
                this.isPlaying = this.wavesurfer.isPlaying();
            },
            skipBack() {
                this.wavesurfer.skipBackward();
            },
            skipFor() {
                this.wavesurfer.skipForward();
            },
            setVolume() {
                this.wavesurfer.setVolume(this.currentVolume);
                this.isMute = this.wavesurfer.getMute();
            },
            toggleMute() {
                this.wavesurfer.toggleMute();
                this.isMute = this.wavesurfer.getMute();
            }
        },
        data() {
            return {
                initFinished: false,
                currentVolume: 1,
                isPlaying: false,
                isMute: false,
                progress: 0,
                duration: 0,
                wavesurfer: {},
                filters: [],
                EQs: [
                    {
                        f: 32,
                        type: 'lowshelf'
                    }, {
                        f: 64,
                        type: 'peaking'
                    }, {
                        f: 125,
                        type: 'peaking'
                    }, {
                        f: 250,
                        type: 'peaking'
                    }, {
                        f: 500,
                        type: 'peaking'
                    }, {
                        f: 1000,
                        type: 'peaking'
                    }, {
                        f: 2000,
                        type: 'peaking'
                    }, {
                        f: 4000,
                        type: 'peaking'
                    }, {
                        f: 8000,
                        type: 'peaking'
                    }, {
                        f: 16000,
                        type: 'highshelf'
                    }
                ],
                colors: [
                    {
                        wave: '#FFCC00',
                        progress: '#FF9900'
                    },
                    {
                        wave: '#66FF00',
                        progress: '#669900'
                    },
                    {
                        wave: '#00CC00',
                        progress: '#006600'
                    },
                    {
                        wave: '#FF9900',
                        progress: '#FF6600'
                    },
                    {
                        wave: '#996633',
                        progress: '#990033'
                    },
                    {
                        wave: '#3399FF',
                        progress: '#3300FF'
                    },
                    {
                        wave: '#9966FF',
                        progress: '#9900FF'
                    },
                    {
                        wave: '#996666',
                        progress: '#990066'
                    }
                ]
            }
        },
        watch: {
            file(newFile, oldFile) {
                // Only init if file changed
                if(!oldFile || (newFile.name != oldFile.name && newFile.size != oldFile.size)) {
                    if(this.wavesurfer.destroy) this.wavesurfer.destroy();
                    this.init();
                }
            }
        }
    }
</script>
