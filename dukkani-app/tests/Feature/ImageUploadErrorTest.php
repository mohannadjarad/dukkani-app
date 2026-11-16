<?php

namespace Tests\Feature;

use App\Exceptions\ImageServiceNotConfiguredException;
use App\Models\Store;
use App\Models\User;
use App\Services\ImageService;
use Database\Seeders\DemoSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Tests\TestCase;

class ImageUploadErrorTest extends TestCase
{
    use RefreshDatabase;

    protected Store $store;

    protected User $owner;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(DemoSeeder::class);
        $this->store = Store::where('slug', 'amal-bakes')->firstOrFail();
        $this->owner = $this->store->owner;
    }

    public function test_a_product_image_upload_reports_a_missing_image_service(): void
    {
        $this->mock(ImageService::class, function ($mock) {
            $mock->shouldReceive('upload')
                ->once()
                ->andThrow(new ImageServiceNotConfiguredException('CLOUDINARY_URL is not configured.'));
        });

        $this->actingAs($this->owner)
            ->from(route('products.create'))
            ->post(route('products.store'), [
                'name' => 'كيكة الاختبار',
                'price' => '5.000',
                'is_active' => '1',
                'image_file' => UploadedFile::fake()->create('cake.jpg', 10, 'image/jpeg'),
            ])
            ->assertRedirect(route('products.create'))
            ->assertSessionHasErrors(['image_file' => 'خدمة الصور غير مهيأة']);

        $this->assertDatabaseMissing('products', ['name' => 'كيكة الاختبار']);
    }

    public function test_a_store_logo_upload_reports_a_missing_image_service(): void
    {
        $this->mock(ImageService::class, function ($mock) {
            $mock->shouldReceive('upload')
                ->once()
                ->andThrow(new ImageServiceNotConfiguredException('CLOUDINARY_URL is not configured.'));
        });

        $this->actingAs($this->owner)
            ->from(route('settings.index', ['tab' => 'profile']))
            ->patch(route('settings.profile'), [
                'name' => $this->store->name,
                'slug' => $this->store->slug,
                'phone' => $this->store->phone,
                'city' => $this->store->city,
                'logo_file' => UploadedFile::fake()->create('logo.jpg', 10, 'image/jpeg'),
            ])
            ->assertRedirect(route('settings.index', ['tab' => 'profile']))
            ->assertSessionHasErrors(['logo_file' => 'خدمة الصور غير مهيأة']);
    }
}
