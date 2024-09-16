import { onBeforeUnmount, onMounted } from 'vue';

export function useGlobalClick(func, eventType = 'click') {
    onMounted(() => {
        window.addEventListener(eventType, func);
    });

    onBeforeUnmount(() => {
        window.removeEventListener(eventType, func);
    });
}