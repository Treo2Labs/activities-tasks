<?php
/**
 * ActivitiesTasks
 * TreoPIM Premium Plugin
 * Copyright (c) Zinit Solutions GmbH
 *
 * This Software is the property of Zinit Solutions GmbH and is protected
 * by copyright law - it is NOT Freeware and can be used only in one project
 * under a proprietary license, which is delivered along with this program.
 * If not, see <http://treopim.com/eula>.
 *
 * This Software is distributed as is, with LIMITED WARRANTY AND LIABILITY.
 * Any unauthorised use of this Software without a valid license is
 * a violation of the License Agreement.
 *
 * According to the terms of the license you shall not resell, sublicense,
 * rent, lease, distribute or otherwise transfer rights or usage of this
 * Software or its derivatives. You may modify the code of this Software
 * for your own needs, if source code is provided.
 */

declare(strict_types=1);

namespace Espo\Modules\ActivitiesTasks\Listeners;

use Espo\Core\ORM\EntityManager;
use Espo\Modules\TreoCore\Listeners\AbstractListener;
use Espo\Core\Utils\Util;

/**
 * Class Installer
 */
class Installer extends AbstractListener
{
    /**
     * After installation system action
     */
    public function afterInstallSystem()
    {
        $delimiter = '_delimiter_' . substr(Util::generateId(), 0, 8);
        $this->setToConfig($delimiter);
        $this->setToLabelManager($delimiter);
    }

    protected function setToConfig(string $delimiter)
    {
        $tabList = $this->getConfig()->get('tabList');

        $navActivities = [
            "name"  => $delimiter,
            "items" => [
                "Task",
                "Meeting",
                "Call"
            ]
        ];

        $tabList[] = (object)$navActivities;

        $this->getConfig()->set('tabList', $tabList);
        $this->getConfig()->save();
    }

    protected function setToLabelManager(string $delimiter)
    {
        $scope     = 'Global';
        $category  = 'navMenuDelimiters';
        $language = $this->getLanguage()->getLanguage();
        $label[$category . '[.]' . $delimiter] = "Activity";

        $labelManager = $this->getContainer()->get('injectableFactory')
            ->createByClassName('\\Espo\\Core\\Utils\\LabelManager');
        $labelManager->saveLabels($language, $scope, $label);
    }
}