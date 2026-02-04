<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class PozicijaTip extends Model
{
    use HasFactory;

     /**
     * The attributes that are mass assignable.
     *
     * @var string[]
     */
    protected $fillable = [
        'naziv',
        'opis',
        'dashboard_path',
        'kat_id',
    ];

    // Relationship to users
    public function users(): HasMany
    {
        return $this->hasMany(User::class, 'pozicija_tipId');
    }
    
    /**
     * vraca poyicije koje moze da ima user
     *
     * @return array
     */
    public static function userRoleList(){
        foreach(PozicijaTip::all() as $pozicija){
            if($pozicija->naziv != 'Obrisan')  $pozicija_list[$pozicija->id] = $pozicija->naziv;
        }
        return  $pozicija_list;
    }

    /**
     * vraca sve pozicije koje moze da ima user za search polje
     *
     * @return array
     */
    public static function userRoleListAll(){
        foreach(PozicijaTip::all() as $pozicija){
           $pozicija_list[$pozicija->id] = $pozicija->naziv;
        }
        return  $pozicija_list;
    }

    public static function getDashboardByPozicija($pozicijaId){
        $pozicija = PozicijaTip::find($pozicijaId);
        return $pozicija ? $pozicija->dashboard_path : '';
    }

}
