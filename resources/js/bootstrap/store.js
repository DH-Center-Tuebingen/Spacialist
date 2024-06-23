import { createStore } from 'vuex';

import {
    sortTree,
    Node,
    openPath,
} from '@/helpers/tree.js';
import {
    can,
    fillEntityData,
    only,
    slugify,
    sortConcepts,
    getIntersectedEntityAttributes,
    hasIntersectionWithEntityAttributes,
} from '@/helpers/helpers.js';
import {
    getEntityData,
    getEntityParentIds,
    getEntityReferences,
} from '@/api.js';

function updateSelectionTypeIdList(selection) {
    const tmpDict = {};
    for(let k in selection) {
        const curr = selection[k];
        if(!tmpDict[curr.entity_type_id]) {
            tmpDict[curr.entity_type_id] = 1;
        }
    }
    return Object.keys(tmpDict).map(tdk => parseInt(tdk));
}

export const store = createStore({
    modules: {
        core: {
            namespaced: false,
            state() {
                return {
                    appInitialized: false,
                    userLoggedIn: false,
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
                    datatypeData: {},
                    tags: [],
                    roles: [],
                    rolePresets: [],
                    tree: [],
                    treeSelectionMode: false,
                    treeSelection: {},
                    treeSelectionTypeIds: [],
                    cachedConceptSelections: {},
                    user: {},
                    users: [],
                    version: {},
                    plugins: [],
                    colorsets: [],
                    registeredPluginSlots: {
                        tab: [],
                        tools: [],
                        settings: [],
                    },
                    registeredPluginPreferences: {
                        user: {},
                        system: {},
                    },
                    hasAnalysis: false,
                };
            },
            mutations: {
                setAppInitialized(state, data) {
                    state.appInitialized = data;
                },
                setUserLogin(state, data) {
                    state.userLoggedIn = data;
                },
                setAttributes(state, data) {
                    state.attributes = data;
                },
                setAttributeTypes(state, data) {
                    state.attributeTypes = data;
                },
                setAttributeSelection(state, data) {
                    if(data.nested) {
                        for(let k in data.selection) {
                            state.attributeSelections[k] = data.selection[k].sort(sortConcepts);
                        }
                    } else {
                        state.attributeSelections[data.id] = data.selection.sort(sortConcepts);
                    }
                },
                setAttributeSelections(state, data) {
                    for(let k in data) {
                        data[k].sort(sortConcepts);
                    }
                    state.attributeSelections = data;
                },
                setBibliography(state, data) {
                    state.bibliography = data;
                },
                addBibliographyItem(state, data) {
                    state.bibliography.push(data);
                },
                updateBibliographyItem(state, data) {
                    const entry = state.bibliography.find(e => e.id == data.id);
                    entry.type = data.type;
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
                        if(!!parent) {
                            if(parent.childrenLoaded) {
                                parent.children.push(n);
                            }
                            if(doCount) {
                                parent.children_count++;
                                parent.state.openable = true;
                            }
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
                updateEntityModificationState(state, data) {
                    const entity = state.entities[data.id];
                    entity.reverb_state = data.status;
                },
                updateEntityType(state, data) {
                    const entityType = state.entityTypes[data.id];
                    const values = only(data, ['thesaurus_url', 'updated_at', 'is_root', 'sub_entity_types']);
                    for(let k in values) {
                        entityType[k] = values[k];
                    }
                },
                moveEntity(state, data) {
                    const entity = state.entities[data.entity_id];
                    const oldRank = entity.rank;
                    const newRank = data.rank;
                    const rankIdx = newRank - 1;
                    const append = data.to_end;

                    entity.rank = newRank;

                    let oldSiblings;
                    if(!!entity.root_entity_id) {
                        oldSiblings = state.entities[entity.root_entity_id].children;
                    } else {
                        oldSiblings = state.tree;
                    }
                    const idx = oldSiblings.findIndex(n => n.id == entity.id);
                    if(idx > -1) {
                        oldSiblings.splice(idx, 1);
                        oldSiblings.map(s => {
                            if(s.rank > oldRank) {
                                s.rank--;
                            }
                        });
                    }

                    // Update children state of old parent
                    if(!!entity.root_entity_id) {
                        const oldParent = state.entities[entity.root_entity_id];
                        oldParent.children_count--;
                        if(oldParent.children_count == 0) {
                            oldParent.state.openable = false;
                            oldParent.state.opened = false;
                        }
                    }

                    if(!data.parent_id) {
                        // Set new (= unset) parent
                        entity.root_entity_id = null;
                        if(append) {
                            state.tree.push(entity);
                        } else {
                            state.tree.splice(rankIdx, 0, entity);
                            state.tree.map(s => {
                                if(s.rank >= newRank) {
                                    s.rank++;
                                }
                            });
                        }
                    } else {
                        // Update children state of new parent
                        const parent = state.entities[data.parent_id];
                        if(!!parent) {
                            if(parent.childrenLoaded) {
                                if(append) {
                                    parent.children.push(entity);
                                } else {
                                    parent.children.splice(rankIdx, 0, entity);
                                    parent.children.map(s => {
                                        if(s.rank >= newRank) {
                                            s.rank++;
                                        }
                                    });
                                }
                            }
                            parent.children_count++;
                            parent.state.openable = true;
                        }
                        // Set new parent
                        entity.root_entity_id = data.parent_id;
                    }
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
                        parent.state.openable = parent.children_count > 0;
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
                        // when attribute value is set empty, delete whole attribute
                        if(!data.data[k] && data.data[k] != false) {
                            entity.data[k] = {};
                            if(data.sync) {
                                state.entity.data[k] = {};
                            }
                        } else {
                            // if no id exists, this data is added
                            if(!entity.data[k].id) {
                                entity.data[k] = data.new_data[k];
                                if(data.sync) {
                                    state.entity.data[k] = data.new_data[k];
                                }
                            } else {
                                entity.data[k] = data.data[k];
                                if(data.sync) {
                                    state.entity.data[k] = data.data[k];
                                }
                            }
                        }
                    }
                },
                updateEntityDataModerations(state, data) {
                    const entity = state.entities[data.entity_id];
                    for(let i=0; i<data.attribute_ids.length; i++) {
                        const curr = data.attribute_ids[i];
                        entity.data[curr].moderation_state = data.state;
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
                    const idx = attrs.findIndex(a => a.pivot.id == data.attribute_id);
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
                    const {
                        is_profile_data,
                        ...userData
                    } = data;
                    const index = state.users.findIndex(u => u.id == userData.id);
                    if(index > -1) {
                        let allowedProps = [
                            'email',
                            'roles',
                            'updated_at',
                            'deleted_at',
                            'login_attempts',
                        ];
                        if(is_profile_data) {
                            allowedProps.push(
                                'nickname',
                                'metadata',
                            );
                        }

                        const cleanData = only(data, allowedProps);
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
                        const cleanData = only(data, ['display_name', 'description', 'permissions', 'is_moderated', 'updated_at', 'deleted_at',]);
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
                updateAttributeMetadata(state, data) {
                    const attrs = state.entityTypeAttributes[data.entity_type_id];
                    const attr = attrs.find(a => a.id == data.attribute_id && a.pivot.id == data.id);
                    attr.pivot.metadata = data.data;
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
                addToTreeSelection(state, data) {
                    const addPossible = hasIntersectionWithEntityAttributes(data.value.entity_type_id, state.treeSelectionTypeIds);
                    if(addPossible || state.treeSelectionTypeIds.length == 0) {
                        state.treeSelection[data.id] = data.value;

                        state.treeSelectionTypeIds = [];
                        state.treeSelectionTypeIds = updateSelectionTypeIdList(state.treeSelection);
                    }
                },
                removeFromTreeSelection(state, data) {
                    delete state.treeSelection[data.id];

                    state.treeSelectionTypeIds = [];
                    state.treeSelectionTypeIds = updateSelectionTypeIdList(state.treeSelection);
                },
                toggleTreeSelectionMode(state) {
                    state.treeSelectionMode = !state.treeSelectionMode;

                    if(!state.treeSelectionMode) {
                        state.treeSelection = {};
                        state.treeSelectionTypeIds = [];
                    }
                },
                setTreeSelectionMode(state, data) {
                    state.treeSelectionMode = data;

                    if(!state.treeSelectionMode) {
                        state.treeSelection = {};
                        state.treeSelectionTypeIds = [];
                    }
                },
                setCachedConceptSelection(state, data) {
                    state.cachedConceptSelections[data.id] = data.selection;
                },
                setPreferences(state, data) {
                    state.preferences = data;
                },
                setSystemPreferences(state, data) {
                    state.systemPreferences = data;
                },
                setTags(state, data) {
                    state.tags = data;
                },
                setRoles(state, data) {
                    state.roles = data;
                },
                setRolePresets(state, data) {
                    state.rolePresets = data;
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
                addPlugin(state, data) {
                    const idx = state.plugins.findIndex(p => p.id == data.id);
                    if(idx > -1) {
                        state.plugins[idx] = data;
                    } else {
                        state.plugins.push(data);
                    }
                },
                updatePlugin(state, data) {
                    const idx = state.plugins.findIndex(p => p.id == data.plugin_id);
                    if(idx == -1) return;

                    let plugin = null;
                    let remove = false;

                    if(data.deleted) {
                        const delPlugins = state.plugins.splice(idx, 1);
                        plugin = delPlugins[0];
                        remove = true;
                    } else {
                        const props = only(data.properties, ['installed_at', 'updated_at', 'update_available', 'version']);
                        const updPlugin = state.plugins[idx];
                        for(let k in props) {
                            updPlugin[k] = props[k];
                        }
                        
                        if(data.uninstalled) {
                            plugin = updPlugin;
                            remove = true;
                        }
                    }

                    if(plugin && remove) {
                        const slots = state.registeredPluginSlots;
                        const pluginId = slugify(plugin.name);
                        for(let k in slots) {
                            const slot = slots[k];
                            slot.forEach(slotPlugin => {
                                if(slotPlugin.of == pluginId) {
                                    const spIdx = slot.findIndex(sp => sp.of == pluginId);
                                    slot.splice(spIdx, 1);
                                }
                            });
                        }
                    }
                },
                registerPluginInSlot(state, data) {
                    state.registeredPluginSlots[data.slot].push(data);
                },
                registerPluginPreference(state, data) {
                    const category = state.registeredPluginPreferences[data.category];
                    if(!category[data.subcategory]) {
                        category[data.subcategory] = {
                            preferences: [],
                        };
                    }
                    const pref = {
                        title: data.label,
                        label: data.key,
                        component: data.component,
                        default_value: data.default_value,
                    };
                    if(data.data) {
                        pref.data = data.data;
                    }
                    if(data.custom_subcategory) {
                        category[data.subcategory].custom = true;
                        category[data.subcategory].title = data.custom_label;
                    }
                    category[data.subcategory].preferences.push(pref);
                },
                setColorSets(state, data) {
                    state.colorSets = data;
                },
                setAnalysis(state, data) {
                    state.hasAnalysis = data;
                },
                setDatatypeData(state, data) {
                    for(let k in data) {
                        state.datatypeData[k] = data[k];
                    }
                },
            },
            actions: {
                setAppState({commit}, data) {
                    commit('setAppInitialized', data);
                },
                setUserLogin({commit}, data) {
                    commit('setUserLogin', data);
                },
                setBibliography({commit}, data) {
                    commit('setBibliography', data);
                },
                updateBibliography({commit}, data) {
                    data.forEach(itemWrap => {
                        if(itemWrap.added) {
                            commit('addBibliographyItem', itemWrap.entry);
                        } else {
                            commit('updateBibliographyItem', itemWrap.entry);
                        }
                    });
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
                setTags({commit}, data) {
                    commit('setTags', data);
                },
                setRoles({commit}, data) {
                    commit('setRoles', data.roles);
                    commit('setPermissions', data.permissions);
                    commit('setRolePresets', data.presets);
                },
                setUsers({commit}, data) {
                    commit('setUsers', data);
                },
                setUser({commit}, data) {
                    commit('setUser', data);
                },
                sortTree({commit}, sort) {
                    commit('sortTree', sort);
                },
                addToTreeSelection({commit}, data) {
                    commit('addToTreeSelection', data);
                },
                removeFromTreeSelection({commit}, data) {
                    commit('removeFromTreeSelection', data);
                },
                toggleTreeSelectionMode({commit}) {
                    commit('toggleTreeSelectionMode');
                },
                setTreeSelectionMode({commit}) {
                    commit('setTreeSelectionMode', true);
                },
                unsetTreeSelectionMode({commit}) {
                    commit('setTreeSelectionMode', false);
                },
                setCachedConceptSelection({commit}, data) {
                    commit('setCachedConceptSelection', data);
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
                    if(!can('entity_data_read')) {
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
                        entity.references = await getEntityReferences(entityId) || {};
                        for(let k in entity.data) {
                            const curr = entity.data[k];
                            if(curr.attribute) {
                                const key = curr.attribute.thesaurus_url;
                                if(!entity.references[key]) {
                                    entity.references[key] = [];
                                }
                            }
                        }
                        commit('setEntity', entity);
                        return;
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
                updateEntityModificationState({commit}, data) {
                    if (data.status == 'added') {
                        const n = new Node(data.data);
                        if (!!data.data.root_entity_id) {
                            commit('addEntity', n);
                        } else {
                            commit('addRootEntity', n);
                        }
                    }
                    commit('updateEntityModificationState', data);
                },
                updateEntityType({commit}, data) {
                    commit('updateEntityType', data);
                },
                moveEntity({commit}, data) {
                    commit('moveEntity', data);
                },
                deleteEntity({commit}, data) {
                    commit('deleteEntity', data);
                },
                updateEntityData({commit}, data) {
                    commit('updateEntityData', data);
                },
                updateEntityDataModerations({commit}, data) {
                    commit('updateEntityDataModerations', data);
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
                updateUserProfile({commit}, data) {
                    commit('setUser', data);
                    commit('updateUser', {
                        ...data,
                        is_profile_data: true,
                    });
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
                updateAttributeMetadata({commit}, data) {
                    commit('updateAttributeMetadata', data);
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
                    commit('addAttribute', data.attribute);
                    if(data.selection) {
                        commit('setAttributeSelection', {
                            id: data.attribute.id,
                            nested: data.attribute.datatype == 'table',
                            selection: data.selection,
                        });
                    }
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
                addPlugin({commit}, data) {
                    commit('addPlugin', data);
                },
                updatePlugin({commit}, data) {
                    commit('updatePlugin', data);
                },
                registerPluginInSlot({commit}, data) {
                    commit('registerPluginInSlot', data);
                },
                registerPluginPreference({commit}, data) {
                    commit('registerPluginPreference', data);
                },
                setColorSets({commit}, data) {
                    commit('setColorSets', data);
                },
                setAnalysis({commit}, data) {
                    commit('setAnalysis', data);
                },
                setDatatypeData({commit}, data) {
                    commit('setDatatypeData', data);
                },
            },
            getters: {
                appInitialized: state => state.appInitialized,
                loggedIn: state => state.userLoggedIn,
                attributes: state => state.attributes,
                attributeTypes: state => state.attributeTypes,
                attributeTableTypes: state => state.attributeTypes.filter(at => at.in_table),
                attributeSelections: state => state.attributeSelections,
                bibliography: state => state.bibliography,
                concepts: state => state.concepts,
                entities: state => state.entities,
                entityTypes: state => state.entityTypes,
                entityTypeAttributes: state => (id, exclude = false) => {
                    if(exclude === true) {
                        return state.entityTypeAttributes[id].filter(a => a.datatype != 'system-separator');
                    } else if(Array.isArray(exclude)) {
                        return state.entityTypeAttributes[id].filter(a => !exclude.includes(a.datatype));
                    }

                    return state.entityTypeAttributes[id];
                },
                entityTypeColors: state => id => state.entityTypeColors[id],
                geometryTypes: state => state.geometryTypes,
                mainView: state => state.mainView,
                tree: state => state.tree,
                treeSelectionMode: state => state.treeSelectionMode,
                treeSelection: state => state.treeSelection,
                treeSelectionCount: state => Object.keys(state.treeSelection).length,
                treeSelectionTypeIds: state => state.treeSelectionTypeIds,
                treeSelectionIntersection: state => getIntersectedEntityAttributes(state.treeSelectionTypeIds),
                cachedConceptSelections: state => state.cachedConceptSelections,
                cachedConceptSelection: state => id => state.cachedConceptSelections[id],
                preferenceByKey: state => key => state.preferences[key],
                preferences: state => state.preferences,
                systemPreferences: state => state.systemPreferences,
                datatypeData: state => state.datatypeData,
                datatypeDataOf: state => key => state.datatypeData[key],
                tags: state => state.tags,
                roles: state => noPerms => {
                    return noPerms ? state.roles.map(r => {
                        // Remove permissions from role
                        let {permissions, ...role} = r;
                        return role;
                    }) : state.roles;
                },
                rolePresets: state => state.rolePresets,
                permissions: state => state.permissions,
                allUsers: state => {
                    return [
                        ...state.users,
                        ...state.deletedUsers
                    ];
                },
                users: state => state.users,
                deletedUsers: state => state.deletedUsers,
                user: state => state.user,
                isLoggedIn: state => !!state.user,
                isModerated: state => state.user.roles.some(r => r.is_moderated),
                entity: state => state.entity,
                file: state => state.file,
                version: state => state.version,
                plugins: state => state.plugins,
                slotPlugins: state => slot => {
                    const p = state.registeredPluginSlots;
                    return slot ? p[slot] : p;
                },
                pluginPreferences: state => state.registeredPluginPreferences,
                pluginPreferencesInCategory: state => cat => {
                    return state.registeredPluginPreferences[cat];
                },
                colorSets: state => state.colorSets,
                hasAnalysis: state => state.hasAnalysis,
            }
        },
        pluginstore: {
            namespaced: true,
            state: () => ({}),
            mutations: {},
            actions: {},
            getters: {},
            modules: {},
        },
    },
});

export function useStore() {
    return store;
}

export default store;