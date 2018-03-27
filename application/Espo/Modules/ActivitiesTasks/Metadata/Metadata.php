<?php
declare(strict_types = 1);

namespace Espo\Modules\ActivitiesTasks\Metadata;

use Espo\Modules\TreoCore\Metadata\AbstractMetadata;

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
        // add activities
        $data = $this->addActivities($data);

        // add tasks
        $data = $this->addTasks($data);

        // delete activities
        $data = $this->deleteActivities($data);

        // delete tasks
        $data = $this->deleteTasks($data);

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
                $data['entityDefs'][$entity]['links']['meetings'] = [
                    "type"                        => "hasChildren",
                    "entity"                      => "Meeting",
                    "foreign"                     => "parent",
                    "layoutRelationshipsDisabled" => true,
                    "audited"                     => true
                ];

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
                    $panelData["history"]    = [
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

    /**
     * Delete activities
     *
     * @param array $data
     *
     * @return array
     */
    protected function deleteActivities(array $data): array
    {
        foreach ($data['entityDefs'] as $entity => $row) {
            if (isset($data['scopes'][$entity]['hasActivities']) && empty($data['scopes'][$entity]['hasActivities'])) {
                // remove from entityList
                $entityList = [];
                foreach ($data['entityDefs']['Meeting']['fields']['parent']['entityList'] as $item) {
                    if ($entity != $item) {
                        $entityList[] = $item;
                    }
                }
                $data['entityDefs']['Meeting']['fields']['parent']['entityList'] = $entityList;

                // delete from side panel
                foreach (['detail', 'detailSmall'] as $panel) {
                    if (!empty($data['clientDefs'][$entity]['sidePanels'][$panel])) {
                        $sidePanelsData = [];
                        foreach ($data['clientDefs'][$entity]['sidePanels'][$panel] as $k => $item) {
                            if (!in_array($item['name'], ['activities', 'history'])) {
                                $sidePanelsData[] = $item;
                            }
                        }
                        $data['clientDefs'][$entity]['sidePanels'][$panel] = $sidePanelsData;
                    }
                }

                // delete link
                if (isset($data['entityDefs'][$entity]['links']['meetings'])) {
                    unset($data['entityDefs'][$entity]['links']['meetings']);
                }
            }
        }

        return $data;
    }

    /**
     * Delete tasks
     *
     * @param array $data
     *
     * @return array
     */
    protected function deleteTasks(array $data): array
    {
        foreach ($data['entityDefs'] as $entity => $row) {
            if (isset($data['scopes'][$entity]['hasTasks']) && $data['scopes'][$entity]['hasTasks'] === false) {
                // remove from entityList
                $entityList = [];
                foreach ($data['entityDefs']['Task']['fields']['parent']['entityList'] as $item) {
                    if ($entity != $item) {
                        $entityList[] = $item;
                    }
                }
                $data['entityDefs']['Task']['fields']['parent']['entityList'] = $entityList;

                // remove from client defs
                if (isset($data['clientDefs'][$entity]['sidePanels'])) {
                    foreach ($data['clientDefs'][$entity]['sidePanels'] as $panel => $rows) {
                        $sidePanelsData = [];
                        foreach ($rows as $k => $row) {
                            if ($row['name'] != 'tasks') {
                                $sidePanelsData[] = $row;
                            }
                        }
                        $data['clientDefs'][$entity]['sidePanels'][$panel] = $sidePanelsData;
                    }
                }
            }
        }

        return $data;
    }
}
