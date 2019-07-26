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

use Treo\Core\ModuleManager\AbstractEvent;

/**
 * Class Event
 *
 * @author r.ratsun <r.ratsun@treolabs.com>
 */
class Event extends AbstractEvent
{
    const ACTIVITY_GROUP_ID = '_delimiter_activity';
    const ACTIVITY_GROUP_NAME = 'Activity';

    /**
     * @var array
     */
    protected $tabList = [];

    /**
     * @var array
     */
    protected $twoLevelTabList = [];

    /**
     * @var array
     */
    protected $items
        = [
            "Task",
            "Meeting",
            "Call"
        ];

    /**
     * @inheritdoc
     */
    public function afterInstall(): void
    {
        $this->prepareNavMenu();
    }

    /**
     * @inheritdoc
     */
    public function afterDelete(): void
    {
    }


    /**
     * Prepare NavMenu
     */
    protected function prepareNavMenu(): void
    {
        // get config
        $config = $this->getContainer()->get('config');

        // prepare data
        $this->tabList = $config->get('tabList');
        $this->twoLevelTabList = $config->get('twoLevelTabList');

        // prepare TabList
        $this->prepareTabList();

        // prepare twoLevelTabList
        $this->prepareTwoLevelTabList();

        // save
        $config->set('tabList', $this->tabList);
        $config->set('twoLevelTabList', $this->twoLevelTabList);
        $config->save();
    }

    /**
     * Prepare tab list
     */
    protected function prepareTabList(): void
    {
        foreach ($this->items as $v) {
            if (!in_array($v, $this->tabList)) {
                $this->tabList[] = $v;
            }
        }
    }

    /**
     * Prepare two level tab list
     */
    protected function prepareTwoLevelTabList(): void
    {
        // create group
        $this->createGroup();

        foreach ($this->twoLevelTabList as $k => $item) {
            if (!is_string($item) && $item->id == self::ACTIVITY_GROUP_ID) {
                foreach ($this->items as $v) {
                    if (!in_array($v, $item->items)) {
                        $this->twoLevelTabList[$k]->items[] = $v;
                    }
                }
            }
        }
    }

    /**
     * Create group
     *
     * @return bool
     */
    protected function createGroup(): bool
    {
        foreach ($this->twoLevelTabList as $item) {
            if (!is_string($item) && $item->id == self::ACTIVITY_GROUP_ID) {
                return false;
            }
        }

        $this->twoLevelTabList[] = (object)[
            "id" => self::ACTIVITY_GROUP_ID,
            "name" => self::ACTIVITY_GROUP_NAME,
            "items" => [],
            "iconClass" => "fas fa-list-alt"
        ];

        return true;
    }
}
