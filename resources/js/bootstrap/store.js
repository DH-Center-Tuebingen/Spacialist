import { createStore } from 'vuex';

import { sortTree, Node, openPath } from '../helpers/tree.js';
import {
    can,
    fillEntityData,
    only,
} from '../helpers/helpers.js';
import {
    getEntityData,
    getEntityParentIds,
    getEntityReferences,
} from '../api.js';

export const store = createStore({
    state() {
        return {
            appInitialized: false,
            attributes: [],
            attributeTypes: [],
            attributeSelections: {},
            entityTypeAttributes: {},
            entityTypeColors: {},
            bibliography: [],
            concepts: {},
            deletedUsers: [],
            entity: {},
            entityTypes: {},
            entities: {},
            file: {},
            geometryTypes: [],
            mainView: {
                tab: 'references',
            },
            permissions: [],
            preferences: {},
            systemPreferences: {},
            roles: [],
            tree: [],
            user: {},
            users: [],
            version: {},
            plugins: [],
            registeredPluginSlots: {
                tab: [],
                tools: [],
                settings: [],
            },
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
        setAttributeTypes(state, data) {
            state.attributeTypes = data;
        },
        setAttributeSelections(state, data) {
            state.attributeSelections = data;
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
            const doCount = !n.already_existing;
            delete n.already_existing;
            state.entities[n.id] = n;
            if(!!n.root_entity_id) {
                const parent = state.entities[n.root_entity_id];
                if(parent.childrenLoaded) {
                    parent.children.push(n);
                }
                if(doCount) {
                    parent.children_count++;
                }
            }
        },
        addRootEntity(state, n) {
            state.entities[n.id] = n;
            state.tree.push(n);
        },
        updateEntity(state, data) {
            const entity = state.entities[data.id];
            entity.updated_at = data.updated_at;
            entity.user_id = data.user_id;
            entity.user = data.user;
            if(!!data.name) {
                entity.name = data.name;
            }
        },
        updateEntityType(state, data) {
            const entityType = state.entityTypes[data.id];
            entityType.updated_at = data.updated_at;
            entityType.thesaurus_url = data.thesaurus_url;
        },
        deleteEntity(state, data) {
            const entity = state.entities[data.id];
            if(entity.root_entity_id) {
                const parent = state.entities[entity.root_entity_id];
                if(parent.childrenLoaded) {
                    const idx = parent.children.findIndex(c => c.id == entity.id);
                    if(idx > -1) {
                        parent.children.splice(idx, 1);
                    }
                }
                parent.children_count--;
            } else {
                const idx = state.tree.findIndex(l => l.id == entity.id);
                if(idx > -1) {
                    state.tree.splice(idx, 1);
                }
            }
            delete state.entities[data.id];
        },
        updateEntityData(state, data) {
            const entity = state.entities[data.eid];
            for(let k in data.data) {
                entity.data[k].value = data.data[k];
            }
        },
        addEntityTypeAttribute(state, data) {
            const attrs = state.entityTypeAttributes[data.entity_type_id];
            attrs.splice(data.position-1, 0, data);
            for(let i=data.position; i<attrs.length; i++) {
                if(attrs[i].position) {
                    attrs[i].position++;
                } else if(attrs[i].pivot && attrs[i].pivot.position) {
                    attrs[i].pivot.position++;
                }
            }
        },
        removeEntityTypeAttribute(state, data) {
            const attrs = state.entityTypeAttributes[data.entity_type_id];
            const idx = attrs.findIndex(a => a.id == data.attribute_id);
            if(idx > -1) {
                attrs.splice(idx, 1);
            }
            for(let i=idx; i<attrs.length; i++) {
                if(attrs[i].position) {
                    attrs[i].position++;
                } else if(attrs[i].pivot && attrs[i].pivot.position) {
                    attrs[i].pivot.position++;
                }
            }
        },
        deleteAttribute(state, data) {
            const idx = state.attributes.findIndex(a => a.id == data.id);
            if(idx > -1) {
                state.attributes.splice(idx, 1);
            }
            // Also remove from entity type attribute lists
            for(let k in state.entityTypeAttributes) {
                const curr = state.entityTypeAttributes[k];
                const etIdx = curr.findIndex(a => a.id == data.id);
                if(etIdx > -1) {
                    curr.splice(etIdx, 1);
                }
            }
        },
        reorderAttributes(state, data) {
            const {
                rank,
                from,
                to,
                entity_type_id,
            } = data;
            const attrs = state.entityTypeAttributes[entity_type_id];
            attrs[from].position = rank;
            const movedAttrs = attrs.splice(from, 1);
            attrs.splice(to, 0, ...movedAttrs);
            if(from < to) {
                for(let i=from; i<to; i++) {
                    if(attrs[i].position) {
                        attrs[i].position++;
                    } else if(attrs[i].pivot && attrs[i].pivot.position) {
                        attrs[i].pivot.position++;
                    }
                }
            } else {
                for(let i=to+1; i<=from; i++) {
                    if(attrs[i].position) {
                        attrs[i].position--;
                    } else if(attrs[i].pivot && attrs[i].pivot.position) {
                        attrs[i].pivot.position--;
                    }
                }
            }
        },
        addEntityType(state, data) {
            if(data.attributes) {
                state.entityTypeAttributes[data.id] = data.attributes.slice();
                delete data.attributes;
            } else {
                state.entityTypeAttributes[data.id] = [];
            }
            state.entityTypes[data.id] = data;
        },
        addAttribute(state, data) {
            state.attributes.push(data);
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
        addRole(state, data) {
            state.roles.push(data);
        },
        updateRole(state, data) {
            const index = state.roles.findIndex(r => r.id == data.id);
            if(index > -1) {
                const cleanData = only(data, ['display_name', 'description', 'permissions', 'updated_at', 'deleted_at',]);
                const currentData = state.roles[index];
                state.roles[index] = {
                    ...currentData,
                    ...cleanData,
                };
            }
        },
        deleteRole(state, data) {
            const index = state.roles.findIndex(r => r.id == data.id);
            if(index > -1) {
                state.roles.splice(index, 1);
            }
        },
        deleteEntityType(state, data) {
            delete state.entityTypes[data.id];
            delete state.entityTypeAttributes[data.id];
        },
        updateDependency(state, data) {
            const attrs = state.entityTypeAttributes[data.entity_type_id];
            const attr = attrs.find(a => a.id == data.attribute_id);
            attr.pivot.depends_on = data.data;
        },
        addReference(state, data) {
            let entity = state.entities[data.entity_id];
            let references = entity.references[data.attribute_url];
            if(!references) {
                references = [];
            }
            delete data.attribute_url;
            references.push(data);
        },
        updateReference(state, data) {
            let references = state.entities[data.entity_id].references[data.attribute_url];
            const ref = references.find(r => {
                return r.id == data.reference_id;
            });
            if(!!ref) {
                for(let k in data.data) {
                    ref[k] = data.data[k];
                }
            }
        },
        removeReferenceFromEntity(state, data) {
            let references = state.entities[data.entity_id].references[data.attribute_url];
            const idx = references.findIndex(r => {
                return r.id == data.reference_id;
            });
            if(idx > -1) {
                references.splice(idx, 1);
            }
        },
        setConcepts(state, data) {
            state.concepts = data;
        },
        setMainViewTab(state, data) {
            state.mainView.tab = data;
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
        setEntityTypeColors(state, data) {
            state.entityTypeColors[data.id] = data.colors;
        },
        setFile(state, data) {
            state.file = data;
        },
        setVersion(state, data) {
            state.version = data;
        },
        setPlugins(state, data) {
            state.plugins = data;
        },
        registerPluginInSlot(state, data) {
            state.registeredPluginSlots[data.slot].push(data);
        },
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
        setMainViewTab({commit}, data) {
            commit('setMainViewTab', data);
        },
        async getEntity({commit, state}, entityId) {
            let entity = state.entities[entityId];
            if(!entity) {
                const ids = await getEntityParentIds(entityId);
                await openPath(ids);
                entity = state.entities[entityId];
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
                entity.references = await getEntityReferences(entityId);
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
        loadEntities({commit}, data) {
            let nodes = [];
            data.entities.forEach(e => {
                const n = new Node(e);
                commit('addEntity', {
                    ...n,
                    // flag to make sure to not increase children_count as we simply load already existing children
                    already_existing: true,
                });
                nodes.push(n);
            });
            sortTree(data.sort.by, data.sort.dir, nodes);
            return nodes;
        },
        addEntity({commit}, data) {
            const n = new Node(data);
            if(!!data.root_entity_id) {
                commit('addEntity', n);
            } else {
                commit('addRootEntity', n);
            }
            return n;
        },
        updateEntity({commit}, data) {
            commit('updateEntity', data);
        },
        updateEntityType({commit}, data) {
            commit('updateEntityType', data);
        },
        deleteEntity({commit}, data) {
            commit('deleteEntity', data);
        },
        updateEntityData({commit}, data) {
            commit('updateEntityData', data);
        },
        addEntityTypeAttribute({commit}, data) {
            commit('addEntityTypeAttribute', data);
        },
        removeEntityTypeAttribute({commit}, data) {
            commit('removeEntityTypeAttribute', data);
        },
        deleteAttribute({commit}, data) {
            commit('deleteAttribute', data);
        },
        reorderAttributes({commit}, data) {
            commit('reorderAttributes', data);
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
        addRole({commit}, data) {
            commit('addRole', data);
        },
        updateRole({commit}, data) {
            commit('updateRole', data);
        },
        deleteRole({commit}, data) {
            commit('deleteRole', data);
        },
        setEntityTypes({commit, state}, data) {
            state.entityTypes = {};
            state.entityTypeAttributes = {};
            commit('setEntityTypes', data);
        },
        setEntityTypeColors({commit}, data) {
            commit('setEntityTypeColors', data);
        },
        deleteEntityType({commit}, data) {
            commit('deleteEntityType', data);
        },
        updateDependency({commit}, data) {
            commit('updateDependency', data);
        },
        addReference({commit}, data) {
            commit('addReference', data);
        },
        updateReference({commit}, data) {
            commit('updateReference', data);
        },
        removeReferenceFromEntity({commit}, data) {
            commit('removeReferenceFromEntity', data);
        },
        setAttributes({commit, state}, data) {
            state.attributes = [];
            state.attributeSelections = {};
            commit('setAttributes', data.attributes);
            commit('setAttributeSelections', data.selections);
        },
        addAttribute({commit}, data) {
            commit('addAttribute', data);
        },
        setAttributeTypes({commit, state}, data) {
            state.attributeTypes = [];
            commit('setAttributeTypes', data);
        },
        setVersion({commit}, data) {
            commit('setVersion', data);
        },
        setPlugins({commit}, data) {
            commit('setPlugins', data);
        },
        registerPluginInSlot({commit}, data) {
            commit('registerPluginInSlot', data);
        },
    },
    getters: {
        appInitialized: state => {
            return state.appInitialized;
        },
        attributes: state => {
            return state.attributes;
        },
        attributeTypes: state => {
            return state.attributeTypes;
        },
        attributeTableTypes: state => {
            return state.attributeTypes.filter(at => at.in_table);
        },
        attributeSelections: state => {
            return state.attributeSelections;
        },
        bibliography: state => {
            return state.bibliography;
        },
        concepts: state => {
            return state.concepts;
        },
        entities: state => {
            return state.entities;
        },
        entityTypes: state => {
            return state.entityTypes;
        },
        entityTypeAttributes: state => id => {
            return state.entityTypeAttributes[id];
        },
        entityTypeColors: state => id => {
            return state.entityTypeColors[id];
        },
        geometryTypes: state => {
            return state.geometryTypes;
        },
        mainView: state => {
            return state.mainView;
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
        plugins: state => {
            return state.plugins;
        },
        slotPlugins: state => slot => {
            const p = state.registeredPluginSlots;
            return slot ? p[slot] : p;
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