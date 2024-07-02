<template>
    <a
        href="#"
        class="d-flex flex-row text-decoration-none"
        @click.prevent="showUserInfo(user)"
    >
        <div
            class="badge bg-tertiary border pe-4 d-flex align-items-center pb-0"
            :class="colorClass"
        >
            <span>
                {{ user.name }}
            </span>
        </div>

        <user-avatar
            :user="user"
            :size="20"
            class="align-middle ms-n3"
        />
    </a>
</template>

<script>

    import {
        computed,
    } from 'vue';

    import {
        showUserInfo,
    } from '@/helpers/modal';

    import {
        userId,
    } from '@/helpers/helpers';

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
            specialUserId: {
                type: Number,
                default: 0,
            },
        },
        setup(props) {

            const colorClass = computed(() => {
                const activeUserId = userId();
                let border = 'border-secondary';
                let background = 'bg-white';
                let text = 'text-secondary';

                if(props.user.id == activeUserId) {
                    border = 'border-secondary';
                    background = 'bg-primary';
                    text = 'text-white';
                } else if(props.user.id == props.specialUserId) {
                    border = 'border-primary';
                    text = 'text-primary';
                }

                return [
                    border,
                    background,
                    text
                ];
            });

            return {
                colorClass,
                showUserInfo,
                userId,
            };
        }
    };
</script>