import { defineStore } from 'pinia';
import i18n from '@/bootstrap/i18n.js';

import useAttributeStore from './attribute.js';
import useBibliographyStore from './bibliography.js';
import useEntityStore from './entity.js';
import useUserStore from './user.js';

import {
    checkAccess,
    fetchPreData,
    searchConceptSelection,
} from '@/api.js';

import {
    router,
} from '@/bootstrap/router.js';

import {
    fetchGlobals,
} from '@/open_api.js';

import {
    slugify,
} from '@/helpers/helpers.js';

import { usePluginStore } from './plugin.js';

const resetState = ctx => {
    ctx.appInitialized = false;
    ctx.colorSets = [];
    ctx.concepts = {};
    ctx.cachedConceptSelections = {};
    ctx.mainView = {
        tab: 'references',
    };
    ctx.systemPreferences = {};
    ctx.tags = [];
    ctx.version = {};
};

export const useSystemStore = defineStore('system', {
    state: _ => ({
        appInitialized: false,
        hasAnalysis: false,
        colorSets: [],
        concepts: {},
        cachedConceptSelections: {},
        mainView: {
            tab: 'references',
        },
        systemPreferences: {},
        tags: [],
        version: {},
        // TODO
        file: {},
        geometryTypes: [],
        datatypeData: {},
        accessPoints: {},
    }),
    getters: {
        translateConcept: state => url => {
            if(!url || !state.concepts) return url;
            if(!state.concepts[url]) return url;
            return state.concepts[url].label;
        },
        hasPreference: state => (key, property) => {
            const preference = useUserStore().getPreferenceByKey(key);
            if(preference) {
                return preference[property] || preference;
            }
            return false;
        },
        getPreference: state => key => {
            return useUserStore().getPreferenceByKey(key);
        },
        getProjectName: state => slug => {
            const projectName = useUserStore().getPreferenceByKey('prefs.project-name');
            return slug ? slugify(projectName) : projectName;
        },
        getDatatypeDataOf: state => key => state.datatypeData[key],
        getAccessPointsAsArray(state) {
            return Object.values(state.accessPoints).map(accesspoint => {
                return {
                    path: accesspoint.path,
                    label: i18n.global.t(accesspoint.label),
                };
            });
        },
    },
    actions: {
        getConceptById(id) {
            return this.concepts[id];
        },
        setAppState(state) {
            this.appInitialized = state;
        },
        setMainViewTab(tab) {
            this.mainView.tab = tab;
        },
        setConcepts(concepts) {
            this.concepts = concepts;
        },
        addCachedConceptSelection(data) {
            this.cachedConceptSelections[data.id] = data.selection;
        },
        async checkAccess(route) {
            const accessResponse = await checkAccess(route);
            if(accessResponse.status == 200 && accessResponse?.data?.redirect) {
                router.push(accessResponse.data.redirect);
                return false;
            }

            return true;
        },
        async initialize(locale) {
            resetState(this);

            const attributeStore = useAttributeStore();
            const bibliographyStore = useBibliographyStore();
            const entityStore = useEntityStore();
            const userStore = useUserStore();
            const pluginStore = usePluginStore();

            const preData = await fetchPreData();
            this.concepts = preData.concepts;
            this.systemPreferences = preData.system_preferences;
            this.colorSets = preData.colorSets;
            this.hasAnalysis = preData.analysis;
            this.datatypeData = preData.datatype_data;
            this.accessPoints = preData.accesspoints ?? {};
            entityStore.initializeEntityTypes(preData.entityTypes);
            userStore.setPreferences(preData.preferences);
            pluginStore.set(preData.plugins);
            
            if(locale?.value) {
                locale.value = this.getPreference('prefs.gui-language');
            }

            attributeStore.setAttributes(preData.attributes);
            attributeStore.setAttributeSelections(preData.attributeSelections);
            userStore.setUsers(preData.users, preData.deleted_users);
            userStore.setRoles(preData.roles, preData.permissions, preData.presets);
            entityStore.initialize(preData.topEntities);
            bibliographyStore.initialize(preData.bibliography);
            this.setTags(preData.tags);
            this.version = preData.version;
            
            this.geometryTypes = preData.geometryTypes;
            attributeStore.setAttributeTypes(preData.attributeTypes);
        },
        async initializeOpenAccess() {
            return fetchGlobals().then(data => {
                this.concepts = data.concepts;
                this.preferences = data.preferences;
                return data;
            });
        },
        setTags(tags) {
            this.tags = tags;
        },
        setColorSets(data) {
            this.colorSets = data;
        },
        setAnalysis(state) {
            this.hasAnalysis = state;
        },
        setDatatypeData(data) {
            for(let k in data) {
                this.datatypeData[k] = data[k];
            }
        },
        async fetchConceptSelection(id) {
            const cachedSelection = this.cachedConceptSelections[id];
            if(!cachedSelection) {
                const selection = await searchConceptSelection(id);
                this.cachedConceptSelections[id] = selection || [];
            }
            return this.cachedConceptSelections[id];
        },
    },
});

export default useSystemStore;