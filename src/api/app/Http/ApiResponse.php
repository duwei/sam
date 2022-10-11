<?php
namespace App\Http;

use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Lang;
use OpenApi\Annotations\Property;
use OpenApi\Annotations\Schema;

/**
 * @Schema(
 *     schema="ApiResponse",
 *     type="object",
 *     required={"code", "msg"},
 *     @Property(
 *         property="code",
 *         type="number",
 *         description="response code"
 *     ),
 *     @Property(
 *         property="msg",
 *         type="string",
 *         description="response message"
 *     )
 * )
 */

class ApiResponse extends JsonResponse
{
    const RET_OK              = 0;
    const BAD_REQUEST         = 1;
    const UNAUTHORIZED        = 2;
    const CODE_EXPIRED        = 3;
    const INVALID_CODE        = 4;
    const USER_REGISTERED     = 5;
    const EMAIL_REGISTERED    = 6;
    const THIRD_PARTY_ERROR   = 7;
    const SERVICE_ID_MISSING  = 8;
    const INVALID_SERVICE_ID  = 9;

    public function __construct($code = self::RET_OK, $data = null, $status = 200)
    {
        $retData = array(
            'code' => $code,
            'msg'  => Lang::get('messages.code_' . $code),
        );
        if (! is_null($data)) {
            $retData['data'] = $data;
        }
        parent::__construct($retData, $status);
    }
}
