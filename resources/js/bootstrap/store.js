import { createStore } from 'vuex';

import { sortTree, Node } from '../helpers/tree.js';

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
            // return state.tree;
            return [
                  {
                    text: 'node 1',
                    value: { id: 1 },
                    state: {
                    opened: true
                    },
                    children: [
                    {
                        text: 'node 11',
                        value: { id: 11 }
                    },
                    {
                        text: 'node 12',
                        value: { id: 12 },
                        state: {
                        opened: true
                        },
                        children: [
                        {
                            text: 'node 121',
                            value: { id: 121 }
                        },
                        {
                            text: 'node 122',
                            value: { id: 122 }
                        },
                        {
                            text: 'node 123',
                            value: { id: 123 }
                        }
                        ]
                    }
                    ]
                },
            ]
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