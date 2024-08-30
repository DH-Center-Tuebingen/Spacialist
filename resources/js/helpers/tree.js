import TreeNode from '@/components/tree/Node.vue';

import {
    markRaw,
    ref,
 } from 'vue';
import store from '@/bootstrap/store.js';
import { getNodeFromPath } from 'tree-component';
import {
    fetchChildren as fetchChildrenApi,
} from '@/api.js';
import useEntityStore from '@/bootstrap/stores/entity.js';

import {
    getEntityTypeName,
} from '@/helpers/helpers.js';

export async function fetchChildren(id, sort = {by: 'rank', dir: 'asc'}) {
    const entityStore = useEntityStore();
    return fetchChildrenApi(id).then(data => {
        return entityStore.setDescendants({
            entities: data,
            sort: sort,
        });
    });
    
}

export function sortTree(by, dir, tree) {
    dir = dir == 'desc' ? dir : 'asc';
    let sortFn;
    switch(by) {
        case 'rank':
            sortFn = (a, b) => {
                let value = a.rank - b.rank;
                if(dir == 'desc') {
                    value = -value;
                }
                return value;
            };
            break;
        case 'alpha':
            sortFn = (a, b) => {
                let value = 0;
                if(a.name < b.name) value = -1;
                if(a.name > b.name) value = 1;
                if(dir == 'desc') {
                    value = -value;
                }
                return value;
            };
            break;
        case 'children':
            sortFn = (a, b) => {
                let value = a.children_count - b.children_count;
                if(dir == 'desc') {
                    value = -value;
                }
                return value;
            };
            break;
        case 'type':
            sortFn = (a, b) => {
                const aurl = getEntityTypeName(a.entity_type_id);
                const burl = getEntityTypeName(b.entity_type_id);
                let value = 0;
                if(aurl < burl) value = -1;
                if(aurl > burl) value = 1;
                if(dir == 'desc') {
                    value = -value;
                }
                return value;
            };
            break;
    }
    sortTreeLevel(tree, sortFn);
}

function sortTreeLevel(tree, fn) {
    if(!tree) return;

    const treeVal = Array.isArray(tree) ? tree : tree.value;

    treeVal.sort(fn);
    treeVal.forEach(n => {
        if(n.childrenLoaded) {
            sortTreeLevel(n.children, fn);
        }
    });
}

export async function openPath(ids, sort = {by: 'rank', dir: 'asc'}) {
    const index = ids.pop();
    const elem = store.getters.entities[index];
    if(ids.length == 0) {
        return elem;
    }
    if(!elem.childrenLoaded) {
        elem.state.loading = true;
        const children = await fetchChildren(elem.id, sort);
        elem.state.loading = false;
        elem.children = children;
        elem.childrenLoaded = true;
        // Have to get current elemen from tree (not entities array) as well
        // otherwise children and childrenLoaded props are not correctly set
        const htmlElem = document.getElementById(`tree-node-${elem.id}`).parentElement;
        const node = getNodeFromPath(store.getters.tree, htmlElem.getAttribute('data-path').split(','));
        node.children = children;
        node.childrenLoaded = true;
    }
    elem.state.opened = true;
    return openPath(ids, elem.children);
}

export class Node {
    constructor(data, component) {
        Object.assign(this, data);
        this.text = this.name;
        this.state = {
            opened: false,
            selected: false,
            disabled: false,
            loading: false,
            highlighted: false,
            openable: this.children_count > 0,
            dropPosition: 0,
            dropAllowed: true,
        };
        this.icon = false;
        this.children = ref([]);
        this.childrenLoaded = ref(this.children.length == this.children_count);
        this.children_count = ref(this.children_count);
        this.component = component || markRaw(TreeNode);
        // this.dragDelay = vm.dragDelay;
        // this.dragAllowed = _ => vm.isDragAllowed;
        // this.onToggle = vm.itemToggle;
    }
}