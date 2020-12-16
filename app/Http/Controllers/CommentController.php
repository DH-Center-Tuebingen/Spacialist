<?php

namespace App\Http\Controllers;

use App\AttributeValue;
use App\Comment;
use App\Entity;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\Request;

class CommentController extends Controller {
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct() {
        //
    }

    // GET

    public function getComments(Request $request, $id) {
        $user = auth()->user();
        if(!$user->can('view_concepts')) {
            return response()->json([
                'error' => __('You do not have the permission to get comments')
            ], 403);
        }

        $resourceType = $request->input('r');
        $resource = null;
        switch($resourceType) {
            case 'attribute_value':
                try {
                    $aid = $request->input('aid');
                    $resource = AttributeValue::where('entity_id', $id)
                        ->where('attribute_id', $aid)
                        ->firstOrFail();
                } catch (ModelNotFoundException $e) {
                    return response()->json([
                        'error' => __('This attribute value does not exist')
                    ], 400);
                }
                break;
            case 'entity':
                try {
                    $resource = Entity::findOrFail($id);
                } catch (ModelNotFoundException $e) {
                    return response()->json([
                        'error' => __('This entity does not exist')
                    ], 400);
                }
                break;
            default:
                return response()->json([
                    'error' => __('This resource type does not exist')
                ], 400);
        }
        $comments = $resource->comments;
        $comments->loadCount('replies');
        
        return response()->json($comments);
    }

    public function getCommentReplies($id) {
        $user = auth()->user();
        if(!$user->can('view_concepts')) {
            return response()->json([
                'error' => __('You do not have the permission to get comments')
            ], 403);
        }

        try {
            $comment = Comment::findOrFail($id);
        } catch(ModelNotFoundException $e) {
            return response()->json([
                'error' => __('This comment does not exist')
            ], 400);
        }
        return response()->json($comment->replies);
    }

    // POST

    public function addComment(Request $request)
    {
        $user = auth()->user();
        if (!$user->can('create_concepts')) {
            return response()->json([
                'error' => __('You do not have the permission to add comments')
            ], 403);
        }
        $this->validate($request, Comment::postKeys);

        $type = $request->input('resource_type');
        $id = $request->input('resource_id');
        $resMeta = [];
        $object = null;
        switch($type) {
            case 'entity':
                try {
                    $object = Entity::findOrFail($id);
                } catch (ModelNotFoundException $e) {
                    return response()->json([
                        'error' => __('This entity does not exist')
                    ], 400);
                }
                break;
            case 'attribute_value':
                try {
                    $object = AttributeValue::findOrFail($id);
                    $resMeta = [
                        'entity_id' => $object->entity_id,
                        'attribute_id' => $object->attribute_id,
                    ];
                } catch (ModelNotFoundException $e) {
                    return response()->json([
                        'error' => __('This attribute value does not exist')
                    ], 400);
                }
                break;
        }

        if(!isset($object)) {
            return response()->json([
                'error' => __('Unsupported resource type')
            ], 400);
        }

        $comment = $object->addComment($request->only(array_keys(Comment::postKeys)), $user, true, $resMeta);
        return response()->json($comment, 201);
    }

    // PATCH

    public function patchComment(Request $request, $id)
    {
        $user = auth()->user();
        if (!$user->can('delete_move_concepts')) {
            return response()->json([
                'error' => __('You do not have the permission to edit a comment')
            ], 403);
        }

        $this->validate($request, Comment::patchKeys);

        try {
            $comment = Comment::where('id', $id)
                ->where('user_id', $user->id)
                ->firstOrFail();
        } catch (ModelNotFoundException $e) {
            return response()->json([
                'error' => __('This comment does not exist')
            ], 400);
        }

        $patchable = $request->only(array_keys(Comment::patchKeys));

        foreach ($patchable as $key => $val) {
            $comment->{$key} = $val;
        }
        $comment->save();
        return response()->json($comment);
    }

    // DELETE

    public function deleteComment($id) {
        $user = auth()->user();
        if(!$user->can('delete_move_concepts')) {
            return response()->json([
                'error' => __('You do not have the permission to delete a comment')
            ], 403);
        }
        try {
            $comment = Comment::withoutTrashed()->findOrFail($id);
        } catch(ModelNotFoundException $e) {
            return response()->json([
                'error' => __('This comment does not exist')
            ], 400);
        }

        $comment->delete();

        return response()->json(null, 204);
    }
}
