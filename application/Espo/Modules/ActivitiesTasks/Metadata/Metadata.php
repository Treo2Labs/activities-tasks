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

namespace Espo\Modules\ActivitiesTasks\Metadata;

use Treo\Metadata\AbstractMetadata;

/**
 * Metadata
 *
 * @author r.ratsun <r.ratsun@zinitsolutions.com>
 */
class Metadata extends AbstractMetadata
{

    /**
     * Modify
     *
     * @param array $data
     *
     * @return array
     */
    public function modify(array $data): array
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

        // add activities
        $data = $this->addActivities($data);

        // add tasks
        $data = $this->addTasks($data);

        return $data;
    }

    /**
     * Add activities
     *
     * @param array $data
     *
     * @return array
     */
    protected function addActivities(array $data): array
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
                        "name"     => "activities",
                        "label"    => "Activities",
                        "view"     => "crm:views/record/panels/activities",
                        "aclScope" => "Activities"
                    ];
                    $panelData["history"] = [
                        "name"     => "history",
                        "label"    => "History",
                        "view"     => "crm:views/record/panels/history",
                        "aclScope" => "Activities"
                    ];

                    $data['clientDefs'][$entity]['sidePanels'][$panel] = array_values($panelData);
                }
            }
        }

        return $data;
    }

    /**
     * Add tasks
     *
     * @param array $data
     *
     * @return array
     */
    protected function addTasks(array $data): array
    {
        foreach ($data['entityDefs'] as $entity => $row) {
            if (!empty($data['scopes'][$entity]['hasTasks'])) {
                // push to entityList
                if (!in_array($entity, $data['entityDefs']['Task']['fields']['parent']['entityList'])) {
                    $data['entityDefs']['Task']['fields']['parent']['entityList'][] = $entity;
                }

                // add field
                $data['entityDefs']['Task']['fields'][lcfirst($entity)] = [
                    "type"     => "link",
                    "readOnly" => true
                ];

                // add link
                $data['entityDefs']['Task']['links'][lcfirst($entity)] = [
                    "type"   => "belongsTo",
                    "entity" => $entity
                ];

                // add link to entity
                $data['entityDefs'][$entity]['links']['tasks'] = [
                    "type"                        => "hasChildren",
                    "entity"                      => "Task",
                    "foreign"                     => "parent",
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
                        "name"     => "tasks",
                        "label"    => "Tasks",
                        "view"     => "crm:views/record/panels/tasks",
                        "aclScope" => "Task"
                    ];

                    $data['clientDefs'][$entity]['sidePanels'][$panel] = array_values($panelData);
                }
            }
        }

        return $data;
    }
}
