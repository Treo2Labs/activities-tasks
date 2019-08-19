<?php
/**
 * ActivitiesTasks
 * TreoLabs Free Module
 * Copyright (c) TreoLabs GmbH
 *
 * This Software is the property of TreoLabs GmbH and is protected
 * by copyright law - it is NOT Freeware and can be used only in one project
 * under a proprietary license, which is delivered along with this program.
 * If not, see <https://treolabs.com/eula>.
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

namespace ActivitiesTasks;

use Treo\Core\ModuleManager\AbstractModule;

/**
 * Class Module
 *
 * @author r.ratsun <r.ratsun@treolabs.com>
 */
class Module extends AbstractModule
{
    protected $activitiesRow = [
        0 => ['name' => 'activitiesEntityList'],
        1 => ['name' => 'historyEntityList']
    ];

    /**
     * @inheritdoc
     */
    public static function getLoadOrder(): int
    {
        return 100;
    }

    /**
     * @inheritdoc
     */
    public function loadLayouts(string $scope, string $name, array &$data)
    {
        if ($scope == 'Settings') {
            if (array_search('Activities', array_column($data, 'label')) === false) {
                $item = ['label' => 'Activities'];
                $this->addRowActivities($item);
                $data[] = $item;
            } else {
                foreach ($data as &$item) {
                    if ($item['label'] === 'Activities') {
                        $this->addRowActivities($item);
                    }
                }
            }
        }
        parent::loadLayouts($scope, $name, $data);
    }

    /**
     * @param $item
     */
    private function addRowActivities(&$item) :void
    {
        if (isset($item['rows']) && is_array($item['rows'])) {
            array_unshift($item['rows'], $this->activitiesRow) ;
        } else {
            $item['rows'][] = $this->activitiesRow;
        }
    }
}
