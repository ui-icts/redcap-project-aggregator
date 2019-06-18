<?php
/** @var \UIOWA\ProjectAggregator\ProjectAggregator $module */

require_once APP_PATH_DOCROOT . 'ProjectGeneral/header.php';
require_once APP_PATH_DOCROOT . 'ProjectGeneral/form_renderer_functions.php';

$note = $module->getProjectSetting('aggregate-note');
$sourceProjects = $module->getSourceProjects($_GET['pid'], true);
$totalProjectCount = count($sourceProjects);
$totalRecordCount = 0;
$noRecordsCount = 0;

foreach ($sourceProjects as $index => $project) {
    if ($project['record_count'] == 0) {
        unset($sourceProjects[$index]);
        $noRecordsCount++;
    }
    else {
        $totalRecordCount += $project['record_count'];
    }
}

$sql = "
    SELECT c.cron_enabled, c.cron_frequency, c.cron_last_run_start
    FROM redcap_crons c
    LEFT JOIN redcap_external_modules m ON m.external_module_id = c.external_module_id
    WHERE m.directory_prefix = 'project_aggregator'
";

$cronInfo = db_fetch_assoc(db_query($sql));

$projectUrl =
    (isset($_SERVER['HTTPS']) ? 'https://' : 'http://') .
    SERVER_NAME .
    APP_PATH_WEBROOT .
    'ProjectSetup/index.php?pid=';

$module->$smarty = new \Smarty();
$module->$smarty->setTemplateDir(MODULE_DOCROOT . 'templates');
$module->$smarty->setCompileDir(MODULE_DOCROOT . 'templates_c');
$module->$smarty->setConfigDir(MODULE_DOCROOT . 'configs');
$module->$smarty->setCacheDir(MODULE_DOCROOT . 'cache');

$module->$smarty->assign('sourceProjects', $sourceProjects);
$module->$smarty->assign('totalProjectCount', $totalProjectCount);
$module->$smarty->assign('totalRecordCount', $totalRecordCount);
$module->$smarty->assign('noRecordsCount', $noRecordsCount);
$module->$smarty->assign('note', $note);
$module->$smarty->assign('cronInfo', $cronInfo);
$module->$smarty->assign('projectUrl', $projectUrl);

$module->$smarty->display('index.tpl');

?>

<script>
    UIOWA_ProjectAggregator = {
        requestUrl: '<?= $module->getUrl('requestHandler.php') ?>'
    };
</script>

<script src="<?= $module->getUrl("/projectAggregator.js") ?>"></script>
<link href="<?= $module->getUrl("/style.css") ?>" rel="stylesheet">