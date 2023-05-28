<?php

namespace Tests\Feature;

use App\Http\Resources\PostResource;
use App\Models\Post;
use App\Models\User;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Passport\Passport;
use Tests\TestCase;

class PostControllerTest extends TestCase
{
    use RefreshDatabase;

    public function test_can_list_posts(): void
    {
        $user = User::factory()->create();
        $posts = Post::factory()->for($user)->count(3)->create();
        $posts->load('user');

        Passport::actingAs($user);

        $response = $this->get('/api/posts');

        $json = $response->json();
        $postsResource = PostResource::collection($posts)->response()->getData(true);

        $response->assertStatus(200);

        $this->assertEquals($json['posts'], $postsResource['data']);
    }

    public function test_can_create_post(): void
    {
        Passport::actingAs(User::factory()->create());

        $data = [
            'title' => 'Test Post',
            'content' => 'This is a test post.',
        ];

        $response = $this->post('/api/posts', $data);

        $response->assertStatus(201)
            ->assertJson([
                'post' => $data,
            ]);

        $this->assertDatabaseHas('posts', $data);
    }

    public function test_can_update_own_post(): void
    {
        $user = User::factory()->create();
        $post = Post::factory()->for($user)->create();

        Passport::actingAs($user, ['update-post']);

        $data = [
            'title' => 'Updated Post Title',
            'content' => 'Updated post content.',
        ];

        $response = $this->put('/api/posts/'.$post->id, $data);

        $response->assertStatus(200)
            ->assertJson([
                'post' => $data,
            ]);

        $this->assertDatabaseHas('posts', $data);
    }

    public function test_cannot_update_other_users_post(): void
    {
        $this->expectException(AuthorizationException::class);

        $user1 = User::factory()->create();
        $user2 = User::factory()->create();

        $post = Post::factory()->for($user1)->create();

        Passport::actingAs($user2, ['update-post']);

        $data = [
            'title' => 'Updated Post Title',
            'content' => 'Updated post content.',
        ];

        $response = $this->put('/api/posts/'.$post->id, $data);

        $response->assertStatus(403)
            ->assertJson([
                'error' => 'Unauthorized',
            ]);

        $this->assertDatabaseMissing('posts', $data);
    }

    public function test_can_delete_own_post(): void
    {
        $user = User::factory()->create();
        $post = Post::factory()->for($user)->create();

        Passport::actingAs($user, ['delete-post']);

        $response = $this->delete('/api/posts/'.$post->id);

        $response->assertStatus(200)
            ->assertExactJson([
                'message' => 'Post deleted successfully'
            ]);

        $this->assertDatabaseMissing('posts', ['id' => $post->id]);
    }

    public function test_cannot_delete_other_users_post(): void
    {
        $this->expectException(AuthorizationException::class);

        $user1 = User::factory()->create();
        $user2 = User::factory()->create();

        $post = Post::factory()->for($user1)->create();

        Passport::actingAs($user2, ['delete-post']);

        $response = $this->delete('/api/posts/'.$post->id);

        $response->assertStatus(403)
            ->assertJson([
                'error' => 'Unauthorized',
            ]);

        $this->assertDatabaseHas('posts', $post->id);
    }
}
