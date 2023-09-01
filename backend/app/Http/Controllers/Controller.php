<?php

namespace App\Http\Controllers;

/**
 * @OA\Info(
 *   version="1.0.0",
 *   title="PARTYMAX",
 *   description="Official API for 'PARTYMAX' App",
 *   @OA\Contact(
 *       email="admin@gmail.com"
 *   ),
 *   @OA\License(
 *       name="Apache 2.0",
 *       url="http://www.apache.org/licenses/LICENSE-2.0.html"
 *   )
 * )
 * @OA\SecurityScheme(
 *   securityScheme="bearerAuth",
 *   in="header",
 *   name="bearerAuth",
 *   type="http",
 *   scheme="bearer",
 *   bearerFormat="JWT",
 * ),
 * @OA\Server(
 *   url=L5_SWAGGER_CONST_HOST,
 *   description="API"
 * )
 *
 */

use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Routing\Controller as BaseController;

class Controller extends BaseController
{
    use AuthorizesRequests, ValidatesRequests;
}
