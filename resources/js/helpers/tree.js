import TreeNode from '../components/tree/Node.vue';
import store from '../bootstrap/store.js';
import {
    fetchChildren as fetchChildrenApi,
} from '../api.js';

import {
    translateEntityType,
} from './helpers';

export async function fetchChildren(id, sort = {by: 'rank', dir: 'asc'}) {
    return fetchChildrenApi(id).then(data => {
        return store.dispatch('loadEntities', {
            entities: data,
            sort: sort,
        });
    });
    
};

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
                const aurl = translateEntityType(a.entity_type_id);
                const burl = translateEntityType(b.entity_type_id);
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
    tree.sort(fn);
    tree.forEach(n => {
        if(n.childrenLoaded) {
            sortTreeLevel(n.children, fn);
        }
    });
}

export class Node {
    constructor(data, vm) {
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
        this.children = [];
        this.childrenLoaded = this.children.length == this.children_count;
        this.component = TreeNode;
        // this.dragDelay = vm.dragDelay;
        // this.dragAllowed = _ => vm.isDragAllowed;
        // this.onToggle = vm.itemToggle;
    }
}