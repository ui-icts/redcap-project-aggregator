<?php
/** @var \UIOWA\ProjectAggregator\ProjectAggregator $module */

require_once APP_PATH_DOCROOT . 'ProjectGeneral/header.php';

$note = $module->getProjectSetting('aggregate-note');

$sourceProjects = [];

if (isset($_GET['pid']) && filter_var($_GET['pid'], FILTER_VALIDATE_INT)) {
    
    $sanitizedPid = htmlentities(strip_tags($_GET['pid'], ENT_QUOTES));
    $sourceProjects = $module->getSourceProjects($sanitizedPid, true);

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

        
    $serializedProjects = htmlentities(json_encode($sourceProjects,true),ENT_QUOTES, 'UTF-8');
    $serializedCronInfo = htmlentities(json_encode($cronInfo),ENT_QUOTES, 'UTF-8'); 

    ?>

    <script>

        let UIOWA_ProjectAggregator = {
            requestUrl: '<?= $module->getUrl('requestHandler.php') ?>',
            projects: JSON.parse('<?= str_replace("&quot;", '"', $serializedProjects) ?>'),
            projectUrl: '<?= $projectUrl ?>',
            note: '<?= $note ?>',
            cronInfo: JSON.parse('<?= str_replace("&quot;", '"', $serializedCronInfo) ?>'),
            noRecordsCount: '<?= $noRecordsCount ?>',
            totalRecordsCount: '<?= $totalRecordsCount ?>',
            htmlString: ``
        };

        function generateTable() {


            UIOWA_ProjectAggregator.htmlString =  `<div style="width: 70%;">
                <h3>Project Aggregator</h3>
                <br />
                <p>
                    ${UIOWA_ProjectAggregator.projects.length} projects tagged "<b>${UIOWA_ProjectAggregator.note}</b>" were found. Please review before initiating the aggregation process to ensure the expected data will be included. Any projects with import errors will be skipped.
                </p>`


            if(UIOWA_ProjectAggregator.cronInfo.cron_enabled === 'ENABLED') {
                UIOWA_ProjectAggregator.htmlString += `<p>
                    This process is set to run automatically once every 24 hours. ${UIOWA_ProjectAggregator.cronInfo.cron_last_run_start} it was last run at <span class="cron-timestamp">${UIOWA_ProjectAggregator.cronInfo.cron_last_run_start}</span>.
                </p>`
            }

            UIOWA_ProjectAggregator.htmlString += `<table class="table table-striped table-bordered">
                <thead>
                    <tr class="table-primary">
                        <th scope="col">PID</th>
                        <th scope="col">Project Title</th>
                        <th scope="col">Record Count</th>
                        <th scope="col">Import Errors</th>
                    </tr>
                </thead>
                <tbody id="projects-table">`

            for(let i = 0; i < UIOWA_ProjectAggregator.projects.length; i++) {
                UIOWA_ProjectAggregator.htmlString += `<tr class="project-row">
                    <td class="pid centered">${UIOWA_ProjectAggregator.projects[i].project_id}</td>
                    <td><a href="${UIOWA_ProjectAggregator.projectUrl}${UIOWA_ProjectAggregator.projects[i].project_id}">${UIOWA_ProjectAggregator.projects[i].app_title}</a></td>
                    <td class="centered">${UIOWA_ProjectAggregator.projects[i].record_count}</td>
                    <td class="centered"><div class='aggregateProgress'></div></td>
                </tr>
            `
            }

            if(UIOWA_ProjectAggregator.noRecordsCount > 0) {
                UIOWA_ProjectAggregator.htmlString += ` <tr>
                    <td colspan="4" style="text-align: center">
                        ${UIOWA_ProjectAggregator.noRecordsCount} project(s) with no data
                    </td>
                </tr>`
            }


            UIOWA_ProjectAggregator.htmlString += `</tbody>
            </table>
                <div style="text-align: right">
                    <button id="start" class="btn btn-primary"><span>Import ${UIOWA_ProjectAggregator.totalRecordsCount} Records</span></button>
                </div>
            </div>`

            return UIOWA_ProjectAggregator.htmlString

        }

        function renderTable() {
            $( "#center").append( generateTable());
        }

        renderTable()

    </script>

    <script src="<?= $module->getUrl("/projectAggregator.js") ?>"></script>
    <link href="<?= $module->getUrl("/style.css") ?>" rel="stylesheet">
    <?php
} 

