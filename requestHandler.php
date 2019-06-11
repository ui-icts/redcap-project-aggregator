<?php

$module = new \UIOWA\ProjectAggregator\ProjectAggregator();

if ($_REQUEST['type'] == 'single') {
    $destinationPid = $_GET['pid'];
    $sourcePid = file_get_contents('php://input');
    $token = $module->getProjectSetting('delete-token');

    if ($token) {
        $module->deleteExistingRecords($destinationPid, $token);
    }

    echo json_encode($module->aggregateToProject($destinationPid, $sourcePid));
}