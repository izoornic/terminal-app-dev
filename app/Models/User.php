<?php

namespace App\Models;

use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Fortify\TwoFactorAuthenticatable;
use Laravel\Jetstream\HasProfilePhoto;
use Laravel\Sanctum\HasApiTokens;

use Spatie\Permission\Traits\HasRoles;
use App\Models\PozicijaTip;

use App\Models\Blokacija;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\DB;

class User extends Authenticatable
{
    use HasApiTokens;
    use HasFactory;
    use HasProfilePhoto;
    use Notifiable;
    use TwoFactorAuthenticatable;

     use HasRoles;

    /**
     * The attributes that are mass assignable.
     *
     * @var string[]
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'pozicija_tipId',
        'vidi_komentare_na_terminalu',
        'lokacijaId',
        'telegramId',
        'tel',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array
     */
    protected $hidden = [
        'password',
        'remember_token',
        'two_factor_recovery_codes',
        'two_factor_secret',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
    ];

    /**
     * The accessors to append to the model's array form.
     *
     * @var array
     */
    protected $appends = [
        'profile_photo_url',
    ];

    // Add relationship to pozicija_tips
    public function pozicijaTip():BelongsTo
    {
        return $this->belongsTo(PozicijaTip::class, 'pozicija_tipId');
    }

    // Helper method to get role name from pozicija_tipId
    public function getPozicijaRoleName()
    {
        return $this->pozicijaTip?->naziv;
    }

    /**
     * Lista stranica i ruta za kreiranje menija
     * u zavisnosti od uloge koju korisnik ima
     *
     * @param  mixed $curentUserId
     * @return array
     */
    public static function userPozicijeList($curentUserId)
    {
        $pozicije = DB::select(
            'SELECT
                s.naziv, s.route_name
            FROM
                stranicas s,
                pozicija_prikaz_stranicas ps,
                pozicija_tips pt,
                users u
            WHERE
                s.id = ps.stranicaId AND ps.pozicija_tipId = pt.id AND pt.id = u.pozicija_tipId AND s.show_in_meni = 1 AND s.sub_menu_order IS NULL AND u.id = ?
            ORDER BY
                s.menu_order' , 
            [$curentUserId]
        );

        $retval = [];
        foreach ($pozicije as $pozicija) {
         $retval[$pozicija->route_name] = $pozicija->naziv;
        }
        return  $retval;
    }

    public function userBankmatPositionAndRegion(){
        $roles = [
            9 => 'admin',
            10 => 'sef',
            11 => 'serviser'
        ];
        $retval = [];
        $retval['role'] = $roles[$this->pozicija_tipId];
        $retval['region'] = Blokacija::select('bankomat_region_id')->where('id', auth()->user()->lokacijaId)->first()->bankomat_region_id;
        return $retval;
    }
}
