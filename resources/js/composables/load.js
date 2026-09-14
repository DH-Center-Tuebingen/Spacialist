import { ref } from "vue";

/**
 * Composable for reoccuring loading tasks.
 * 
 * @returns {Object} - An object containing loading state, error state, and execAsync function.
 * @property {Ref<boolean>} loading - A ref indicating whether the loading is in progress.
 * @property {Ref<string>} error - A ref holding any error that occurred during loading.
 * @property {Function} execAsync - A function to execute an asynchronous task with loading and error handling.
 */
export function useLoad() {

    const loading = ref(true);
    const error = ref("");

    // Important: This should be an arrow funciton
    // or prevent problems when using the current context (this)
    const execAsync = async (fn) => {
        loading.value = true;
        error.value = "";
        try {
            return fn();
        } catch(e) {
            error.value = e?.message || e?.toString() || "Unknown error";
        } finally {
            loading.value = false;
        }
        return null;
    };

    return {
        loading,
        error,
        execAsync,
    }
}