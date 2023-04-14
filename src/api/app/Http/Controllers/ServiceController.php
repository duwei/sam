<?php
namespace App\Http\Controllers;

use App\Http\ApiResponse;
use App\Models\InquiryCategory;
use App\Models\InquiryStatus;
use App\Models\ThirdParty;
use App\Models\Service;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Redis;
use OpenApi\Annotations as OA;
use OpenApi\Annotations\Get;
use OpenApi\Annotations\MediaType;
use OpenApi\Annotations\Post;
use OpenApi\Annotations\Property;
use OpenApi\Annotations\RequestBody;
use OpenApi\Annotations\Response;
use OpenApi\Annotations\Schema;
use OpenApi\Annotations\Items;

class ServiceController extends Controller
{
    /**
     * @Get(
     *     path="/services",
     *     tags={"Service"},
     *     summary="all services list",
     *     @Response(
     *         response="200",
     *         description="registered services response",
     *         @MediaType(
     *             mediaType="application/json",
     *             @Schema(
     *                 allOf={
     *                     @Schema(ref="#/components/schemas/ApiResponse"),
     *                     @Schema (
     *                         @Property(
     *                             property="data",
     *                             description="response data",
     *                             type="array",
     *                             @Items(ref="#/components/schemas/Service")
     *                         )
     *                     )
     *                 }
     *             )
     *         )
     *     )
     * )
     *
     * @param Request $request
     *
     * @return ApiResponse
     */
    public function index(Request $request)
    {
        return response_data(Service::with('third_party')->get());
    }
    /**
     * @Post (
     *     path="/service/register",
     *     tags={"Service"},
     *     summary="register account service",
     *     @RequestBody(
     *         @MediaType(
     *             mediaType="application/json",
     *             @Schema(
     *                type="object",
     *                @Property(
     *                    property="third_party",
     *                    type="string",
     *                    description="third party: Google, Facebook, Tess",
     *                ),
     *                @Property(
     *                  property="client_id",
     *                  description="client id, from third party",
     *                  type="string"
     *                ),
     *                @Property(
     *                  property="client_secret",
     *                  description="client secret, from third party",
     *                  type="string"
     *                ),
     *                @Property(
     *                  property="redirect_uri",
     *                  description="redirect uri, it should be set to: https://tessverso.io/sam/api/callback",
     *                  type="string"
     *                ),
     *                @Property(
     *                  property="client_uri",
     *                  description="client uri, optional, if empty,  '/success' will be used",
     *                  type="string"
     *                ),
     *                @Property(
     *                  property="scope",
     *                  description="scope",
     *                  type="string"
     *                ),
     *                example={
     *                 "third_party": "Google",
     *                 "client_id": "98eb7a8c-636f-4bb1-8108-f7cf38af09cb",
     *                 "client_secret": "XCMx7GWjvHqqF72IzGjcNoLh9N59ksfK1rHcyPhj",
     *                 "redirect_uri": "https://tessverso.io/sam/api/callback",
     *                 "client_uri": "http://localhost:3000/googlelogin/redirect",
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
     *                                      property="service",
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
    public function create(Request $request)
    {
        $this->validate($request, [
            'third_party' => 'required|exists:third_parties,name',
            'client_id' => 'required|string',
            'client_secret' => 'required|string',
            'redirect_uri' => 'required|string',
            'scope' => 'required|string',
            'client_uri' => 'string',
        ]);
        $request->merge(['third_party_id' => ThirdParty::whereName($request->third_party)->first()->id]);
        $service = Service::create($request->all());

        return response_data(['service_id' => $service->id]);
    }

    /**
     * @Post(
     *     path="/service/update",
     *     tags={"Service"},
     *     summary="update service",
     *     @RequestBody(
     *         @MediaType(
     *             mediaType="application/json",
     *             @Schema(
     *                type="object",
     *                @Property(
     *                  property="id",
     *                  description="service id",
     *                  type="string"
     *                ),
     *                @Property(
     *                    property="third_party",
     *                    type="string",
     *                    description="third party Google, Facebook, Tess",
     *                ),
     *                @Property(
     *                  property="client_id",
     *                  description="client id, from third party",
     *                  type="string"
     *                ),
     *                @Property(
     *                  property="client_secret",
     *                  description="client secret, from third party",
     *                  type="string"
     *                ),
     *                @Property(
     *                  property="redirect_uri",
     *                  description="redirect uri, it should be set to: https://tessverso.io/sam/api/callback",
     *                  type="string"
     *                ),
     *                @Property(
     *                  property="client_uri",
     *                  description="client uri, optional, if empty,  '/success' will be used",
     *                  type="string"
     *                ),
     *                @Property(
     *                  property="scope",
     *                  description="scope",
     *                  type="string"
     *                ),
     *                example={
     *                 "id": "Srv2AH0J9yv",
     *                 "third_party": "Google",
     *                 "client_id": "98eb7a8c-636f-4bb1-8108-f7cf38af09cb",
     *                 "client_secret": "XCMx7GWjvHqqF72IzGjcNoLh9N59ksfK1rHcyPhj",
     *                 "redirect_uri": "https://tessverso.io/sam/api/callback",
     *                 "client_uri": "http://localhost:3000/googlelogin/redirect",
     *                 "scope": "*"
     *                }
     *             )
     *          )
     *     ),
     *     @Response(
     *         response="200",
     *         description="inquiry categories response",
     *         @MediaType(
     *             mediaType="application/json",
     *             @Schema(
     *                 allOf={
     *                     @Schema(ref="#/components/schemas/ApiResponse"),
     *                 }
     *             )
     *         )
     *     )
     * )
     *
     * @param Request $request
     *
     * @return ApiResponse
     */
    public function update(Request $request)
    {
        $this->validate($request, [
            'id' => 'required|exists:services,id',
            'third_party' => 'required|exists:third_parties,name',
            'client_id' => 'string',
            'client_secret' => 'string',
            'redirect_uri' => 'string',
            'client_uri' => 'string',
            'scope' => 'string',
        ]);
        $request->merge(['third_party_id' => ThirdParty::whereName($request->third_party)->first()->id]);
        Service::find($request->get('id'))->updateOrFail($request->all());

        return response_code();
    }
    /**
     * @Post(
     *     path="/service/delete",
     *     tags={"Service"},
     *     summary="delete service",
     *     @RequestBody(
     *         @MediaType(
     *             mediaType="application/json",
     *             @Schema(
     *                type="object",
     *                required={"id"},
     *                @Property(
     *                  property="id",
     *                  description="service id",
     *                  type="string",
     *                ),
     *                example={
     *                 "id": "Srv2AH0J9yv"
     *                }
     *             )
     *          )
     *     ),
     *     @Response(
     *         response="200",
     *         description="services response",
     *         @MediaType(
     *             mediaType="application/json",
     *             @Schema(
     *                 allOf={
     *                     @Schema(ref="#/components/schemas/ApiResponse"),
     *                 }
     *             )
     *         )
     *     )
     * )
     *
     * @param Request $request
     *
     * @return ApiResponse
     */
    public function delete(Request $request)
    {
        $this->validate($request, [
            'id' => 'required|exists:services,id',
        ]);
        Service::destroy($request->get('id'));

        return response_code();
    }
}
