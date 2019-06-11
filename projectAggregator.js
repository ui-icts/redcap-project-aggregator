$(document).ready(function() {

    $('#start').click(function () {
        $(this)
            .addClass('disabled')
            .prop('disabled', 'disabled')
        .find('span')
            .addClass('fas fa-spinner fa-spin')
            .html('');

        $('.project-row').each(function() {
            UIOWA_ProjectAggregator.aggregateToProject($(this));
        });
    });
});

UIOWA_ProjectAggregator.completedProjectCount = 0;
UIOWA_ProjectAggregator.importedRecordCount = 0;

UIOWA_ProjectAggregator.aggregateToProject = function($row) {
    var pid = $row.find('.pid').html();
    var spinner = 'fas fa-spinner fa-spin';
    var progressDiv = $row.find('.aggregateProgress');
    var self = this;

    progressDiv
        .addClass(spinner)
        .html('');

    $.ajax({
        method: 'POST',
        url: self.requestUrl + '&type=single',
        data: pid,
        success: function (data) {
            data = JSON.parse(data);
            progressDiv.removeClass(spinner);

            if (data.errors.length > 0) {
                progressDiv.html(data.errors);
            }
            else {
                progressDiv.html('Done!');

                self.importedRecordCount += Object.keys(data.ids).length;
            }

            self.completedProjectCount++;

            if (self.completedProjectCount == $('.project-row').length) {
                $('#start')
                    .addClass('btn-success')
                .find('span')
                    .removeClass('fas fa-spinner fa-spin btn-primary')
                    .html('Imported ' + self.importedRecordCount + ' Records');
            }
        }
    });
};