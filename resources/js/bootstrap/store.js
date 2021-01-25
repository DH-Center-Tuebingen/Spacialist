import { createStore } from 'vuex';

import { sortTree, Node } from '../helpers/tree.js';
import { can } from '../helpers/helpers.js';
import { getEntityData } from '../api.js';

export const store = createStore({
    state() {
        return {
            concepts: {},
            entityTypes: {},
            topEntities: [],
            entities: {},
            tree: [],
            preferences: {},
            users: [],
            user: {},
            entity: {},
            file: {}
        }
    },
    mutations: {
        setConcepts(state, data) {
            state.concepts = data;
        },
        setEntityTypes(state, data) {
            state.entityTypes = data;
        },
        setTopEntities(state, data) {
            state.tree = [];
            state.entities = {};

            state.topEntities = data;
            data.forEach(e => {
                const n = new Node(e);
                state.entities[n.id] = n;
                state.tree.push(n);
            });
            sortTree('rank', 'asc', state.tree);
        },
        sortTree(state, sort) {
            sortTree(sort.by, sort.dir, state.tree);
        },
        setPreferences(state, data) {
            state.preferences = data;
        },
        setUsers(state, data) {
            state.users = data;
        },
        setUser(state, data) {
            state.user = data;
        },
        setEntity(state, data) {
            state.entity = data;
        },
        setFile(state, data) {
            state.file = data;
        }
    },
    actions: {
        sortTree({commit}, sort) {
            commit('sortTree', sort)
        },
        async getEntity({commit}, entityId) {
            if(!can('view_concept_props')) {
                const hiddenEntity = {
                    id: entity.id,
                    // TODO: parents?
                    data: {},
                    attributes: [],
                    selections: {},
                    dependencies: [],
                    references: [],
                    comments: [],
                };
                commit('setEntity', hiddenEntity);
            } else {
                let entity = {};
                entity.data = await getEntityData(entityId);
                
                return;

                return $httpQueue.add(() => $http.get(`/entity/${cid}/data`).then(response => {
                    // if result is empty, php returns [] instead of {}
                    if(response.data instanceof Array) {
                        response.data = {};
                    }
                    Vue.set(this.selectedEntity, 'data', response.data);
                    return $http.get(`/editor/entity_type/${ctid}/attribute`);
                }).then(response => {
                    this.selectedEntity.attributes = [];
                    let data = response.data;
                    for(let i=0; i<data.attributes.length; i++) {
                        let aid = data.attributes[i].id;
                        if(!this.selectedEntity.data[aid]) {
                            let val = {};
                            switch(data.attributes[i].datatype) {
                                case 'dimension':
                                case 'epoch':
                                case 'timeperiod':
                                    val.value = {};
                                    break;
                                case 'table':
                                case 'list':
                                    val.value = [];
                                    break;
                            }
                            Vue.set(this.selectedEntity.data, aid, val);
                        } else {
                            const val = this.selectedEntity.data[aid].value;
                            switch(data.attributes[i].datatype) {
                                case 'date':
                                    const dtVal = new Date(val);
                                    this.selectedEntity.data[aid].value = dtVal;
                                    break;
                            }
                        }
                        this.selectedEntity.attributes.push(data.attributes[i]);
                    }
                    // if result is empty, php returns [] instead of {}
                    if(data.selections instanceof Array) {
                        data.selections = {};
                    }
                    if(data.dependencies instanceof Array) {
                        data.dependencies = {};
                    }
                    Vue.set(this.selectedEntity, 'selections', data.selections);
                    Vue.set(this.selectedEntity, 'dependencies', data.dependencies);

                    const aid = this.$route.params.aid;
                    this.setReferenceAttribute(aid);
                    Vue.set(this, 'dataLoaded', true);
                    this.setEntityView();
                }));
            }
        }
    },
    getters: {
        concepts: state => {
            return state.concepts;
        },
        entityTypes: state => {
            return state.entityTypes;
        },
        topEntities: state => {
            return state.topEntities;
        },
        tree: state => {
            return state.tree;
        },
        preferenceByKey: state => key => {
            return state.preferences[key];
        },
        preferences: state => {
            return state.preferences;
        },
        users: state => {
            return state.users;
        },
        user: state => {
            return state.user;
        },
        isLoggedIn: state => {
            return !!state.user;
        },
        entity: state => {
            return state.entity;
        },
        file: state => {
            return state.file;
        },
    }
});

export function useStore() {
    return store;
}

export default store;