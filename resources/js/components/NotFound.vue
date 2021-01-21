<template>
    <div class="d-flex flex-column align-items-center">
        <h2>Not Found</h2>
        <span>
            This page does not exist!
        </span>
        <div class="mt-2">
            <router-link class="btn btn-primary" :to="{name: 'home'}">
                Go home
            </router-link>
        </div>
    </div>
</template>

<script>
    export default {
        setup() {
            return {};
        }
    }
</script>
