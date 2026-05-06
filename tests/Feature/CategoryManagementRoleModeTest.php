<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CategoryManagementRoleModeTest extends TestCase
{
    use RefreshDatabase;

    public function test_owner_can_access_and_crud_categories(): void
    {
        $owner = User::factory()->create([
            'role' => 'owner',
            'mode_app' => 'sederhana',
        ]);

        $this->actingAs($owner)->get('/categories')->assertOk();
        $this->actingAs($owner)->get('/categories/create')->assertOk();

        $this->actingAs($owner)
            ->post('/categories', [
                'nama_kategori' => 'Sembako',
                'deskripsi' => 'Kategori sembako',
            ])
            ->assertRedirect(route('categories.index'));

        $this->assertDatabaseHas('categories', [
            'nama_kategori' => 'Sembako',
            'slug' => 'sembako',
        ]);

        $category = Category::where('nama_kategori', 'Sembako')->first();

        $this->actingAs($owner)->get(route('categories.edit', $category))->assertOk();

        $this->actingAs($owner)
            ->put(route('categories.update', $category), [
                'nama_kategori' => 'Sembako Premium',
            ])
            ->assertRedirect(route('categories.index'));

        $this->assertDatabaseHas('categories', [
            'id' => $category->id,
            'nama_kategori' => 'Sembako Premium',
        ]);

        $this->actingAs($owner)
            ->delete(route('categories.destroy', $category))
            ->assertRedirect(route('categories.index'));

        $this->assertDatabaseMissing('categories', [
            'id' => $category->id,
        ]);
    }

    public function test_gudang_lengkap_can_crud_categories(): void
    {
        $gudang = User::factory()->create([
            'role' => 'gudang',
            'mode_app' => 'lengkap',
        ]);

        $category = Category::create([
            'nama_kategori' => 'Minuman',
            'slug' => 'minuman',
        ]);

        $this->actingAs($gudang)->get('/categories')->assertOk();
        $this->actingAs($gudang)->get('/categories/create')->assertOk();
        $this->actingAs($gudang)->get(route('categories.edit', $category))->assertOk();

        $this->actingAs($gudang)
            ->put(route('categories.update', $category), [
                'nama_kategori' => 'Minuman Ringan',
            ])
            ->assertRedirect(route('categories.index'));

        $this->assertDatabaseHas('categories', [
            'id' => $category->id,
            'nama_kategori' => 'Minuman Ringan',
        ]);

        $this->actingAs($gudang)
            ->delete(route('categories.destroy', $category))
            ->assertRedirect(route('categories.index'));

        $this->assertDatabaseMissing('categories', [
            'id' => $category->id,
        ]);
    }

    public function test_gudang_sederhana_cannot_access_categories(): void
    {
        $gudang = User::factory()->create([
            'role' => 'gudang',
            'mode_app' => 'sederhana',
        ]);

        $category = Category::create([
            'nama_kategori' => 'Snack',
            'slug' => 'snack',
        ]);

        $this->actingAs($gudang)->get('/categories')->assertForbidden();
        $this->actingAs($gudang)->get('/categories/create')->assertForbidden();
        $this->actingAs($gudang)->post('/categories', ['nama_kategori' => 'Test'])->assertForbidden();
        $this->actingAs($gudang)->get(route('categories.edit', $category))->assertForbidden();
    }

    public function test_kasir_cannot_access_categories(): void
    {
        $kasir = User::factory()->create([
            'role' => 'kasir',
            'mode_app' => 'lengkap',
        ]);

        $category = Category::create([
            'nama_kategori' => 'Rokok',
            'slug' => 'rokok',
        ]);

        $this->actingAs($kasir)->get('/categories')->assertForbidden();
        $this->actingAs($kasir)->get('/categories/create')->assertForbidden();
        $this->actingAs($kasir)->post('/categories', ['nama_kategori' => 'Test'])->assertForbidden();
        $this->actingAs($kasir)->get(route('categories.edit', $category))->assertForbidden();
        $this->actingAs($kasir)->put(route('categories.update', $category), ['nama_kategori' => 'Test2'])->assertForbidden();
        $this->actingAs($kasir)->delete(route('categories.destroy', $category))->assertForbidden();
    }

    public function test_category_validation_requires_unique_name(): void
    {
        $owner = User::factory()->create([
            'role' => 'owner',
            'mode_app' => 'lengkap',
        ]);

        Category::create([
            'nama_kategori' => 'Sayuran',
            'slug' => 'sayuran',
        ]);

        $this->actingAs($owner)
            ->from('/categories/create')
            ->post('/categories', [
                'nama_kategori' => '', // Empty
            ])
            ->assertRedirect('/categories/create')
            ->assertSessionHasErrors(['nama_kategori']);

        $this->actingAs($owner)
            ->from('/categories/create')
            ->post('/categories', [
                'nama_kategori' => 'Sayuran', // Duplicate
            ])
            ->assertRedirect('/categories/create')
            ->assertSessionHasErrors(['nama_kategori']);
    }
}
