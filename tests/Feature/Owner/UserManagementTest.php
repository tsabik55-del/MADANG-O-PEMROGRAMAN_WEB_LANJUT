<?php

namespace Tests\Feature\Owner;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class UserManagementTest extends TestCase
{
    use RefreshDatabase;

    private function owner(): User
    {
        return User::factory()->owner()->create();
    }

    public function test_owner_bisa_melihat_dan_mencari_pengguna(): void
    {
        $owner = $this->owner();
        $budi = User::factory()->pelanggan()->create(['name' => 'Budi Khusus']);
        User::factory()->karyawan()->create(['name' => 'Sari Karyawan']);

        $this->actingAs($owner)
            ->get(route('owner.users.index', ['search' => 'Budi Khusus']))
            ->assertOk()
            ->assertSee('Budi Khusus')
            ->assertDontSee('Sari Karyawan');
    }

    public function test_owner_bisa_menambah_pengguna_dengan_peran(): void
    {
        $this->actingAs($this->owner())->post(route('owner.users.store'), [
            'name' => 'Kasir Baru',
            'username' => 'kasir_baru',
            'email' => 'kasir@example.com',
            'wa_number' => '081211112222',
            'password' => 'password123',
            'password_confirmation' => 'password123',
            'role' => 'karyawan',
        ])->assertRedirect(route('owner.users.index'));

        $this->assertDatabaseHas('users', [
            'username' => 'kasir_baru',
            'role' => 'karyawan',
        ]);
    }

    public function test_owner_tidak_bisa_menghapus_akun_sendiri(): void
    {
        $owner = $this->owner();

        $this->actingAs($owner)
            ->delete(route('owner.users.destroy', $owner))
            ->assertSessionHasErrors('user');

        $this->assertDatabaseHas('users', ['id' => $owner->id]);
    }

    public function test_owner_tidak_bisa_menurunkan_peran_sendiri(): void
    {
        $owner = $this->owner();

        $this->actingAs($owner)
            ->put(route('owner.users.update', $owner), [
                'name' => $owner->name,
                'username' => $owner->username,
                'email' => $owner->email,
                'wa_number' => $owner->wa_number,
                'role' => 'pelanggan',
            ])
            ->assertSessionHasErrors('role');

        $this->assertSame('owner', $owner->fresh()->role);
    }

    public function test_owner_bisa_mengubah_data_dan_reset_password(): void
    {
        $owner = $this->owner();
        $user = User::factory()->pelanggan()->create();

        $this->actingAs($owner)->put(route('owner.users.update', $user), [
            'name' => 'Nama Baru',
            'username' => $user->username,
            'email' => $user->email,
            'wa_number' => $user->wa_number,
            'password' => 'passwordbaru',
            'password_confirmation' => 'passwordbaru',
            'role' => 'pelanggan',
        ])->assertRedirect(route('owner.users.index'));

        $this->assertSame('Nama Baru', $user->fresh()->name);
        $this->assertTrue(Hash::check('passwordbaru', $user->fresh()->password));
    }

    public function test_peran_lain_tidak_bisa_membuka_halaman_owner(): void
    {
        $this->actingAs(User::factory()->pelanggan()->create())
            ->get(route('owner.users.index'))
            ->assertForbidden();

        $this->actingAs(User::factory()->karyawan()->create())
            ->get(route('owner.reports.index'))
            ->assertForbidden();

        $this->actingAs(User::factory()->pelanggan()->create())
            ->get(route('karyawan.dashboard'))
            ->assertForbidden();
    }
}
