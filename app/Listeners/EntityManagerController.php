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

namespace ActivitiesTasks\Listeners;

use Treo\Listeners\AbstractListener;
use Treo\Core\EventManager\Event;

/**
 * Class EntityManagerController
 *
 * @author m.kokhanskiy@treolabs.com
 */
class EntityManagerController extends AbstractListener
{

    protected $params = [
        'hasActivities',
        'hasTasks'
    ];

    /**
     * @param Event $event
     */
    public function afterActionCreateEntity(Event $event)
    {
        $data = $event->getArgument('data');
        $this->setParams($data);
    }

    /**
     * @param Event $event
     */
    public function beforeActionUpdateEntity(Event $event)
    {
        $data = $event->getArgument('data');
        $this->setParams($data);
    }

    /**
     * Set all $this->params in scope
     *
     * @param $data
     */
    protected function setParams($data)
    {
        $scope = $data->name;

        foreach ($this->params as $param) {
            $value = !empty($data->{$param});

            $this->setValueOfParam($scope, $param, $value);
        }
    }

    /**
     * Set param in metadata
     *
     * @param string $scope
     * @param string $param
     * @param bool $value
     */
    protected function setValueOfParam(string $scope, string $param, bool $value): void
    {
        // prepare data
        $data = $this
            ->getContainer()
            ->get('metadata')
            ->get("scopes.{$scope}");
        //set value
        $data[$param] = $value;

        $this
            ->getContainer()
            ->get('metadata')
            ->set("scopes", $scope, $data);

        // save
        $this->getContainer()->get('metadata')->save();
    }
}
