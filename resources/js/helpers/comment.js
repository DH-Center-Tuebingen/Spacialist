export function getEditedComment(entity, comment, replyTo) {
    let editedComment = null;
    if(replyTo) {
        const op = entity.comments.find(c => c.id == replyTo);
        if(op.replies) {
            editedComment = op.replies.find(reply => reply.id == comment.id);
        }
    } else {
        editedComment = entity.comments.find(c => c.id == comment.id);
    }
    return editedComment;
}