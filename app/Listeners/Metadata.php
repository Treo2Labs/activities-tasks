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

use Treo\Core\EventManager\Event;
use Treo\Listeners\AbstractListener;

/**
 * Class Metadata
 *
 * @author r.ratsun <r.ratsun@treolabs.com>
 */
class Metadata extends AbstractListener
{

    /**
     * Modify
     *
     * @param Event $event
     */
    public function modify(Event $event): void
    {
        // get data
        $data = $event->getArgument('data');

        // add dashlets
        $this->addDashlets($data);

        // add activities
        $this->addActivities($data);

        // add tasks
        $this->addTasks($data);

        $event->setArgument('data', $data);
    }

    /**
     * @param array $data
     */
    protected function addDashlets(array &$data): void
    {
        // add dashlets
        if (!isset($data['dashlets']['Activities']) && isset($data['hidedDashlets']['Activities'])) {
            $data['dashlets']['Activities'] = $data['hidedDashlets']['Activities'];
        }
        if (!isset($data['dashlets']['Tasks']) && isset($data['hidedDashlets']['Tasks'])) {
            $data['dashlets']['Tasks'] = $data['hidedDashlets']['Tasks'];
        }
        if (!isset($data['dashlets']['Calls']) && isset($data['hidedDashlets']['Calls'])) {
            $data['dashlets']['Calls'] = $data['hidedDashlets']['Calls'];
        }
        if (!isset($data['dashlets']['Emails']) && isset($data['hidedDashlets']['Emails'])) {
            $data['dashlets']['Emails'] = $data['hidedDashlets']['Emails'];
        }
        if (!isset($data['dashlets']['Meetings']) && isset($data['hidedDashlets']['Meetings'])) {
            $data['dashlets']['Meetings'] = $data['hidedDashlets']['Meetings'];
        }
    }

    /**
     * Add activities
     *
     * @param array $data
     */
    protected function addActivities(array &$data): void
    {
        foreach ($data['entityDefs'] as $entity => $row) {
            if (!empty($data['scopes'][$entity]['hasActivities'])) {
                // push to entityList
                if (!in_array($entity, $data['entityDefs']['Meeting']['fields']['parent']['entityList'])) {
                    $data['entityDefs']['Meeting']['fields']['parent']['entityList'][] = $entity;
                }

                // add link to entity
                if (!isset($data['entityDefs'][$entity]['links']['meetings'])) {
                    $data['entityDefs'][$entity]['links']['meetings'] = [
                        "type" => "hasChildren",
                        "entity" => "Meeting",
                        "foreign" => "parent",
                        "layoutRelationshipsDisabled" => true,
                        "audited" => true
                    ];
                }

                // add to client defs
                foreach (['detail', 'detailSmall'] as $panel) {
                    $panelData = [];
                    if (!empty($data['clientDefs'][$entity]['sidePanels'][$panel])) {
                        foreach ($data['clientDefs'][$entity]['sidePanels'][$panel] as $item) {
                            $panelData[$item['name']] = $item;
                        }
                    }

                    $panelData["activities"] = [
                        "name" => "activities",
                        "label" => "Activities",
                        "view" => "activitiestasks:views/record/panels/activities",
                        "aclScope" => "Activities"
                    ];
                    $panelData["history"] = [
                        "name" => "history",
                        "label" => "History",
                        "view" => "activitiestasks:views/record/panels/history",
                        "aclScope" => "Activities"
                    ];

                    $data['clientDefs'][$entity]['sidePanels'][$panel] = array_values($panelData);
                }
            }
        }
    }

    /**
     * Add tasks
     *
     * @param array $data
     */
    protected function addTasks(array &$data): void
    {
        foreach ($data['entityDefs'] as $entity => $row) {
            if (!empty($data['scopes'][$entity]['hasTasks'])) {
                // push to entityList
                if (!in_array($entity, $data['entityDefs']['Task']['fields']['parent']['entityList'])) {
                    $data['entityDefs']['Task']['fields']['parent']['entityList'][] = $entity;
                }
                $name = $entity != 'Task' ? lcfirst($entity) : lcfirst($entity) . 'Child';
                // add field
                $data['entityDefs']['Task']['fields'][lcfirst($entity)] = [
                    "type" => "link",
                    "readOnly" => true
                ];

                // add link
                $data['entityDefs']['Task']['links'][$name] = [
                    "type" => $name,
                    "entity" => $entity,
                ];
                // add link to entity
                $data['entityDefs'][$entity]['links']['tasks'] = [
                    "type" => "hasChildren",
                    "entity" => "Task",
                    "foreign" => "parent",
                    "layoutRelationshipsDisabled" => true
                ];

                // add to client defs
                foreach (['detail', 'detailSmall'] as $panel) {
                    $panelData = [];
                    if (!empty($data['clientDefs'][$entity]['sidePanels'][$panel])) {
                        foreach ($data['clientDefs'][$entity]['sidePanels'][$panel] as $item) {
                            $panelData[$item['name']] = $item;
                        }
                    }

                    $panelData["tasks"] = [
                        "name" => "tasks",
                        "label" => "Tasks",
                        "view" => "activitiestasks:views/record/panels/tasks",
                        "aclScope" => "Task"
                    ];

                    $data['clientDefs'][$entity]['sidePanels'][$panel] = array_values($panelData);
                }
            }
        }
    }
}
