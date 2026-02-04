<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

use Illuminate\Support\Facades\DB;
use Spatie\Permission\Models\Role;
use App\Models\User;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        // Get all pozicija_tips
        $pozicijaTips = DB::table('pozicija_tips')->get();

        // Create Spatie roles from pozicija_tips
        foreach ($pozicijaTips as $pozicija) {
            Role::firstOrCreate([
                'name' => $pozicija->naziv,
                'guard_name' => 'web'
            ]);
        }

        // Assign roles to existing users based on their pozicija_tipId
        User::whereNotNull('pozicija_tipId')->chunk(100, function ($users) use ($pozicijaTips) {
            foreach ($users as $user) {
                $pozicija = $pozicijaTips->firstWhere('id', $user->pozicija_tipId);
                if ($pozicija) {
                    $user->syncRoles([$pozicija->naziv]);
                }
            }
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
       DB::table('model_has_roles')->truncate();
        
        $pozicijaTips = DB::table('pozicija_tips')->pluck('naziv');
        Role::whereIn('name', $pozicijaTips)->delete();
    }
};
