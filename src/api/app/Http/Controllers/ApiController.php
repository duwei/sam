<?php
namespace App\Http\Controllers;

use Exception;
use App\Http\ApiResponse;
use App\Models\Service;
use App\Models\ThirdParty;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Env;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Redis;

use Illuminate\Support\Str;
use Lcobucci\JWT\Encoding\JoseEncoder;
use Lcobucci\JWT\Token\Parser;
use OpenApi\Annotations\Get;
use OpenApi\Annotations\Post;
use OpenApi\Annotations\MediaType;
use OpenApi\Annotations\Property;
use OpenApi\Annotations\RequestBody;
use OpenApi\Annotations\Response;
use OpenApi\Annotations\Schema;
use OpenApi\Annotations\Items;
use Tymon\JWTAuth\Contracts\JWTSubject;
use Tymon\JWTAuth\Facades\JWTAuth;
use Tymon\JWTAuth\Facades\JWTFactory;
use Tymon\JWTAuth\Facades\JWTProvider;
use Tymon\JWTAuth\JWT;
use Tymon\JWTAuth\Manager;
use Tymon\JWTAuth\Payload;
use Tymon\JWTAuth\Token;

class ApiController extends Controller
{
    private array $fields;

    public function __construct()
    {
        $this->fields = ['id', 'password'];
        $this->middleware('auth:api', ['except' => ['login', 'register', 'callback', 'success']]);
    }
    /**
     * @Get (
     *     path="/callback",
     *     tags={"OAuth"},
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
     *                 "state": "service_id=Srv2AH0J9yv",
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
            case ThirdParty::KAKAO:
                $ret = $http->request('GET', $service->third_party->profile_uri, [
                    'headers' => [
                        'Accept'     => 'application/json',
                        'Authorization' => 'Bearer '. $tokenInfo['access_token'],
                    ]
                ]);
                $userInfo = json_decode((string)$ret->getBody(), true);
                $token = $this->createOrUpdateUser(
                    ThirdParty::KAKAO, $service->id, $userInfo['id'], $userInfo, $tokenInfo);
                $successUri = is_null($service->client_uri) || empty($service->client_uri) ? '/success' : $service->client_uri;
                return redirect($successUri . '#' . $token);
            case ThirdParty::TESS:
                $ret = $http->request('GET', $service->third_party->profile_uri, [
                    'headers' => [
                        'Accept'     => 'application/json',
                        'Authorization' => 'Bearer '. $tokenInfo['access_token'],
                    ]
                ]);
                $userInfo = json_decode((string)$ret->getBody(), true);
                $token = $this->createOrUpdateUser(
                    ThirdParty::TESS, $service->id, $userInfo['id'], $userInfo, $tokenInfo);
                // todo token ttl
//                auth('api')->factory()->setTTL();
                $successUri = is_null($service->client_uri) || empty($service->client_uri) ? '/success' : $service->client_uri;
                return redirect($successUri . '#' . $token);
//            case ThirdParty::FACEBOOK:
//                break;
            case ThirdParty::GOOGLE:
                $parser = new Parser(new JoseEncoder());
                $userInfo = $parser->parse($tokenInfo['id_token'])->claims()->all();
                $token = $this->createOrUpdateUser(
                    ThirdParty::GOOGLE, $service->id, $userInfo['sub'], $userInfo, $tokenInfo
                );
                $successUri = is_null($service->client_uri) || empty($service->client_uri) ? '/success' : $service->client_uri;
                return redirect($successUri . '#' . $token);
        }
        // todo: add refresh user info task
        return response_code(ApiResponse::BAD_REQUEST);
    }

    public function success(Request $request)
    {
        return 'success';
    }

    /**
     * @Post (
     *     path="/tess/login",
     *     tags={"Account"},
     *     summary="tess user login",
     *     deprecated=true,
     *     @RequestBody(
     *         @MediaType(
     *             mediaType="application/json",
     *             @Schema(
     *                type="object",
     *                @Property(
     *                  property="password",
     *                  description="user password",
     *                  type="string"
     *                ),
     *                @Property(
     *                  property="email",
     *                  description="user email",
     *                  type="string"
     *                ),
     *                @Property(
     *                  property="service_id",
     *                  description="service id",
     *                  type="string"
     *                ),
     *                example={
     *                 "password": "password",
     *                 "email": "tom@hotmail.com",
     *                 "service_id": "Srvrr9ENY7f",
     *                }
     *             )
     *          )
     *     ),
     *     @Response(
     *         response="200",
     *         description="login user response",
     *         @MediaType(
     *             mediaType="application/json",
     *             @Schema(
     *                 allOf={
     *                     @Schema(ref="#/components/schemas/ApiResponse"),
     *                     @Schema (
     *                         @Property(
     *                             property="data",
     *                             description="response data",
     *                             ref="#/components/schemas/JWTToken"
     *                         )
     *                     )
     *                 }
     *             )
     *         )
     *     )
     *  )
     */
    public function login(Request $request)
    {
        $credentials = $request->only(['email', 'password']);

        $tess = Env::get('TESS_URI');
        if (is_null($tess)) {
            throw new Exception('TESS_URI is not set.');
        }
        $client = new \GuzzleHttp\Client;
        $ret = json_decode($client->request('POST', $tess . '/login', [
            'json' => $credentials
        ])->getBody()->getContents(), true);
        if (isset($ret['code']) && $ret['code'] == 0) {
            $tokenInfo = $ret['data'];
            $headers = [
                'Authorization' => 'Bearer ' . $tokenInfo['access_token'],
                'Accept'        => 'application/json',
            ];
            $ret = json_decode($client->request('GET', $tess . '/me', [
                'headers' => $headers
            ])->getBody()->getContents(), true);
            if (isset($ret['code']) && $ret['code'] == 0) {
                $userInfo = $ret['data'];
                $token = $this->createOrUpdateUser(
                    ThirdParty::TESS, $request->service_id, $userInfo['id'], $userInfo, $tokenInfo);
                return $this->respondWithToken($token);
            } else {
                return response_code(ApiResponse::SERVER_ERROR);
            }
        } else {
            return response_code(ApiResponse::BAD_REQUEST);
        }
    }

    /**
     * @Get (
     *     path="/me",
     *     tags={"Account"},
     *     summary="user profile",
     *     security={{"bearerAuth":{}}},
     *     @Response(
     *         response="200",
     *         description="user profile response",
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

    /**
     * Get the authenticated User.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function me()
    {
        return response_data(auth('api')->user());
    }

    /**
     * @Get (
     *     path="/token/verify",
     *     tags={"Token"},
     *     summary="verify token",
     *     security={{"bearerAuth":{}}},
     *     @Response(
     *         response="200",
     *         description="verify token response",
     *         @MediaType(
     *             mediaType="application/json",
     *             @Schema(
     *                 allOf={
     *                     @Schema(ref="#/components/schemas/ApiResponse"),
     *                 }
     *             )
     *         )
     *     )
     *  )
     */

    public function verify()
    {
        return response_code();
    }

    /**
     * @Post (
     *     path="/token/refresh",
     *     tags={"Token"},
     *     summary="JWT token refresh",
     *     security={{"bearerAuth":{}}},
     *     @Response(
     *         response="200",
     *         description="refresh JWT token response",
     *         @MediaType(
     *             mediaType="application/json",
     *             @Schema(
     *                 allOf={
     *                     @Schema(ref="#/components/schemas/ApiResponse"),
     *                     @Schema (
     *                         @Property(
     *                             property="data",
     *                             description="response data",
     *                             ref="#/components/schemas/JWTToken"
     *                         )
     *                     )
     *                 }
     *             )
     *         )
     *     )
     *  )
     */
    public function refresh()
    {
        return $this->respondWithToken(auth('api')->refresh());
    }
    /**
     * @Get (
     *     path="/token/invalidate",
     *     tags={"Token"},
     *     summary="invalidate current token",
     *     security={{"bearerAuth":{}}},
     *     @Response(
     *         response="200",
     *         description="invalidate current token response",
     *         @MediaType(
     *             mediaType="application/json",
     *             @Schema(
     *                 allOf={
     *                     @Schema(ref="#/components/schemas/ApiResponse"),
     *                 }
     *             )
     *         )
     *     )
     *  )
     */
    public function logout()
    {
        auth("api")->logout();
        return response_code();
    }

    /**
     * @Schema(
     *     schema="JWTToken",
     *     type="object",
     *     @Property(
     *         property="access_token",
     *         type="string",
     *         description="access token"
     *     ),
     *     @Property(
     *         property="token_type",
     *         type="string",
     *         description="token type"
     *     ),
     *     @Property(
     *         property="expires_in",
     *         type="integer",
     *         description="token expiration time"
     *     )
     * )
     */
    /**
     * Get the token array structure.
     *
     * @param string $token
     *
     * @return \Illuminate\Http\JsonResponse
     */
    protected function respondWithToken($token)
    {
        return response_data([
            'access_token' => $token,
            'token_type' => 'bearer',
            'expires_in' => auth('api')->factory()->getTTL() * 60
        ]);
    }

    private function createOrUpdateUser($third_party_id, $service_id, $third_party_uid, $userInfo, $tokenInfo)
    {
        $name = $userInfo['name'] ?? $userInfo['properties']['nickname'] ?? '';
        $email = $userInfo['email'] ?? $userInfo['kakao_account']['email'] ?? '';
        $image = $userInfo['picture'] ?? $userInfo['properties']['profile_image'] ?? '';
        $profile = $userInfo['profile'] ?? '';

        $accessToken = DB::transaction(function () use (
            $third_party_id, $service_id, $third_party_uid, $userInfo, $tokenInfo, $name, $email, $image, $profile) {
            $user = User::firstOrCreate([
                'third_party_id' => $third_party_id,
                'third_party_user_id' => $third_party_uid,
                'service_id' => $service_id,
            ], [
                'third_party_user_info' => json_encode($userInfo),
                'name' => $name,
                'email' => $email,
                'image' => $image,
                'profile' => $profile
            ]);

            $password = str::uuid();
            $user->password = app('hash')->make($password);
            $user->token_type = $tokenInfo['token_type'];
            $user->expires_in = $tokenInfo['expires_in'];
            $user->access_token = $tokenInfo['access_token'];
            $user->refresh_token = $tokenInfo['refresh_token'] ?? '';
            $user->save();

            $user->password = $password;
            $credentials = $user->only($this->fields);

            $token = auth('api')->attempt($credentials);
            return $token;
        }, 5);
        return $accessToken;
    }
}
