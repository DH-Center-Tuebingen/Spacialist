<template>
    <div>
        <h3>Your Activity</h3>
        <activity-log
            v-if="dataLoaded"
            :activity="userActivity"
            :hide-user="true">    
        </activity-log>
    </div>
</template>

<script>
    export default {
        mounted() {
            this.init();
        },
        methods: {
            init() {
                this.dataLoaded = false;
                const uid = this.$auth.user().id;
                $http.get(`activity/${uid}`).then(response => {
                    this.userActivity.length = 0;
                    this.userActivity = response.data;
                    this.dataLoaded = true;
                });
            }
        },
        data() {
            return {
                userActivity: [],
                dataLoaded: false
            }
        },
    }
</script>
