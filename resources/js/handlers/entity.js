import useAttributeStore from '@/bootstrap/stores/attribute.js';
import useBibliographyStore from '@/bootstrap/stores/bibliography.js';
import useEntityStore from '@/bootstrap/stores/entity.js';
import useUserStore from '@/bootstrap/stores/user.js';

import { addToast } from '@/plugins/toast.js';

export const handleEntityDataUpdated = {
    'EntityDataUpdated': e => {
        console.log('handleEntityDataUpdated', e);
    },
};

export const handleAttributeValueCreated = {
    'AttributeValueCreated': e => {
        // Only handle event if from different user
        if(e.user.id == useUserStore().getCurrentUserId) return;
        console.log('handleAttributeValueCreated', e);
    },
};

export const handleAttributeValueUpdated = {
    'AttributeValueUpdated': e => {
        // Only handle event if from different user
        if(e.user.id == useUserStore().getCurrentUserId) return;
        console.log('handleAttributeValueUpdated', e);
        useEntityStore().updateEntityData(
            e.attributeValue.entity_id,
            {
                [e.attributeValue.attribute_id]: e.value,
            },
            {
                [e.attributeValue.attribute_id]: e.attributeValue,
            },
            {},
            true,
            true,
        );
    },
};

export const handleAttributeValueDeleted = {
    'AttributeValueDeleted': e => {
        // Only handle event if from different user
        if(e.user.id == useUserStore().getCurrentUserId) return;
        console.log('handleAttributeValueDeleted', e);
    },
};

export const handleEntityReferenceAdded = {
    'ReferenceAdded': e => {
        // Only handle event if from different user
        if(e.user.id == useUserStore().getCurrentUserId) return;
        const reference = e.reference;
        const entityId = reference.entity_id;
        const attribute = useAttributeStore().getAttribute(reference.attribute_id);
        const attributeUrl = attribute.thesaurus_url;
        reference.bibliography = useBibliographyStore().getEntry(reference.bibliography_id);
        useEntityStore().handleReference(entityId, attributeUrl, 'add', reference);

        const message = `A reference has been added by '${e.user.nickname}'!`;
        addToast(message, '', {
            duration: 2500,
            autohide: true,
            channel: 'info',
            icon: true,
            simple: true,
        });
    },
};

export const handleEntityReferenceUpdated = {
    'ReferenceUpdated': e => {
        // Only handle event if from different user
        if(e.user.id == useUserStore().getCurrentUserId) return;
        const reference = e.reference;
        const entityId = reference.entity_id;
        const attribute = useAttributeStore().getAttribute(reference.attribute_id);
        const attributeUrl = attribute.thesaurus_url;
        useEntityStore().handleReference(entityId, attributeUrl, 'update', {
            id: reference.id,
            data: reference,
            updates: reference,
        });

        const message = `A reference has been updated by '${e.user.nickname}'!`;
        addToast(message, '', {
            duration: 2500,
            autohide: true,
            channel: 'info',
            icon: true,
            simple: true,
        });
    },
};

export const handleEntityReferenceDeleted = {
    'ReferenceDeleted': e => {
        // Only handle event if from different user
        if(e.user.id == useUserStore().getCurrentUserId) return;
        const reference = e.reference;
        const entityId = reference.entity_id;
        const attribute = useAttributeStore().getAttribute(reference.attribute_id);
        const attributeUrl = attribute.thesaurus_url;
        useEntityStore().handleReference(entityId, attributeUrl, 'delete', {
            id: reference.id,
        });

        const message = `A reference has been deleted by '${e.user.nickname}'!`;
        addToast(message, '', {
            duration: 2500,
            autohide: true,
            channel: 'info',
            icon: true,
            simple: true,
        });
    },
};

export const handleEntityCommentAdded = {
    'CommentAdded': e => {
        const entityStore = useEntityStore();
        entityStore.handleComment(e.comment.commentable_id, e.comment, 'add', {
            replyTo: e.replyTo,
        });

        const entity = entityStore.getEntity(e.comment.commentable_id);
        const message = `'${e.user.nickname}' commented on entity '${entity.name}'!`;
        addToast(message, '', {
            duration: 2500,
            autohide: true,
            channel: 'info',
            icon: true,
            simple: true,
        });
    },
};

export const handleEntityCommentUpdated = {
    'CommentUpdated': e => {
        const entityStore = useEntityStore();
        entityStore.handleComment(e.comment.commentable_id, e.comment, 'update', {
            replyTo: e.replyTo,
        });

        const entity = entityStore.getEntity(e.comment.commentable_id);
        const message = `'${e.user.nickname}' updated their comment on entity '${entity.name}'!`;
        addToast(message, '', {
            duration: 2500,
            autohide: true,
            channel: 'info',
            icon: true,
            simple: true,
        });
    },
};

export const handleEntityCommentDeleted = {
    'CommentDeleted': e => {
        const entityStore = useEntityStore();
        entityStore.handleComment(e.comment.commentable_id, e.comment, 'delete', {
            replyTo: e.replyTo,
        });

        const entity = entityStore.getEntity(e.comment.commentable_id);
        const message = `'${e.user.nickname}' deleted their comment on entity '${entity.name}'!`;
        addToast(message, '', {
            duration: 2500,
            autohide: true,
            channel: 'info',
            icon: true,
            simple: true,
        });
    },
};