<template>
    <a
        href="#"
        @click.prevent="showUserInfo(user)"
    >
        <user-avatar
            :user="user"
            :size="size"
        />
    </a>
</template>

<script>

    import {
        showUserInfo,
    } from '@/helpers/modal.js';

    import UserAvatar from '@/components/UserAvatar.vue';

    export default {
        components: {
            UserAvatar,
        },
        props: {
            user: {
                type: Object,
                required: true,
            },
            size: {
                type: Number,
                required: true,
            },
        },
        setup() {
            return {
                showUserInfo,
            };
        },
    };
</script>