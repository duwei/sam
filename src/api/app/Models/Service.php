<?php

namespace App\Models;

use Illuminate\Auth\Authenticatable;
use Illuminate\Contracts\Auth\Access\Authorizable as AuthorizableContract;
use Illuminate\Contracts\Auth\Authenticatable as AuthenticatableContract;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Laravel\Lumen\Auth\Authorizable;
use OpenApi\Annotations\Schema;
use OpenApi\Annotations\Property;

/**
 * @Schema(
 *     schema="Service",
 *     type="object",
 *     @Property(
 *         property="id",
 *         type="number",
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
    protected $fillable = [
        'third_party_id', 'client_id', 'client_secret', 'redirect_uri', 'scope', 'client_uri'
    ];

    public function third_party()
    {
        return $this->belongsTo(ThirdParty::class);
    }

    protected $hidden = [
        'created_at', 'updated_at', 'third_party_id'
    ];
}
