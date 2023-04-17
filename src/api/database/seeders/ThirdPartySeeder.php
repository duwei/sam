<?php

namespace Database\Seeders;

use App\Models\ThirdParty;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class ThirdPartySeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        ThirdParty::truncate();
        ThirdParty::insert([
            [
                'name' => 'Google',
                'oauth_uri'     => 'https://oauth2.googleapis.com/token',
                'profile_uri'     => 'https://oauth2.googleapis.com/tokeninfo',
            ],
            [
                'name' => 'Facebook',
                'oauth_uri'     => 'https://facebook.com/oauth',
                'profile_uri'     => 'https://facebook.com/profile',
            ],
            [
                'name' => 'Tess',
                'oauth_uri'     => 'http://172.20.20.198:8080/api/oauth/token',
                'profile_uri'     => 'http://172.20.20.198:8080/api/api/user',
            ],
            [
                'name' => 'Kakao',
                'oauth_uri'     => 'https://kauth.kakao.com/oauth/token',
                'profile_uri'     => 'https://kapi.kakao.com/v2/user/me',
            ],
        ]);
    }
}
