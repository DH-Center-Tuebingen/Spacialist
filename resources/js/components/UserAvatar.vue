<template>
    <span style="display: inline-flex;">
        <img v-if="user.avatar" :src="user.avatar_url" alt="user avatar" :width="`${size}px`" :height="`${size}px`" :class="styles" class="object-fit-cover" />
        <div v-else :style="initialsStyles.container" :class="styles" class="d-flex justify-content-center align-items-center">
            <span :style="initialsStyles.text">
                {{ initials }}
            </span>
        </div>
    </span>
</template>

<script>
    export default {
        props: {
            user: {
                required: true,
                type: Object
            },
            size: {
                required: false,
                type: Number,
                default: 256
            },
            round: {
                required: false,
                type: Boolean,
                default: true
            }
        },
        mounted() {
        },
        methods: {
        },
        data() {
            return {
            }
        },
        computed: {
            styles() {
                return {
                    'rounded-circle': this.round
                }
            },
            color() {
                let hue = 0;
                for(let i=0; i < this.user.name.length; i++) {
                    hue = this.user.name.charCodeAt(i) + ((hue << 5) - hue);
                }

                hue = hue % 360;
                return `hsl(${hue}, 32%, 75%)`;
            },
            initialsStyles() {
                return {
                    container: {
                        height: `${this.size}px`,
                        width: `${this.size}px`,
                        'background-color': this.color
                    },
                    text: {
                        'font-weight': 'bold',
                        'font-size': `${this.size/2}px`,
                        'line-height': `${this.size/2}px`,
                        color: '#ffffff'
                    }
                }
            },
            initials() {
                let initials = '';
                let names = this.user.name.split(' ');
                initials += names[0].charAt(0).toUpperCase();
                if(names.length > 1) {
                    initials += names[names.length-1].charAt(0).toUpperCase();
                }
                return initials;
            }
        }
    }
</script>
