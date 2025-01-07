<template>
    <div
        class="d-flex flex-row gap-1 align-items-center rounded p-1"
    >
        <div class="d-flex align-items-center avatar-list">
            <a
                v-for="user in activeUsers"
                :key="user.id"
                :title="user.name"
                href="#"
                class="avatar-list-item d-flex align-items-center text-decoration-none"
                @click.prevent="showUserInfo(user)"
            >
                <user-avatar
                    :user="user"
                    :size="20"
                    class="align-middle"
                />
            </a>
        </div>
    </div>
</template>

<script>
    import {
        showUserInfo,
    } from '@/helpers/modal.js';
    import { computed } from 'vue';
    import useUserStore from '@/bootstrap/stores/user.js';
    import DotIndicator from '@/components/indicators/DotIndicator.vue';
    import UserAvatar from '@/components/UserAvatar.vue';
    
    export default {
        components: {
            DotIndicator,
            UserAvatar,
        },
        props: {
            activeUsers: {
                required: true,
                type: Array,
            },
        },
        setup(props) {
            const userStore = useUserStore();
            const otherUsers = computed(() => {
                return props.activeUsers.filter(user => user.id !== userStore.getCurrentUserId);
            });
            return {
                otherUsers,
                showUserInfo,
            };
        },
    };
</script>