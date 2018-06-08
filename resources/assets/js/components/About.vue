<template>
    <modal name="about-modal" height="auto" :scrollable="true">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">About Spacialist</h5>
                <button type="button" class="close" aria-label="Close" @click="hideAboutModal">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <div class="media">
                    <img class="mr-3" src="/img/logo.png" alt="spacialist logo" width="64px" />
                    <div class="media-body">
                        <h4>Spacialist</h4>
                        Development of Spacialist is co-funded by the Ministry of Science, Research and the Arts Baden-Württemberg in the <i>E-Science</i> funding programme.
                    </div>
                </div>
                <hr />
                <dl class="row">
                    <dt class="col-md-6 text-right">Release Name</dt>
                    <dd class="col-md-6">
                        {{ version.name }}
                    </dd>
                    <dt class="col-md-6 text-right">Time of Release</dt>
                    <dd class="col-md-6">
                        {{ version.time | date }}
                    </dd>
                    <dt class="col-md-6 text-right">Full Release Name</dt>
                    <dd class="col-md-6">
                        {{ version.full }}
                    </dd>
                </dl>
                <div class="text-center">
                    <a href="https://www.facebook.com/esciencecenter" target="_blank">
                        <i class="fab fa-facebook-square fa-2x text-primary"></i>
                    </a>
                    <a href="https://github.com/eScienceCenter/Spacialist" target="_blank">
                        <i class="fab fa-github fa-2x text-dark"></i>
                    </a>
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
            vm.$http.get('/api/version').then(function(response) {
                for(var k in response.data) {
                    Vue.set(vm.version, k, response.data[k]);
                }
            });
        }
    }
</script>
