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

use Espo\Core\Utils\Util;
use Treo\Listeners\AbstractListener;

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
            $this->setToConfig();
        }
    }

    /**
     * Get navigation menu data
     *
     * @return array
     */
    protected function getData(): array
    {
        $config = $this->getConfig();

        return [
            'tabList' => $config->get('tabList'),
            'twoLevelTabList' => $config->get('twoLevelTabList'),
            'items' => [
                "Task",
                "Meeting",
                "Call"
            ],
            'navGroupName' => 'Activity'
        ];
    }

    /**
     * Set navigation group to config
     */
    protected function setToConfig(): void
    {
        $data = $this->getData();

        $tabList = $this->getNewTabList($data['items'], $data['tabList']);

        $twoLevelTabList
            = $this->getNewTwoLevelTabList($data['items'], $data['twoLevelTabList'], $data['navGroupName']);

        if (!empty($tabList) && !empty($twoLevelTabList)) {
            $this->getConfig()->set('tabList', $tabList);
            $this->getConfig()->set('twoLevelTabList', $twoLevelTabList);
            $this->getConfig()->save();
        }
    }

    /**
     * Gen new tab list
     *
     * @param array $items
     * @param array $list
     *
     * @return array
     */
    protected function getNewTabList(array $items, array $list): array
    {
        if (!empty($result = array_diff($items, $list))) {
            foreach ($result as $item) {
                $list[] = $item;
            }
        }

        return $list;
    }

    /**
     * Remove items of activity from navigation menu if they exist
     *
     * @param array $items
     * @param array $list
     * @param string $delimiter
     *
     * @return array
     */
    protected function removeActivityItems(array $items, array $list, string $delimiter): array
    {
        foreach ($list as $tabKey => $tab) {
            if ($tab instanceof \stdClass && $tab->id != $delimiter) {
                foreach ($tab->items as $key => $item) {
                    if (in_array($item, $items)) {
                        array_splice($tab->items, (int)$key, 1);
                    }
                }
            } else {
                if (in_array($tab, $items)) {
                    array_splice($list, (int)$tabKey, 1);
                }
            }
        }

        return $list;
    }

    /**
     * Get new two level tab list
     *
     * @param array $items
     * @param array $list
     * @param string $navGroup
     *
     * @return array
     */
    protected function getNewTwoLevelTabList(array $items, array $list, string $navGroup): array
    {
        $result = null;
        $delimiter = '_delimiter_activity';

        $list = $this->removeActivityItems($items, $list, $delimiter);

        foreach ($list as $key => $tab) {
            if (!in_array($tab, $items)) {
                if ($tab instanceof \stdClass && $tab->id == $delimiter) {
                    $result = (int)$key;
                }
            }
        }

        if (is_null($result)) {
            $navActivities = [
                "id" => $delimiter,
                "name" => $navGroup,
                "items" => $items
            ];

            $list[] = (object)$navActivities;
        } else {
            foreach ($items as $item) {
                if (!in_array($item, $list[$result]->items)) {
                    $list[$result]->items[] = $item;
                }
            }
        }

        return $list;
    }
}
