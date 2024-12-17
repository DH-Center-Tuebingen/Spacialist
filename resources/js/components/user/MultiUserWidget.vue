<template>
    <div
        v-if="hasOtherUsers"
        class="d-flex flex-row gap-1 align-items-center border-end pe-2"
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
        <DotIndicator
            type="success"
            style="width: 0.6rem;"
        />
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
            const hasOtherUsers = computed(_=> {
                return props.activeUsers.length >= 2 || (props.activeUsers.length == 1 && props.activeUsers[0].id != userStore.getCurrentUserId);
            });
            return {
                showUserInfo,
                hasOtherUsers,
            };
        },
    };
</script>