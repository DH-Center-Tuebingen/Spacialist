import i18n from '@/bootstrap/i18n.js';

const t = i18n.global.t;

export const bibtex = {
    mixed: {
        required: o => t('main.bibliography.types.validation.required', {col: o.path}),
    }
};
export const bibtexExt = {
    requiredIf: (o, ref) => {
        return t('main.bibliography.types.validation.required_one_of', {col: o.path, ref: ref});
    }
};