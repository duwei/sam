<?php

namespace App\Models;

use Illuminate\Auth\Authenticatable;
use Illuminate\Contracts\Auth\Access\Authorizable as AuthorizableContract;
use Illuminate\Contracts\Auth\Authenticatable as AuthenticatableContract;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Laravel\Lumen\Auth\Authorizable;
use OpenApi\Annotations\Schema;
use OpenApi\Annotations\Property;

/**
 * @Schema(
 *     schema="Service",
 *     type="object",
 *     @Property(
 *         property="id",
 *         type="string",
 *         description="service id"
 *     ),
 *     @Property(
 *         property="third_party",
 *         type="object",
 *         description="third party",
 *         ref="#/components/schemas/ThirdParty",
 *     ),
 *     @Property(
 *         property="client_id",
 *         type="string",
 *         description="client id"
 *     ),
 *     @Property(
 *         property="client_secret",
 *         type="string",
 *         description="client secret"
 *     ),
 *     @Property(
 *         property="redirect_uri",
 *         type="string",
 *         description="redirect uri"
 *     ),
 *     @Property(
 *         property="scope",
 *         type="string",
 *         description="scope"
 *     ),
 *     @Property(
 *         property="client_uri",
 *         type="string",
 *         description="client uri"
 *     )
 * )
 */
class Service extends Model
{
    public $incrementing = false;

    protected $fillable = [
        'third_party_id', 'client_id', 'client_secret', 'redirect_uri', 'scope', 'client_uri', 'id'
    ];

    public function third_party()
    {
        return $this->belongsTo(ThirdParty::class);
    }

    protected $hidden = [
        'created_at', 'updated_at', 'third_party_id'
    ];

    public static function generateServiceid(int $length = 8): string
    {
        $service_id = 'Srv' . Str::random($length);
        $exists = DB::table('services')
            ->where('id', '=', $service_id)
            ->get(['id']);//Find matches for id = generated id
        if (isset($exists[0]->id)) {//id exists in users table
            return self::generateServiceid();//Retry with another generated id
        }

        return $service_id;//Return the generated id as it does not exist in the DB
    }

    public static function boot()
    {
        parent::boot();

        self::creating(function($model){
            $model->id = self::generateServiceid();
        });
    }
}
