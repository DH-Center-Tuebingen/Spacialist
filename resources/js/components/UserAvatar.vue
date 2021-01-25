<template>
    <span style="display: inline-flex;">
        <img v-if="user.avatar" :src="user.avatar_url" alt="user avatar" :width="`${size}px`" :height="`${size}px`" :class="state.styles" class="object-fit-cover" />
        <div v-else :style="state.initialsStyles.container" :class="state.styles" class="d-flex justify-content-center align-items-center">
            <span :style="state.initialsStyles.text">
                {{ state.initials }}
            </span>
        </div>
    </span>
</template>

<script>
    import {
        computed,
        reactive,
    } from 'vue';

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
        setup(props) {
            const state = reactive({
                styles: computed(_ => {
                    return {
                        'rounded-circle': props.round
                    }
                }),
                color: computed(_ => {
                    if(!props.user.name) return;
                    let hue = 0;
                    for(let i=0; i < props.user.name.length; i++) {
                        hue = props.user.name.charCodeAt(i) + ((hue << 5) - hue);
                    }

                    hue = hue % 360;
                    return `hsl(${hue}, 32%, 75%)`;
                }),
                initialsStyles: computed(_ => {
                    return {
                        container: {
                            height: `${props.size}px`,
                            width: `${props.size}px`,
                            'background-color': state.color
                        },
                        text: {
                            'font-weight': 'bold',
                            'font-size': `${props.size/2}px`,
                            'line-height': `${props.size/2}px`,
                            color: '#ffffff'
                        }
                    }
                }),
                initials: computed(_ => {
                    if(!props.user.name) return;
                    let initials = '';
                    let names = props.user.name.split(' ');
                    initials += names[0].charAt(0).toUpperCase();
                    if(names.length > 1) {
                        initials += names[names.length-1].charAt(0).toUpperCase();
                    }
                    return initials;
                })
            });

            // RETURN
            return {
                state
            }
        }
    }
</script>
