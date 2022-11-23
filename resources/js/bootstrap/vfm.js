import {
    createVfm,
    useModal as useVfmModal,
} from 'vue-final-modal';

export const vfm = createVfm();

export const useModal = options => {
    return useVfmModal({
        ...options,
        context: vfm,
    });
};
