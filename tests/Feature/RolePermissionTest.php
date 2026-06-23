<?php

namespace Tests\Feature;

use App\Models\PartReservation;
use App\Models\PozicijaTip;
use App\Models\User;
use App\Policies\PartReservationPolicy;
use Database\Seeders\RolePermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;
use Tests\TestCase;

class RolePermissionTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Permisije koje RolePermissionSeeder mora kreirati.
     *
     * @var list<string>
     */
    private const ALL_PERMISSIONS = [
        'view-dashboard',
        'manage-users',
        'manage-terminals',
        'manage-services',
        'view-reports',
        'manage-licenses',
        'manage-distributors',
        'manage-bankomati',
        'parts.types.manage',
        'parts.stock.view.all',
        'parts.stock.view.own',
        'parts.movements.create',
        'parts.movements.view',
        'parts.transfer.execute',
        'parts.reservation.manage',
        'parts.reservation.view',
        'parts.inventory.perform',
        'parts.reports.view',
    ];

    /**
     * Očekivane dodele rola -> permisije (ogledalo RolePermissionSeeder-a).
     * Lock protiv slučajne izmjene seedera.
     *
     * @return array<string, list<string>>
     */
    private function expectedRolePermissions(): array
    {
        return [
            'Admin' => [
                'view-dashboard', 'manage-users', 'manage-terminals', 'manage-services',
                'view-reports', 'manage-licenses', 'manage-distributors', 'manage-bankomati',
                'parts.types.manage', 'parts.stock.view.all', 'parts.movements.create',
                'parts.movements.view', 'parts.transfer.execute', 'parts.reservation.manage',
                'parts.reservation.view', 'parts.inventory.perform', 'parts.reports.view',
            ],
            'Call centar' => [
                'view-dashboard', 'view-reports', 'manage-services',
            ],
            'Šef servisa' => [
                'view-dashboard', 'manage-services', 'view-reports', 'parts.stock.view.all',
                'parts.movements.create', 'parts.movements.view', 'parts.transfer.execute',
                'parts.reservation.manage', 'parts.reservation.view', 'parts.inventory.perform',
                'parts.reports.view',
            ],
            'Serviser' => [
                'view-dashboard', 'manage-services', 'parts.stock.view.own',
                'parts.movements.view', 'parts.reservation.manage', 'parts.reservation.view',
            ],
            'Menadžer licenci' => [
                'view-dashboard', 'manage-licenses', 'view-reports',
            ],
            'Distributer' => [
                'view-dashboard', 'manage-distributors',
            ],
            'Admin bankomata' => [
                'view-dashboard', 'manage-bankomati',
            ],
            'Šef servisa bankomata' => [
                'view-dashboard', 'manage-bankomati',
            ],
            'Serviser bankomata' => [
                'view-dashboard', 'manage-bankomati',
            ],
        ];
    }

    protected function setUp(): void
    {
        parent::setUp();

        // Kreiranje pozicija_tips okida PozicijaTipObserver koji kreira odgovarajuće Spatie role.
        foreach (array_keys($this->expectedRolePermissions()) as $roleName) {
            PozicijaTip::create(['naziv' => $roleName]);
        }

        // Seeder kreira permisije i kači ih na već postojeće role.
        $this->seed(RolePermissionSeeder::class);

        app(PermissionRegistrar::class)->forgetCachedPermissions();
    }

    public function test_seeder_creates_all_permissions(): void
    {
        foreach (self::ALL_PERMISSIONS as $permission) {
            $this->assertTrue(
                Permission::where('name', $permission)->exists(),
                "Permisija '{$permission}' nije kreirana."
            );
        }

        $this->assertSame(count(self::ALL_PERMISSIONS), Permission::count());
    }

    public function test_each_role_has_exactly_its_expected_permissions(): void
    {
        foreach ($this->expectedRolePermissions() as $roleName => $expected) {
            $role = Role::where('name', $roleName)->first();

            $this->assertNotNull($role, "Rola '{$roleName}' ne postoji.");

            $actual = $role->permissions->pluck('name')->sort()->values()->all();
            sort($expected);

            $this->assertSame(
                $expected,
                $actual,
                "Dodjela permisija za rolu '{$roleName}' se ne poklapa sa seederom."
            );
        }
    }

    public function test_user_inherits_permissions_through_pozicija_role_chain(): void
    {
        $serviser = $this->userWithPozicija('Serviser');

        $this->assertTrue($serviser->hasRole('Serviser'));
        $this->assertTrue($serviser->hasPermissionTo('parts.reservation.view'));
        $this->assertTrue($serviser->hasPermissionTo('parts.stock.view.own'));

        $this->assertFalse($serviser->hasPermissionTo('manage-users'));
        $this->assertFalse($serviser->hasPermissionTo('parts.stock.view.all'));
    }

    public function test_changing_pozicija_resyncs_user_role(): void
    {
        $user = $this->userWithPozicija('Serviser');
        $this->assertTrue($user->hasRole('Serviser'));

        $sefServisa = PozicijaTip::where('naziv', 'Šef servisa')->firstOrFail();
        $user->update(['pozicija_tipId' => $sefServisa->id]);

        app(PermissionRegistrar::class)->forgetCachedPermissions();

        $this->assertTrue($user->fresh()->hasRole('Šef servisa'));
        $this->assertFalse($user->fresh()->hasRole('Serviser'));
        $this->assertTrue($user->fresh()->hasPermissionTo('parts.stock.view.all'));
    }

    public function test_manage_role_can_cancel_any_reservation(): void
    {
        $sefServisa = $this->userWithPozicija('Šef servisa');
        $owner = $this->userWithPozicija('Serviser');

        $reservation = new PartReservation(['korisnik_id' => $owner->id]);

        $this->assertTrue($sefServisa->can('cancel', $reservation));
        $this->assertTrue($sefServisa->can('view', $reservation));
    }

    public function test_non_manage_role_cannot_touch_other_users_reservation(): void
    {
        $distributer = $this->userWithPozicija('Distributer');
        $owner = $this->userWithPozicija('Serviser');

        $othersReservation = new PartReservation(['korisnik_id' => $owner->id]);
        $ownReservation = new PartReservation(['korisnik_id' => $distributer->id]);

        $this->assertFalse($distributer->hasPermissionTo('parts.reservation.manage'));

        // Tuđa rezervacija -> zabranjeno.
        $this->assertFalse($distributer->can('cancel', $othersReservation));
        $this->assertFalse($distributer->can('view', $othersReservation));

        // Vlastita rezervacija -> dozvoljeno (vlasnik grana u policy-ju).
        $this->assertTrue($distributer->can('cancel', $ownReservation));
        $this->assertTrue($distributer->can('view', $ownReservation));
    }

    public function test_reservation_policy_logic_directly(): void
    {
        $policy = new PartReservationPolicy();

        $manager = $this->userWithPozicija('Admin');
        $serviser = $this->userWithPozicija('Serviser');
        $distributer = $this->userWithPozicija('Distributer');

        $serviserReservation = new PartReservation(['korisnik_id' => $serviser->id]);

        $this->assertTrue($policy->view($manager, $serviserReservation));
        $this->assertTrue($policy->fulfill($serviser, $serviserReservation));
        $this->assertFalse($policy->cancel($distributer, $serviserReservation));
    }

    private function userWithPozicija(string $roleName): User
    {
        $pozicija = PozicijaTip::where('naziv', $roleName)->firstOrFail();

        // UserObserver::created sinhronizuje Spatie rolu iz pozicija_tipId.
        return User::factory()->create(['pozicija_tipId' => $pozicija->id]);
    }
}
