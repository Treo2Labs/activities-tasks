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
 * Class Composer
 */
class Composer extends AbstractListener
{
    /**
     * After install module event
     *
     * @param array $data
     */
    public function afterInstallModule(array $data): void
    {
        if (!empty($data['id']) && $data['id'] == 'ActivitiesTasks') {
            $navGroupName = "Activity";

            $this->setToConfig($navGroupName);
            $this->setTranslate($navGroupName);
        }

    }

    /**
     * Set navigation group to config
     *
     * @param string $navGroup
     */
    protected function setToConfig(string $navGroup): void
    {
        if ($this->getConfig()->has('twoLevelTabList')) {
            $tabField = 'twoLevelTabList';
        } else {
            $tabField = 'tabList';
        }

        $tabList = $this->getConfig()->get($tabField);
        $result = false;

        foreach ($tabList as $tab) {
            if ($tab instanceof \stdClass) {
                if ($tab->name == $navGroup) {
                    $result = true;
                }
            }
        }

        if (!$result) {
            $navActivities = [
                "id"    => '_delimiter_' . substr(Util::generateId(), 0, 8),
                "name"  => $navGroup,
                "items" => [
                    "Task",
                    "Meeting",
                    "Call"
                ]
            ];

            $tabList[] = (object)$navActivities;

            $this->getConfig()->set($tabField, $tabList);
            $this->getConfig()->save();
        }
    }

    /**
     * Set translate to navigation group
     *
     * @param string $navGroup
     */
    protected function setTranslate(string $navGroup): void
    {
        $scope     = 'Global';
        $category  = 'navMenuDelimiters';

        $this->getLanguage()->set($scope, $category, $navGroup, $navGroup);
        $this->getLanguage()->save();
    }
}
