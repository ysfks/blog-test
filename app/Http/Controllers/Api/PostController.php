<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\PostResource;
use App\Models\Post;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class PostController extends Controller
{
    public function index(): JsonResponse
    {
        $posts = Post::query()->with('user')->orderByDesc('created_at')->paginate(10);

        return response()->json(['posts' => PostResource::collection($posts)]);
    }

    public function show(Post $post): JsonResponse
    {
        return response()->json(['post' => $post]);
    }

    public function store(Request $request): JsonResponse
    {
        $request->validate([
            'title' => 'required',
            'content' => 'required',
        ]);

        $slug = $request->input('slug') ?? Str::slug($request->input('title'), '-');

        $post = auth()->user()->posts()->create([
            'title' => $request->input('title'),
            'slug' => $slug,
            'content' => $request->input('content'),
        ]);

        return response()->json(['post' => $post], 201);
    }

    /**
     * @throws AuthorizationException
     */
    public function update(Request $request, Post $post): JsonResponse
    {
        $request->validate([
            'title' => 'required',
            'content' => 'required',
        ]);

        $this->authorize('update', $post);

        $slug = $request->input('slug') ?? Str::slug($request->input('title'), '-');

        $post->update([
            'title' => $request->input('title'),
            'slug' => $slug,
            'content' => $request->input('content'),
        ]);

        return response()->json(['post' => $post]);
    }

    /**
     * @throws AuthorizationException
     */
    public function destroy(Post $post): JsonResponse
    {
        $this->authorize('update', $post);

        $post->delete();
        return response()->json(['message' => 'Post deleted successfully']);
    }
}
