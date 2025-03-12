<template>
    <span class="fa-layers fa-fw">        
        <i 
            class="fas fa-square"
            :data-fa-transform="`shrink-${shrink} left-${spacing} up-${spacing}`"
        />
        <i 
            class="fas fa-square"
            :data-fa-transform="`shrink-${shrink} right-${spacing} up-${spacing}`"
        />
        <i 
            class="fas fa-square"
            :data-fa-transform="`shrink-${shrink} left-${spacing} down-${spacing}`"
        />
        <i 
            class="fas fa-square"
            :data-fa-transform="`shrink-${shrink} right-${spacing} down-${spacing}`"
        />
    </span>
</template>

<script>
export default {
    setup() {
        return {
            shrink: 6,
            spacing: 5,
        };
    }
};
</script>