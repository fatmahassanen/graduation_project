<?php

namespace Tests\Unit;

use App\Http\Requests\NewsRequest;
use App\Models\Media;
use App\Models\News;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Validator;
use Tests\TestCase;

class NewsRequestTest extends TestCase
{
    use RefreshDatabase;

    public function test_validates_required_fields(): void
    {
        $request = new NewsRequest();
        $validator = Validator::make([], $request->rules());

        $this->assertFalse($validator->passes());
        $this->assertTrue($validator->errors()->has('title'));
        $this->assertTrue($validator->errors()->has('excerpt'));
        $this->assertTrue($validator->errors()->has('body'));
        $this->assertTrue($validator->errors()->has('category'));
        $this->assertTrue($validator->errors()->has('language'));
        $this->assertTrue($validator->errors()->has('status'));
    }

    public function test_validates_title_max_length(): void
    {
        $request = new NewsRequest();
        $data = [
            'title' => str_repeat('a', 256),
            'excerpt' => 'Test excerpt',
            'body' => 'Test body',
            'category' => 'announcement',
            'language' => 'en',
            'status' => 'draft',
        ];
        $validator = Validator::make($data, $request->rules());

        $this->assertFalse($validator->passes());
        $this->assertTrue($validator->errors()->has('title'));
    }

    public function test_validates_slug_uniqueness(): void
    {
        News::factory()->create(['slug' => 'test-news']);

        $request = new NewsRequest();
        $data = [
            'title' => 'Test News',
            'slug' => 'test-news',
            'excerpt' => 'Test excerpt',
            'body' => 'Test body',
            'category' => 'announcement',
            'language' => 'en',
            'status' => 'draft',
        ];
        $validator = Validator::make($data, $request->rules());

        $this->assertFalse($validator->passes());
        $this->assertTrue($validator->errors()->has('slug'));
    }

    public function test_validates_category_enum(): void
    {
        $request = new NewsRequest();
        $data = [
            'title' => 'Test News',
            'excerpt' => 'Test excerpt',
            'body' => 'Test body',
            'category' => 'invalid_category',
            'language' => 'en',
            'status' => 'draft',
        ];
        $validator = Validator::make($data, $request->rules());

        $this->assertFalse($validator->passes());
        $this->assertTrue($validator->errors()->has('category'));
    }

    public function test_accepts_valid_categories(): void
    {
        $validCategories = ['announcement', 'achievement', 'research', 'partnership'];

        foreach ($validCategories as $category) {
            $request = new NewsRequest();
            $data = [
                'title' => 'Test News',
                'excerpt' => 'Test excerpt',
                'body' => 'Test body',
                'category' => $category,
                'language' => 'en',
                'status' => 'draft',
            ];
            $validator = Validator::make($data, $request->rules());

            $this->assertTrue($validator->passes(), "Category {$category} should be valid");
        }
    }

    public function test_validates_status_enum(): void
    {
        $request = new NewsRequest();
        $data = [
            'title' => 'Test News',
            'excerpt' => 'Test excerpt',
            'body' => 'Test body',
            'category' => 'announcement',
            'language' => 'en',
            'status' => 'invalid_status',
        ];
        $validator = Validator::make($data, $request->rules());

        $this->assertFalse($validator->passes());
        $this->assertTrue($validator->errors()->has('status'));
    }

    public function test_validates_language_length(): void
    {
        $request = new NewsRequest();
        $data = [
            'title' => 'Test News',
            'excerpt' => 'Test excerpt',
            'body' => 'Test body',
            'category' => 'announcement',
            'language' => 'eng',
            'status' => 'draft',
        ];
        $validator = Validator::make($data, $request->rules());

        $this->assertFalse($validator->passes());
        $this->assertTrue($validator->errors()->has('language'));
    }

    public function test_validates_featured_image_id_exists(): void
    {
        $request = new NewsRequest();
        $data = [
            'title' => 'Test News',
            'excerpt' => 'Test excerpt',
            'body' => 'Test body',
            'category' => 'announcement',
            'featured_image_id' => 99999,
            'language' => 'en',
            'status' => 'draft',
        ];
        $validator = Validator::make($data, $request->rules());

        $this->assertFalse($validator->passes());
        $this->assertTrue($validator->errors()->has('featured_image_id'));
    }

    public function test_accepts_valid_featured_image_id(): void
    {
        $media = Media::factory()->create();
        
        $request = new NewsRequest();
        $data = [
            'title' => 'Test News',
            'excerpt' => 'Test excerpt',
            'body' => 'Test body',
            'category' => 'announcement',
            'featured_image_id' => $media->id,
            'language' => 'en',
            'status' => 'draft',
        ];
        $validator = Validator::make($data, $request->rules());

        $this->assertTrue($validator->passes());
    }

    public function test_validates_is_featured_is_boolean(): void
    {
        $request = new NewsRequest();
        $data = [
            'title' => 'Test News',
            'excerpt' => 'Test excerpt',
            'body' => 'Test body',
            'category' => 'announcement',
            'is_featured' => 'not_boolean',
            'language' => 'en',
            'status' => 'draft',
        ];
        $validator = Validator::make($data, $request->rules());

        $this->assertFalse($validator->passes());
        $this->assertTrue($validator->errors()->has('is_featured'));
    }

    public function test_passes_with_valid_data(): void
    {
        $request = new NewsRequest();
        $data = [
            'title' => 'Test News',
            'slug' => 'test-news-unique',
            'excerpt' => 'Test excerpt',
            'body' => 'Test body content',
            'category' => 'announcement',
            'is_featured' => true,
            'language' => 'en',
            'status' => 'published',
        ];
        $validator = Validator::make($data, $request->rules());

        $this->assertTrue($validator->passes());
    }
}
