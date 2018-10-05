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
     * After delete module event
     *
     * @param array $data
     */
    public function afterDeleteModule(array $data): void
    {
        if (!empty($data['id']) && $data['id'] == 'ActivitiesTasks') {
            $this->removeFromConfig();
        }
    }

    /**
     * Get navigation menu data
     *
     * @return array
     */
    protected function getData(): array
    {
        $navMenu = false;

        // check if Two-Level Navigation module installed
        foreach ($this->getContainer()->get('metadata')->getModuleList() as $module) {
            if ($module == "NavMenu") {
                $navMenu = true;
                break;
            }
        }

        // tab field name in config
        if ($navMenu) {
            $tabField = 'twoLevelTabList';
        } else {
            $tabField = 'tabList';
        }

        // set data
        $data = [
            'tabList' => $this->getConfig()->get($tabField),
            'tabField' => $tabField,
            'items' => [
                "Task",
                "Meeting",
                "Call"
            ],
            'navMenu' => $navMenu,
            'navGroupName' => 'Activity'
        ];

        return $data;
    }

    /**
     * Set navigation group to config
     */
    protected function setToConfig(): void
    {
        $data = $this->getData();

        $tabList = $data['tabList'];

        if (!$data['navMenu']) {
            $tabList = $this->getNewTabList($data['items'], $tabList);
        } else {
            $tabList = $this->getNewTwoLevelTabList($data['items'], $tabList, $data['navGroupName']);
            $this->setTranslate($data['navGroupName']);
        }

        if (!is_null($tabList)) {
            $this->getConfig()->set($data['tabField'], $tabList);
            $this->getConfig()->save();
        }
    }

    /**
     * Remove navigation group from config
     */
    protected function removeFromConfig(): void
    {
        $data = $this->getData();

        $tabList = $data['tabList'];

        foreach ($tabList as $key => $tab) {
            if (in_array($tab, $data['items']) ||
                ($tab instanceof \stdClass && $tab->name == $data['navGroupName'])) {
                for ($i = (int)$key; $i < count($tabList) - 1; $i++) {
                    if (isset($tabList[$i + 1])) {
                        $tabList[$i] = $tabList[$i + 1];
                    }
                }

                unset($tabList[count($tabList) - 1]);
            }
        }

        if (!empty($tabList)) {
            $this->getConfig()->set($data['tabField'], $tabList);
            $this->getConfig()->save();
        }
    }

    /**
     * Gen new tab list
     *
     * @param array $items
     * @param array $list
     *
     * @return array|null
     */
    protected function getNewTabList(array $items, array $list): array
    {
        if (!empty($result = array_diff($items, $list))) {
            foreach ($result as $item) {
                $list[] = $item;
            }

            return $list;
        }

        return null;
    }

    /**
     * Get new two level tab list
     *
     * @param array $items
     * @param array $list
     * @param string $navGroup
     *
     * @return array|null
     */
    protected function getNewTwoLevelTabList(array $items, array $list, string $navGroup): array
    {
        $result = false;

        foreach ($list as $tab) {
            if ($tab instanceof \stdClass && $tab->name == $navGroup) {
                $result = true;
                break;
            }
        }

        if (!$result) {
            $navActivities = [
                "id" => '_delimiter_' . substr(Util::generateId(), 0, 8),
                "name" => $navGroup,
                "items" => $items
            ];

            $list[] = (object)$navActivities;

            return $list;
        }

        return null;
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
