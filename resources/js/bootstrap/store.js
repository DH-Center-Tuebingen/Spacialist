import { createStore } from 'vuex';

import { sortTree, Node } from '../helpers/tree.js';
import {
    can,
    fillEntityData,
    only,
} from '../helpers/helpers.js';
import { getEntityData } from '../api.js';

export const store = createStore({
    state() {
        return {
            appInitialized: false,
            attributes: [],
            attributeSelections: {},
            attributeDependencies: {},
            entityTypeAttributes: {},
            bibliography: [],
            concepts: {},
            deletedUsers: [],
            entity: {},
            entityTypes: {},
            entities: {},
            file: {},
            geometryTypes: [],
            permissions: [],
            preferences: {},
            systemPreferences: {},
            roles: [],
            tree: [],
            user: {},
            users: [],
            version: {},
            vfm: {},
        }
    },
    mutations: {
        setAppInitialized(state, data) {
            state.appInitialized = data;
        },
        setModalInstance(state, data) {
            state.vfm = data;
        },
        setAttributes(state, data) {
            state.attributes = data;
        },
        setAttributeSelections(state, data) {
            state.attributeSelections = data;
        },
        setAttributeDependencies(state, data) {
            state.attributeDependencies = data;
        },
        setBibliography(state, data) {
            state.bibliography = data;
        },
        addBibliographyItem(state, data) {
            state.bibliography.push(data);
        },
        updateBibliographyItem(state, data) {
            let entry = state.bibliography.find(e => e.id == data.id);
            for(let k in data.fields) {
                entry[k] = data.fields[k];
            }
        },
        deleteBibliographyItem(state, data) {
            const idx = state.bibliography.findIndex(e => e.id == data.id);
            if(idx > -1) {
                state.bibliography.splice(idx, 1);
            }
        },
        addEntity(state, n) {
            state.entities[n.id] = n;
        },
        addEntityType(state, data) {
            state.entityTypeAttributes[data.id] = data.attributes.slice();
            delete data.attributes;
            state.entityTypes[data.id] = data;
        },
        addUser(state, data) {
            state.users.push(data);
        },
        updateUser(state, data) {
            const index = state.users.findIndex(u => u.id == data.id);
            if(index > -1) {
                const cleanData = only(data, ['email', 'roles', 'updated_at', 'deleted_at',]);
                const currentData = state.users[index];
                state.users[index] = {
                    ...currentData,
                    ...cleanData,
                };
            }
        },
        deactivateUser(state, data) {
            const index = state.users.findIndex(u => u.id == data.id);
            if(index > -1) {
                const delUser = state.users.splice(index, 1)[0];
                delUser.deleted_at = data.deleted_at;
                state.deletedUsers.push(delUser);
            }
        },
        reactivateUser(state, data) {
            const index = state.deletedUsers.findIndex(u => u.id == data);
            if(index > -1) {
                const reacUser = state.deletedUsers.splice(index, 1)[0];
                state.users.push(reacUser);
            }
        },
        deleteEntityType(state, data) {
            delete state.entityTypes[data.id];
            delete state.entityTypeAttributes[data.id];
        },
        addRootEntity(state, n) {
            state.entities[n.id] = n;
            state.tree.push(n);
        },
        setConcepts(state, data) {
            state.concepts = data;
        },
        setEntityTypes(state, data) {
            for(let k in data) {
                const et = data[k];
                state.entityTypeAttributes[et.id] = et.attributes.slice();
                delete et.attributes;
            }
            state.entityTypes = data;
        },
        setGeometryTypes(state, data) {
            state.geometryTypes = [];
            state.geometryTypes = data;
        },
        sortTree(state, sort) {
            sortTree(sort.by, sort.dir, state.tree);
        },
        setPreferences(state, data) {
            state.preferences = data;
        },
        setSystemPreferences(state, data) {
            state.systemPreferences = data;
        },
        setRoles(state, data) {
            state.roles = data;
        },
        setPermissions(state, data) {
            state.permissions = data;
        },
        setUsers(state, data) {
            state.users = data.active;
            state.deletedUsers = data.deleted;
        },
        setUser(state, data) {
            state.user = data;
        },
        setEntity(state, data) {
            state.entity = data;
        },
        setEntityComments(state, data) {
            if(!state.entity) return;
            state.entity.comments = data;
        },
        setFile(state, data) {
            state.file = data;
        },
        setVersion(state, data) {
            state.version = data;
        }
    },
    actions: {
        setAppState({commit}, data) {
            commit('setAppInitialized', data);
        },
        setModalInstance({commit}, data) {
            commit('setModalInstance', data);
        },
        setBibliography({commit}, data) {
            commit('setBibliography', data);
        },
        addBibliographyItem({commit}, data) {
            commit('addBibliographyItem', data);
        },
        updateBibliographyItem({commit}, data) {
            commit('updateBibliographyItem', data);
        },
        deleteBibliographyItem({commit}, data) {
            commit('deleteBibliographyItem', data);
        },
        setGeometryTypes({commit}, data) {
            commit('setGeometryTypes', data);
        },
        setRoles({commit}, data) {
            commit('setRoles', data.roles);
            commit('setPermissions', data.permissions);
        },
        setUsers({commit}, data) {
            commit('setUsers', data);
        },
        sortTree({commit}, sort) {
            commit('sortTree', sort)
        },
        async getEntity({commit, state}, entityId) {
            let entity = state.entities[entityId];
            if(!entity) {
                // TODO get entity data (parent ids)
                entity = {};
            }
            if(!can('view_concept_props')) {
                const hiddenEntity = {
                    ...entity,
                    data: {},
                    attributes: [],
                    selections: {},
                    dependencies: [],
                    references: [],
                    comments: [],
                };
                fillEntityData(entity.data, entity.entity_type_id);
                commit('setEntity', hiddenEntity);
            } else {
                entity.data = await getEntityData(entityId);
                fillEntityData(entity.data, entity.entity_type_id);
                commit('setEntity', entity);
                
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
        },
        setEntityComments({commit}, data) {
            commit('setEntityComments', data);
        },
        resetEntity({commit}) {
            commit('setEntity', {});
        },
        setInitialEntities({commit, state}, data) {
            state.tree = [];
            state.entities = {};

            data.forEach(e => {
                const n = new Node(e);
                commit('addRootEntity', n);
            });
            sortTree('rank', 'asc', state.tree);
        },
        addEntities({commit}, data) {
            let nodes = [];
            data.entities.forEach(e => {
                const n = new Node(e);
                commit('addEntity', n);
                nodes.push(n);
            });
            sortTree(data.sort.by, data.sort.dir, nodes);
            return nodes;
        },
        addEntity({commit}, data) {
            const n = new Node(data);
            commit('addEntity', n);
        },
        addEntityType({commit}, data) {
            commit('addEntityType', data);
        },
        addUser({commit}, data) {
            commit('addUser', data);
        },
        updateUser({commit}, data) {
            commit('updateUser', data);
        },
        deactivateUser({commit}, data) {
            commit('deactivateUser', data);
        },
        reactivateUser({commit}, data) {
            commit('reactivateUser', data);
        },
        setEntityTypes({commit, state}, data) {
            state.entityTypes = {};
            state.entityTypeAttributes = {};
            commit('setEntityTypes', data);
        },
        deleteEntityType({commit}, data) {
            commit('deleteEntityType', data);
        },
        setAttributes({commit, state}, data) {
            state.attributes = [];
            state.attributeSelections = {};
            state.attributeDependencies = {};
            commit('setAttributes', data.attributes);
            commit('setAttributeSelections', data.selections);
            commit('setAttributeDependencies', data.dependencies);
        },
        setVersion({commit}, data) {
            commit('setVersion', data);
        },
    },
    getters: {
        appInitialized: state => {
            return state.appInitialized;
        },
        attributes: state => {
            return state.attributes;
        },
        attributeSelections: state => {
            return state.attributeSelections;
        },
        attributeDependencies: state => {
            return state.attributeDependencies;
        },
        bibliography: state => {
            return state.bibliography;
        },
        concepts: state => {
            return state.concepts;
        },
        entityTypes: state => {
            return state.entityTypes;
        },
        entityTypeAttributes: state => id => {
            return state.entityTypeAttributes[id];
        },
        geometryTypes: state => {
            return state.geometryTypes;
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
        systemPreferences: state => {
            return state.systemPreferences;
        },
        roles: state => noPerms => {
            return noPerms ? state.roles.map(r => {
                // Remove permissions from role
                let {permissions, ...role} = r;
                return role;
            }) : state.roles;
        },
        permissions: state => {
            return state.permissions;
        },
        allUsers: state => {
            return [
                ...state.users,
                ...state.deletedUsers
            ];
        },
        users: state => {
            console.log("state.users", state.users);
            return state.users;
        },
        deletedUsers: state => {
            return state.deletedUsers;
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
        version: state => {
            return state.version;
        },
        vfm: state => {
            return state.vfm;
        },
    }
});

export function useStore() {
    return store;
}

export default store;