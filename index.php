<?php
/** @var \UIOWA\ProjectAggregator\ProjectAggregator $module */

require_once APP_PATH_DOCROOT . 'ProjectGeneral/header.php';

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

// $module->$smarty = new \Smarty();
// $module->$smarty->setTemplateDir(MODULE_DOCROOT . 'templates');
// $module->$smarty->setCompileDir(MODULE_DOCROOT . 'templates_c');
// $module->$smarty->setConfigDir(MODULE_DOCROOT . 'configs');
// $module->$smarty->setCacheDir(MODULE_DOCROOT . 'cache');

// $module->$smarty->assign('sourceProjects', $sourceProjects);
// $module->$smarty->assign('totalProjectCount', $totalProjectCount);
// $module->$smarty->assign('totalRecordCount', $totalRecordCount);
// $module->$smarty->assign('noRecordsCount', $noRecordsCount);
// $module->$smarty->assign('note', $note);
// $module->$smarty->assign('cronInfo', $cronInfo);
// $module->$smarty->assign('projectUrl', $projectUrl);

// $module->$smarty->display('index.tpl');
$serializedProjects = json_encode($sourceProjects);
$serializedCronInfo = json_encode($cronInfo);

?>

<script>
    UIOWA_ProjectAggregator = {
        requestUrl: '<?= $module->getUrl('requestHandler.php') ?>'
   
    };

    const projects = JSON.parse('<?= $serializedProjects ?>')
    const projectUrl = '<?= $projectUrl ?>'
    const note = '<?= $note ?>'
    const cronInfo = JSON.parse('<?= $serializedCronInfo ?>')
    const noRecordsCount = '<?= $noRecordsCount ?>'
    const totalRecordsCount = '<?= $totalRecordsCount ?>'
    console.log(projects)
    function generateTable() {


        let htmlString =  `<div style="width: 70%;">
    <h3>Project Aggregator</h3>
    <br />
    <p>
        ${projects.length} projects tagged "<b>${note}</b>" were found. Please review before initiating the aggregation process to ensure the expected data will be included. Any projects with import errors will be skipped.
    </p>`


    if(cronInfo.cron_enabled === 'ENABLED') {
        htmlString += `<p>
            This process is set to run automatically once every 24 hours. ${cronInfo.cron_last_run_start} it was last run at <span class="cron-timestamp">${cronInfo.cron_last_run_start}</span>.
        </p>`
    }

    htmlString += `<table class="table table-striped table-bordered">
        <thead>
            <tr class="table-primary">
                <th scope="col">PID</th>
                <th scope="col">Project Title</th>
                <th scope="col">Record Count</th>
                <th scope="col">Import Errors</th>
            </tr>
        </thead>
        <tbody id="projects-table">`

        for(let i = 0; i < projects.length; i++) {
            htmlString += `<tr class="project-row">
                <td class="pid centered">${projects[i].project_id}</td>
                <td><a href="${projectUrl}${projects[i].project_id}">${projects[i].app_title}</a></td>
                <td class="centered">${projects[i].record_count}</td>
                <td class="centered"><div class='aggregateProgress'></div></td>
            </tr>
        `
        }

        if(noRecordsCount > 0) {
            htmlString += ` <tr>
                <td colspan="4" style="text-align: center">
                    ${noRecordsCount} project(s) with no data
                </td>
            </tr>`
        }


        htmlString += `</tbody>
    </table>
    <div style="text-align: right">
    <button id="start" class="btn btn-primary"><span>Import ${totalRecordsCount} Records</span></button>
    </div>
</div>`

return htmlString
    }

    function renderTable() {
        $( "#center").append( generateTable());
    }

    renderTable()

</script>

<script src="<?= $module->getUrl("/projectAggregator.js") ?>"></script>
<link href="<?= $module->getUrl("/style.css") ?>" rel="stylesheet">