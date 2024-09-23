import {
    defineStore,
} from 'pinia';

import {
    addOrUpdateBibliographyItem,
    deleteBibliographyItem,
    importBibliographyFile,
} from '@/api.js';

export const useBibliographyStore = defineStore('bibliography', {
    state: _ => ({
        bibliography: [],
    }),
    getters: {
        getEntry: state => id => {
            return state.bibliography.find(entry => entry.id == id);
        },
    },
    actions: {
        initialize(items) {
            this.bibliography = items;
        },
        addBibliographyItem(data) {
            this.bibliography.push(data);
        },
        updateBibliographyItem(data) {
            const entry = this.bibliography.find(e => e.id == data.id);
            entry.type = data.type;
            for(let k in data.fields) {
                entry[k] = data.fields[k];
            }
        },
        deleteBibliographyItem(id) {
            const idx = this.bibliography.findIndex(e => e.id == id);
            if(idx > -1) {
                this.bibliography.splice(idx, 1);
            }
        },
        updateBibliography(items) {
            items.forEach(wrappedItem => {
                if(wrappedItem.added) {
                    this.addBibliographyItem(wrappedItem.entry);
                } else {
                    this.updateBibliographyItem(wrappedItem.entry);
                }
            });
        },
        async createOrUpdate(itemData, file) {
            addOrUpdateBibliographyItem(itemData, file).then(data => {
                // if id exists, it is an existing item
                if(itemData.id) {
                    this.updateBibliographyItem({
                        id: itemData.id,
                        type: itemData.type.name,
                        fields: {
                            ...itemData.fields,
                            citekey: data.citekey,
                            file: data.file,
                            file_url: data.file_url,
                        },
                    });
                } else {
                    this.add(data);
                }

                return data;
            });
        },
        async delete(itemId) {
            deleteBibliographyItem(itemId).then(_ => {
                this.deleteBibliographyItem(itemId);
            });
        },
        async import(file) {
            return importBibliographyFile(file).then(items => {
                items.forEach(itemWrap => {
                    if(itemWrap.added) {
                        this.addBibliographyItem(itemWrap.entry);
                    } else {
                        this.updateBibliographyItem(itemWrap.entry);
                    }
                });
                const itemCnt = items.length;
                const itemsAdded = items.filter(item => item.added).length;
                const itemsUpdated = itemCnt - itemsAdded;
                return {
                    total: itemCnt,
                    added: itemsAdded,
                    updated: itemsUpdated,
                };
            }).catch(error => {
                throw error;
            });
        },
    },
});

export default useBibliographyStore;