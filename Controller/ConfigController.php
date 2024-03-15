<?php

/*
 * This file is part of the Thelia package.
 * http://www.thelia.net
 *
 * (c) OpenStudio <info@thelia.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace HealthStatus\Controller;

use Thelia\Controller\Admin\BaseAdminController;

class ConfigController extends BaseAdminController
{
    public function setAction()
    {
        return $this->render('module-configure', [
            'module_code' => 'HealthStatus',
        ]);
    }


}
