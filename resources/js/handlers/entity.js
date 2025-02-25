import useAttributeStore from '@/bootstrap/stores/attribute.js';
import useBibliographyStore from '@/bootstrap/stores/bibliography.js';
import useEntityStore from '@/bootstrap/stores/entity.js';
import useReferenceStore from '../bootstrap/stores/reference';
import useUserStore from '@/bootstrap/stores/user.js';

import { addToast } from '@/plugins/toast.js';

const sendInfoToastMessage = message => {
    addToast(message, '', {
        duration: 2500,
        autohide: true,
        channel: 'info',
        icon: true,
        simple: true,
    });
};

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
        const attributeValue = e.attributeValue;
        const entity_id = attributeValue.entity_id;
        useEntityStore().externalAttributeValueUpdated(entity_id, attributeValue, e.value);
    },
};

export const handleAttributeValueUpdated = {
    'AttributeValueUpdated': e => {
        // Only handle event if from different user
        if(e.user.id == useUserStore().getCurrentUserId) return;
        console.log('handleAttributeValueUpdated', e);
        const attributeValue = e.attributeValue;
        const entityId = attributeValue.entity_id;
        useEntityStore().externalAttributeValueUpdated(entityId, attributeValue, e.value);
    },
};

export const handleAttributeValueDeleted = {
    'AttributeValueDeleted': e => {
        const attributeId = e.attributeValue.attribute_id;
        const entityId = e.attributeValue.entity_id;
        // Only handle event if from different user
        if(e.user.id == useUserStore().getCurrentUserId) return;
        useEntityStore().externalAttributeValueDeleted(entityId, attributeId);
    },
};

const getReferenceValues = e => {
    const reference = e.reference;
    const entityId = reference.entity_id;
    const attribute = useAttributeStore().getAttribute(reference.attribute_id);
    const attributeUrl = attribute.thesaurus_url;
    reference.bibliography = useBibliographyStore().getEntry(reference.bibliography_id);
    return { entityId, attributeUrl, reference };
};

export const handleEntityReferenceAdded = {
    'ReferenceAdded': e => {
        // TODO: We should only send to different users in the backend.
        if(useUserStore().isSameUser(e.user.id)) return;
        const {
            entityId,
            attributeUrl,
            reference,
        } = getReferenceValues(e);
        useReferenceStore().handleAdd(entityId, attributeUrl, reference);
        const message = `A reference has been added by '${e.user.nickname}'!`;
        sendInfoToastMessage(message);
    },
};

export const handleEntityReferenceUpdated = {
    'ReferenceUpdated': e => {
        // Only handle event if from different user
        if(e.user.id == useUserStore().getCurrentUserId) return;
        const {
            entityId,
            attributeUrl,
            reference,
        } = getReferenceValues(e);
        useReferenceStore().handleUpdate(entityId, attributeUrl, reference);

        const message = `A reference has been updated by '${e.user.nickname}'!`;
        sendInfoToastMessage(message);
    },
};

export const handleEntityReferenceDeleted = {
    'ReferenceDeleted': e => {
        // Only handle event if from different user
        if(e.user.id == useUserStore().getCurrentUserId) return;
        const {
            entityId,
            attributeUrl,
            reference,
        } = getReferenceValues(e);
        useReferenceStore().handleDelete(entityId, attributeUrl, reference.id);

        const message = `A reference has been deleted by '${e.user.nickname}'!`;
        sendInfoToastMessage(message);
    },
};

export const handleEntityCommentAdded = {
    'CommentAdded': e => {
        if(e.user.id == useUserStore().getCurrentUserId) return;

        const entityStore = useEntityStore();
        entityStore.addComment(e.comment.commentable_id, e.comment, {
            replyTo: e.replyTo,
        });

        const entity = entityStore.getEntity(e.comment.commentable_id);
        const message = `'${e.user.nickname}' commented on entity '${entity.name}'!`;
        sendInfoToastMessage(message);
    },
};

export const handleEntityCommentUpdated = {
    'CommentUpdated': e => {
        if(e.user.id == useUserStore().getCurrentUserId) return;

        const entityStore = useEntityStore();
        entityStore.updateComment(e.comment.commentable_id, e.comment, {
            replyTo: e.replyTo,
        });

        const entity = entityStore.getEntity(e.comment.commentable_id);
        const message = `'${e.user.nickname}' updated their comment on entity '${entity.name}'!`;
        sendInfoToastMessage(message);
    },
};

export const handleEntityCommentDeleted = {
    'CommentDeleted': e => {
        if(e.user.id == useUserStore().getCurrentUserId) return;

        const entityStore = useEntityStore();
        entityStore.deleteComment(e.comment.commentable_id, e.comment, {
            replyTo: e.replyTo,
        });

        const entity = entityStore.getEntity(e.comment.commentable_id);
        const message = `'${e.user.nickname}' deleted their comment on entity '${entity.name}'!`;
        sendInfoToastMessage(message);
    },
};