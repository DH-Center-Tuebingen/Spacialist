import { after } from "lodash";
import { globalT } from "../../bootstrap/i18n";

export const defaultEpochFormat = 'bcad';

export const epochOptionMap = {
    'bcad': {
        templateLabel: 'BC/AD',
        before: globalT('main.entity.attributes.bc'),
        after: globalT('main.entity.attributes.ad')
    },
    'bce': {
        templateLabel: 'BCE/CE',
        before: "BCE",
        after: "CE",
    }
}

export const getLabelsOf = (key) => {
    const epoch = epochOptionMap[key] ?? epochOptionMap[defaultEpochFormat];
    return {
        before: epoch.before,
        after: epoch.after,
    }
}

export const getEpochOptions = () => {
    return Object.keys(epochOptionMap).map(key => {
        return {
            key: key,
            templateLabel: epochOptionMap[key].templateLabel,
            before: epochOptionMap[key].before,
            after: epochOptionMap[key].after,
        }
    })
}

export const getEpochTemplateOptions = (key) => {
    return getEpochOptions().toSorted((a, b) => {
        return a.templateLabel.localeCompare(b.templateLabel);
    });
}

export const getEpochByFormat = (format) => {
    return epochOptionMap[format] ?? epochOptionMap[defaultEpochFormat];
}
