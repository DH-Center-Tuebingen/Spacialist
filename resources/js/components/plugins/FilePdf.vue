<template>
    <div class="h-100 d-flex flex-column">
        <div>
            <button type="button" class="btn btn-link" :disabled="isFirstPage" @click="gotoFirstPage()">
                <i class="fas fa-fw fa-angle-double-left"></i>
            </button>
            <button type="button" class="btn btn-link" :disabled="isFirstPage" @click="gotoPreviousPage()">
                <i class="fas fa-fw fa-angle-left"></i>
            </button>
            <button type="button" class="btn btn-link" :disabled="isLastPage" @click="gotoNextPage()">
                <i class="fas fa-fw fa-angle-right"></i>
            </button>
            <button type="button" class="btn btn-link" :disabled="isLastPage" @click="gotoLastPage()">
                <i class="fas fa-fw fa-angle-double-right"></i>
            </button>
            <button type="button" class="btn btn-link" @click="rotateCcw()">
                <i class="fas fa-fw fa-undo-alt"></i>
            </button>
            <button type="button" class="btn btn-link" @click="rotateCw()">
                <i class="fas fa-fw fa-redo-alt"></i>
            </button>
            <input type="number" style="max-width: 100px;" min="1" :max="totalPages" step="1" v-model.number="page" />/{{totalPages}}
            <button type="button" class="btn btn-link" v-if="fullscreenHandler" @click="toggleFullscreen">
                <i class="fas fa-fw fa-expand"></i>
            </button>
        </div>
        <pdf class="col px-0 scroll-y-auto scroll-x-hidden"
            :page="page"
            :rotate="rotation"
            :src="file.url"
            @num-pages="setNumberOfPages">
        </pdf>
    </div>
</template>

<script>
    import pdf from 'vue-pdf';

    export default {
        components: {
            pdf
        },
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
        mounted() {
        },
        methods: {
            setNumberOfPages(pages) {
                this.totalPages = pages;
            },
            gotoFirstPage() {
                this.page = 1;
            },
            gotoPreviousPage() {
                if(this.page > 1) {
                    this.page--;
                }
            },
            gotoNextPage() {
                if(this.page < this.totalPages) {
                    this.page++;
                }
            },
            gotoLastPage() {
                this.page = this.totalPages;
            },
            rotateCcw() {
                if(this.rotationIndex == 0) {
                    this.rotationIndex = this.rotations.length - 1;
                } else {
                    this.rotationIndex--;
                }
            },
            rotateCw() {
                if(this.rotationIndex == this.rotations.length - 1) {
                    this.rotationIndex = 0;
                } else {
                    this.rotationIndex++;
                }
            },
            toggleFullscreen() {
                if(!this.fullscreenHandler) return;
                const element = document.getElementById('file-container');
                this.fullscreenHandler(element)
            }
        },
        data() {
            return {
                rotations: [0, 90, 180, 270],
                rotationIndex: 0,
                page: 1,
                totalPages: 1
            }
        },
        computed: {
            rotation: function() {
                return this.rotations[this.rotationIndex];
            },
            isFirstPage: function() {
                return this.page == 1;
            },
            isLastPage: function() {
                return this.page == this.totalPages;
            }
        }
    }
</script>
