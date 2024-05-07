import { onBeforeUnmount, onMounted } from 'vue';

export function useGlobalClick(func) {
    onMounted(() => {
        window.addEventListener('click', func);
    });

    onBeforeUnmount(() => {
        window.removeEventListener('click', func);
    });
}