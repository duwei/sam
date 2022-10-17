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
                'oauth_uri'     => 'http://211.110.209.62:8081/api/oauth/token',
                'profile_uri'     => 'http://211.110.209.62:8081/api/api/user',
            ],
        ]);
    }
}
