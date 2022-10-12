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
use Tymon\JWTAuth\Contracts\JWTSubject;

/**
 * @Schema(
 *     schema="User",
 *     type="object",
 *     @Property(
 *         property="id",
 *         type="number",
 *         description="user id"
 *     ),
 *     @Property(
 *         property="name",
 *         type="string",
 *         description="user name"
 *     ),
 *     @Property(
 *         property="email",
 *         type="string",
 *         description="user email"
 *     )
 * )
 */
class User extends Model implements AuthenticatableContract, AuthorizableContract, JWTSubject
{
    use Authenticatable, Authorizable, HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var string[]
     */
    protected $fillable = [
        'name', 'email', 'image', 'profile', 'password',
        'third_party_id', 'third_party_user_id', 'third_party_user_info'
    ];

    /**
     * The attributes excluded from the model's JSON form.
     *
     * @var string[]
     */
    protected $hidden = [
        'image', 'profile', 'password', 'email_verified_at', 'remember_token', 'created_at', 'updated_at',
        'third_party_id', 'third_party_user_id', 'third_party_user_info',
        'access_token', 'token_type', 'expires_in', 'refresh_token'
    ];

    /**
     * @inheritDoc
     */
    public function getJWTIdentifier()
    {
        return $this->getKey();
    }

    /**
     * @inheritDoc
     */
    public function getJWTCustomClaims()
    {
        return [];
    }
}
