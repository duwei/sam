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
 *     schema="ThirdParty",
 *     type="object",
 *     @Property(
 *         property="id",
 *         type="number",
 *         description="third party id"
 *     ),
 *     @Property(
 *         property="name",
 *         type="string",
 *         description="third party name"
 *     )
 * )
 */
class ThirdParty extends Model
{
    const GOOGLE   = 1;
    const FACEBOOK = 2;
    const TESS     = 3;
    const KAKAO    = 4;

    protected $hidden = [
        'oauth_uri', 'profile_uri', 'created_at', 'updated_at'
    ];
}
