<?php

namespace HealthStatus\Controller;

use OpenApi\Annotations as OA;
use OpenApi\Generator;
use OpenApi\Util;
use function OpenApi\scan;
use Symfony\Component\Routing\Annotation\Route;
use Thelia\Controller\Front\BaseFrontController;
use Thelia\Core\HttpFoundation\Request;

class HealthApiController extends BaseFrontController
{
    #[Route('/doc', name: 'healthstatus_doc', methods: ['GET'])]
    public function getDocumentation(Request $request)
    {
        header('Access-Control-Allow-Origin: *');
        $annotations = Generator::scan(Util::finder([
            THELIA_MODULE_DIR . 'HealthStatus/Controller',
        ]));


        $annotations = json_decode($annotations->toJson(), true);

        $host = $request->getSchemeAndHttpHost();
        $annotations['servers'] = [
            ['url' => $host.'/healthstatus/open_api'],
            ['url' => $host.'/index_dev.php/healthstatus/open_api'],
        ];

        return $this->render('swagger-ui', [
            'spec' => json_encode($annotations),
        ]);
    }
}