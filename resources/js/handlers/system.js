import useBibliographyStore from '@/bootstrap/stores/bibliography.js';
import useEntityStore from '@/bootstrap/stores/entity.js';
import useUserStore from '@/bootstrap/stores/user.js';

import { addToast } from '@/plugins/toast.js';

export const handleBibliographyCreated = {
    'BibliographyCreated': e => {
        // Only handle event if from different user
        if(e.user.id == useUserStore().getCurrentUserId) return;
        const message = `Bibliography item '${e.bibliography.citekey}' has been added by '${e.user.nickname}'!`;
        useBibliographyStore().addBibliographyItem(e.bibliography);

        addToast(message, '', {
            duration: 2500,
            autohide: true,
            channel: 'info',
            icon: true,
            simple: true,
        });
    },
};

export const handleBibliographyUpdated = {
    'BibliographyUpdated': e => {
        // Only handle event if from different user
        if(e.user.id == useUserStore().getCurrentUserId) return;
        const message = `Bibliography item '${e.bibliography.citekey}' has been updated by '${e.user.nickname}'!`;
        useBibliographyStore().updateBibliographyItem(e.bibliography);

        addToast(message, '', {
            duration: 2500,
            autohide: true,
            channel: 'info',
            icon: true,
            simple: true,
        });
    },
};

export const handleBibliographyDeleted = {
    'BibliographyDeleted': e => {
        // Only handle event if from different user
        if(e.user.id == useUserStore().getCurrentUserId) return;
        const message = `Bibliography item '${e.bibliography.citekey}' has been deleted by '${e.user.nickname}'!`;
        useBibliographyStore().deleteBibliographyItem(e.bibliography.id);

        addToast(message, '', {
            duration: 2500,
            autohide: true,
            channel: 'info',
            icon: true,
            simple: true,
        });
    },
};

export const handleEntityCreated = {
    'EntityCreated': e => {
        // Only handle event if from different user
        if(e.user.id == useUserStore().getCurrentUserId) return;
        const message = `Entity '${e.entity.name}' has been added by '${e.user.nickname}'!`;
        useEntityStore().add(e.entity, true);

        addToast(message, '', {
            duration: 2500,
            autohide: true,
            channel: 'info',
            icon: true,
            simple: true,
        });
    },
};

export const handleEntityUpdated = {
    'EntityUpdated': e => {
        // Only handle event if from different user
        if(e.user.id == useUserStore().getCurrentUserId) return;
        const message = `Entity '${e.entity.name}' has been updated by '${e.user.nickname}'!`;
        useEntityStore().add(e.entity, true);

        addToast(message, '', {
            duration: 2500,
            autohide: true,
            channel: 'info',
            icon: true,
            simple: true,
        });
    },
};

export const handleEntityDeleted = {
    'EntityDeleted': e => {
        // Only handle event if from different user
        if(e.user.id == useUserStore().getCurrentUserId) return;
        const message = `Entity '${e.entity.name}' has been deleted by '${e.user.nickname}'!`;
        useEntityStore().soft_delete(e.entity);

        toast.$toast(message, '', {
            duration: 2500,
            autohide: true,
            channel: 'info',
            icon: true,
            simple: true,
        });
    },
};