import i18n from '@/bootstrap/i18n.js';
import {
    addMethod,
    string,
} from 'yup';

import {
    isValidOrcid,
} from '@/helpers/validators.js';

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

export const orcid = _ => {
    addMethod(string, 'orcid', function() {
        return this.test('test-orcid', t('global.user.invalid_orcid'), function(value) {
            return isValidOrcid(value);
        });
    });

    return simple().orcid();
};

export const simple = _ => {
    return string().trim();
};

export const simple_max = (max = 255) => {
    return simple().max(max);
};

export const simple_req = _ => {
    return simple().required();
};

export const simple_req_max = (max = 255) => {
    return simple_req().max(max);
};

export const name = _ => {
    return simple_req_max();
};

export const nickname = _ => {
    return name().matches(/^[0-9a-zA-Z-_]+$/);
};

export const email = _ => {
    return simple_req_max().email();
};

export const phone = _ => {
    return simple_max().matches(/^(\+[1-9]|0)[0-9 ]+$/);
};