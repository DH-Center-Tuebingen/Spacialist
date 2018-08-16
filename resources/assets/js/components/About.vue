<template>
    <modal name="about-modal" height="auto" :scrollable="true">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">{{ $t('main.about.title') }}</h5>
                <button type="button" class="close" aria-label="Close" @click="hideAboutModal">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <div class="media">
                    <img class="mr-3" src="/img/logo.png" alt="spacialist logo" width="64px" />
                    <div class="media-body">
                        <h4>Spacialist</h4>
                        {{ $t('main.about.desc') }}
                    </div>
                </div>
                <hr />
                <dl class="row">
                    <dt class="col-md-6 text-right">{{ $t('main.about.release.name') }}</dt>
                    <dd class="col-md-6">
                        {{ version.name }}
                    </dd>
                    <dt class="col-md-6 text-right">{{ $t('main.about.release.time') }}</dt>
                    <dd class="col-md-6">
                        {{ version.time | date }}
                    </dd>
                    <dt class="col-md-6 text-right">{{ $t('main.about.release.full-name') }}</dt>
                    <dd class="col-md-6">
                        {{ version.full }}
                    </dd>
                </dl>
                <div class="d-flex flex-row justify-content-between">
                    <span v-html="$t('main.about.build-info')">
                    </span>
                    <div>
                        <a href="https://www.facebook.com/esciencecenter" target="_blank">
                            <i class="fab fa-facebook-square fa-2x text-primary"></i>
                        </a>
                        <a href="https://github.com/eScienceCenter/Spacialist" target="_blank">
                            <i class="fab fa-github fa-2x text-dark"></i>
                        </a>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-outline-secondary"     @click="hideAboutModal">
                    Close
                </button>
            </div>
        </div>
    </modal>
</template>

<script>
    export default {
        mounted() {},
        methods: {
            hideAboutModal() {
                this.$modal.hide('about-modal');
            }
        },
        data() {
            return {
                version: {}
            }
        },
        created() {
            const vm = this;
            vm.$http.get('/version').then(function(response) {
                for(var k in response.data) {
                    Vue.set(vm.version, k, response.data[k]);
                }
            });
        }
    }
</script>
