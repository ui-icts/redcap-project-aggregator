<div style="width: 70%;">
    <h3>Project Aggregator</h3>
    <br />
    <p>
        {$totalProjectCount} projects tagged "<b>{$note}</b>" were found. Please review before initiating the aggregation process to ensure the expected data will be included. Any projects with import errors will be skipped.
    </p>
    {if $cronInfo.cron_enabled == 'ENABLED'}
        <p>
            This process is set to run automatically once every 24 hours. {if $cronInfo.cron_last_run_start}It was last run at <span class="cron-timestamp">{$cronInfo.cron_last_run_start}</span>.{/if}
        </p>
    {/if}
    <table class="table table-striped table-bordered">
        <thead>
            <tr class="table-primary">
                <th scope="col">PID</th>
                <th scope="col">Project Title</th>
                <th scope="col">Record Count</th>
                <th scope="col">Import Errors</th>
            </tr>
        </thead>
        <tbody id="projects-table">
        {foreach $sourceProjects as $project}
            <tr class="project-row">
                <td class="pid centered">{$project.project_id}</td>
                <td><a href="{$projectUrl}{$project.project_id}">{$project.app_title}</a></td>
                <td class="centered">{$project.record_count}</td>
                <td class="centered"><div class='aggregateProgress'></div></td>
            </tr>
        {/foreach}
        {if $noRecordsCount > 0}
            <tr>
                <td colspan="4" style="text-align: center">
                    +{$noRecordsCount} project(s) with no data
                </td>
            </tr>
        {/if}
        </tbody>
    </table>
    <div style="text-align: right">
    <button id="start" class="btn btn-primary"><span>Import {$totalRecordCount} Records</span></button>
    </div>
</div>
