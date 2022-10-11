<?php

namespace App\Http\Controllers;

use Laravel\Lumen\Routing\Controller as BaseController;

use OpenApi\Annotations as OA;
use OpenApi\Annotations\Contact;
use OpenApi\Annotations\Info;
use OpenApi\Annotations\Property;
use OpenApi\Annotations\Schema;
use OpenApi\Annotations\Server;
use OpenApi\Annotations\SecurityScheme;
/**
 *
 * @Info(
 *     version="1.0.0",
 *     title="Api",
 *     description="Api documentation",
 *     @Contact(
 *         email="duweiwork@gmail.com",
 *         name="duwei"
 *     )
 * )
 *
 * @Server(
 *     url="/api",
 *     description="development",
 * )
 *
 * @SecurityScheme(
 *     type="http",
 *     securityScheme="bearerAuth",
 *     scheme="bearer",
 *     bearerFormat="JWT"
 * )
 *
 * @package App\Http\Controllers
 */
class Controller extends BaseController
{
    //
}
