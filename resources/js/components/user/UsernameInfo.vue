<template>
    <a
        href="#"
        class="text-body text-decoration-none"
        @click.prevent="showUserInfo(user)"
    >
        <span class="fw-medium">
            {{ displayName }}
        </span>
        <template v-if="showNickName">
            &bull;
            <span class="text-muted fw-light">
                {{ user.nickname }}
            </span>
        </template>
    </a>
</template>

<script>

    import { computed } from 'vue';

    import {
        showUserInfo,
    } from '@/helpers/modal.js';

    export default {
        props: {
            user: {
                type: Object,
                required: true,
            }
        },
        setup(props) {

            const displayName = computed(() => {
                let displayName = 'Unknown User';
                const names = [props.user.name, props.user.nickname];

                for(let i = 0; i < names.length; i++) {
                    if(names[i]) {
                        displayName = names[i];
                        break;
                    }
                }

                return displayName;
            });

            const showNickName = computed(() => {
                return props.user && props.user.name && props.user.nickname ? true : false;
            });

            return {
                showUserInfo,
                displayName,
                showNickName,
            };
        },
    };
</script>