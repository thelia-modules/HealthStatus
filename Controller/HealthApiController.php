<?php

namespace HealthStatus\Controller;

use OpenApi\Annotations as OA;
use OpenApi\Generator;
use OpenApi\Util;
use Thelia\Core\HttpFoundation\JsonResponse;
use function OpenApi\scan;
use Symfony\Component\Routing\Annotation\Route;
use Thelia\Controller\Front\BaseFrontController;
use Thelia\Core\HttpFoundation\Request;


/**
 * @OA\Info(
 *  title="Thelia OpenApi",
 *  version="0.0.1",
 * ),
 */
class HealthApiController extends BaseFrontController
{
    #[Route('/healthstatus/api/doc', name: 'healthstatus_doc', methods: ['GET'])]
    public function getDocumentation(Request $request)
    {
        header('Access-Control-Allow-Origin: *');
        $annotations = Generator::scan(Util::finder([
            THELIA_MODULE_DIR . 'HealthStatus/Controller',
        ]));


        $annotations = json_decode($annotations->toJson(), true);

        $host = $request->getSchemeAndHttpHost();
        $annotations['servers'] = [
            ['url' => $host.'/healthstatus/api'],
            ['url' => $host.'/index_dev.php/healtstatus/api'],
        ];

        return $this->render('swagger-ui', [
            'spec' => json_encode($annotations),
        ]);
    }
}