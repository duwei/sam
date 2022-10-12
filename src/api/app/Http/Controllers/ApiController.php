<?php
namespace App\Http\Controllers;

use App\Http\ApiResponse;
use App\Models\Service;
use App\Models\ThirdParty;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Redis;

use OpenApi\Annotations\Get;
use OpenApi\Annotations\Post;
use OpenApi\Annotations\MediaType;
use OpenApi\Annotations\Property;
use OpenApi\Annotations\RequestBody;
use OpenApi\Annotations\Response;
use OpenApi\Annotations\Schema;
use OpenApi\Annotations\Items;

class ApiController extends Controller
{
    /**
     * @Post (
     *     path="/register",
     *     summary="register account service",
     *     @RequestBody(
     *         @MediaType(
     *             mediaType="application/json",
     *             @Schema(
     *                type="object",
     *                @Property(
     *                    property="third_party_id",
     *                    type="number",
     *                    description="third party id: 1 => Google, 2 => Facebook, 3 => Tess",
     *                ),
     *                @Property(
     *                  property="client_id",
     *                  description="client id",
     *                  type="string"
     *                ),
     *                @Property(
     *                  property="client_secret",
     *                  description="client secret",
     *                  type="string"
     *                ),
     *                @Property(
     *                  property="redirect_uri",
     *                  description="redirect uri",
     *                  type="string"
     *                ),
     *                @Property(
     *                  property="scope",
     *                  description="scope",
     *                  type="string"
     *                ),
     *                example={
     *                 "third_party_id": "3",
     *                 "client_id": "4",
     *                 "client_secret": "XCMx7GWjvHqqF72IzGjcNoLh9N59ksfK1rHcyPhj",
     *                 "redirect_uri": "http://localhost:8080/callback",
     *                 "scope": "*"
     *                }
     *             )
     *          )
     *     ),
     *     @Response(
     *         response="200",
     *         description="register user response",
     *         @MediaType(
     *             mediaType="application/json",
     *             @Schema(
     *                 allOf={
     *                     @Schema(ref="#/components/schemas/ApiResponse"),
     *                     @Schema (
     *                         @Property(
     *                             property="data",
     *                             description="response data",
     *                                    type="object",
     *                                    @Property(
     *                                      property="service_id",
     *                                      description="service state id",
     *                                      type="string"
     *                                    ),
     *                         )
     *                     )
     *                 }
     *             )
     *         )
     *     )
     *  )
     */
    public function register(Request $request)
    {
        $this->validate($request, [
            'third_party_id' => 'required|exists:third_parties,id',
            'client_id' => 'required|string',
            'client_secret' => 'required|string',
            'redirect_uri' => 'required|string',
            'scope' => 'required|string',
        ]);
        $service = Service::create($request->all());

        return response_data(['service_id' => $service->id]);
    }

    /**
     * @Get (
     *     path="/callback",
     *     summary="oauth callback api for third party.",
     *     @RequestBody(
     *         @MediaType(
     *             mediaType="application/json",
     *             @Schema(
     *                type="object",
     *                @Property(
     *                    property="code",
     *                    type="string",
     *                    description="callback code",
     *                ),
     *                @Property(
     *                    property="state",
     *                    type="string",
     *                    description="callback state",
     *                ),
     *                example={
     *                 "code": "def50200d92a61b414fbe81d409f3698c30eb7a549faf69aae0d451c6f766d8bfcb46056c94932ce038a9260e81668c61c7b3c6f90c4ec32048f6dc5ccd1a9bf694fd73ef672bfff26bbf0c030391722340d34b37983d8ba27f26e389f6ef06e4480364bbbf9cb35c4f4dd8e7c7d333e36ef7656df98706b07a5d83614102644bae58a694623685ca843ae322360e67a3df026e6d9e717cbde0b8b803b954d7077345b78eb96ef0a5f4fa3ed418c8abbc6cbfffe66d96badc17895d8bf2913869189f1da75d4b67bcfeaa6a9aa5482b623ac83dba639d172a12510ce4dae91d581ce9ac744a3d80159eb4cbd136b0d51f07b03c27117c158b009dd5cdbd671a662b58020bf7c35ac7534d73c8be0976b40ccfb1c68246fd0d0bad07c6b7fba4c692b411ed885bda919ca30c36c5459ed22934c729e191c68d95e8c71ebb663b6542ec0445b96cedef5468e3face2183c79c68db96d55a69afab1a4df35ca809ad9e1e7003d52",
     *                 "state": "service_id=1",
     *                }
     *             )
     *          )
     *     ),
     *     @Response(
     *         response="200",
     *         description="register user response",
     *         @MediaType(
     *             mediaType="application/json",
     *             @Schema(
     *                 allOf={
     *                     @Schema(ref="#/components/schemas/ApiResponse"),
     *                     @Schema (
     *                         @Property(
     *                             property="data",
     *                             description="response data",
     *                             ref="#/components/schemas/User"
     *                         )
     *                     )
     *                 }
     *             )
     *         )
     *     )
     *  )
     */
    public function callback(Request $request)
    {
        if (isset($_GET['error'])) {
            return response_code(ApiResponse::THIRD_PARTY_ERROR);
        }
        if (!isset($_GET['code']) || !isset($_GET['state'])) {
            return response_code(ApiResponse::BAD_REQUEST);
        }
        parse_str($_GET['state'], $state);
        if (!isset($state['service_id'])) {
            return response_code(ApiResponse::SERVICE_ID_MISSING);
        }
        $service = Service::find($state['service_id']);
        if (is_null($service)) {
            return response_code(ApiResponse::INVALID_SERVICE_ID);
        }
        $http = new \GuzzleHttp\Client;
        $ret = $http->post($service->third_party->oauth_uri, [
            'form_params' => [
                'grant_type' => 'authorization_code',
                'client_id' => $service->client_id,
                'client_secret' => $service->client_secret,
                'redirect_uri' => $service->redirect_uri,
                'code' => $_GET['code'],
            ],
        ]);
        $tokenInfo = json_decode((string)$ret->getBody(), true);
        switch ($service->third_party->id) {
            case ThirdParty::TESS:
                $service->token_type = $tokenInfo['token_type'];
                $service->expires_in = $tokenInfo['expires_in'];
                $service->access_token = $tokenInfo['access_token'];
                $service->refresh_token = $tokenInfo['refresh_token'];

                $ret = $http->request('GET', $service->third_party->profile_uri, [
                    'headers' => [
                        'Accept'     => 'application/json',
                        'Authorization' => 'Bearer '. $service->access_token,
                    ]
                ]);
                $userInfo = json_decode((string)$ret->getBody(), true);
                $user = User::firstOrCreate([
                    'third_party_id' => ThirdParty::TESS,
                    'third_party_user_id' => $userInfo['id'],
                    'third_party_user_info' => $ret->getBody()
                ], [
                    'name' => $userInfo['name'],
                    'email' => $userInfo['email'],
                    'image' => '',
                    'profile' => ''
                ]);
                $user->touch();
                break;
//            case ThirdParty::FACEBOOK:
//                break;
//            case ThirdParty::GOOGLE:
//                break;
        }
        $service->save();
        // todo: add refresh user info tas
        return response_data($user);
    }
}

