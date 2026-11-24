<?php

namespace Tests\Feature\Owner;

use App\Models\Category;
use App\Models\Menu;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class MenuManagementTest extends TestCase
{
    use RefreshDatabase;

    private function owner(): User
    {
        return User::factory()->owner()->create();
    }

    public function test_owner_bisa_menambah_menu_dengan_upload_foto(): void
    {
        Storage::fake('public');
        $category = Category::factory()->create();

        $this->actingAs($this->owner())->post(route('owner.menus.store'), [
            'category_id' => $category->id,
            'name' => 'Menu Upload',
            'description' => 'Deskripsi',
            'price' => 20000,
            'status_ketersediaan' => '1',
            'image_file' => UploadedFile::fake()->image('foto.jpg', 200, 200),
        ])->assertRedirect(route('owner.menus.index'));

        $menu = Menu::where('name', 'Menu Upload')->firstOrFail();

        $this->assertNotNull($menu->image);
        $this->assertStringStartsWith('images/menus/', $menu->image);
        Storage::disk('public')->assertExists($menu->image);
    }

    public function test_file_selain_gambar_ditolak(): void
    {
        Storage::fake('public');
        $category = Category::factory()->create();

        $this->actingAs($this->owner())->post(route('owner.menus.store'), [
            'category_id' => $category->id,
            'name' => 'Menu Salah',
            'price' => 20000,
            'image_file' => UploadedFile::fake()->create('dokumen.pdf', 10, 'application/pdf'),
        ])->assertSessionHasErrors('image_file');

        $this->assertDatabaseMissing('menus', ['name' => 'Menu Salah']);
    }

    public function test_upload_baru_mengganti_dan_menghapus_foto_lama(): void
    {
        Storage::fake('public');
        $owner = $this->owner();
        $category = Category::factory()->create();

        $this->actingAs($owner)->post(route('owner.menus.store'), [
            'category_id' => $category->id,
            'name' => 'Menu Ganti Foto',
            'price' => 15000,
            'image_file' => UploadedFile::fake()->image('lama.jpg'),
        ]);

        $menu = Menu::where('name', 'Menu Ganti Foto')->firstOrFail();
        $oldPath = $menu->image;
        Storage::disk('public')->assertExists($oldPath);

        $this->actingAs($owner)->put(route('owner.menus.update', $menu), [
            'category_id' => $category->id,
            'name' => 'Menu Ganti Foto',
            'price' => 15000,
            'image' => $oldPath,
            'image_file' => UploadedFile::fake()->image('baru.jpg'),
        ])->assertRedirect(route('owner.menus.index'));

        $newPath = $menu->fresh()->image;
        $this->assertNotSame($oldPath, $newPath);
        Storage::disk('public')->assertExists($newPath);
        Storage::disk('public')->assertMissing($oldPath);
    }

    public function test_pelanggan_tidak_bisa_mengakses_crud_menu(): void
    {
        $this->actingAs(User::factory()->pelanggan()->create())
            ->get(route('owner.menus.index'))
            ->assertForbidden();
    }
}
