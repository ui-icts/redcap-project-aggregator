$(document).ready(function() {

    $('#start').click(function () {
        $(this)
            .addClass('disabled')
            .prop('disabled', 'disabled')
        .find('span')
            .addClass('fas fa-spinner fa-spin')
            .html('');

        UIOWA_ProjectAggregator.aggregateToProject();
    
    });
});

UIOWA_ProjectAggregator.aggregateToProject = function() {
    $.ajax({
        method: 'POST',
        url: UIOWA_ProjectAggregator.requestUrl,
        data: pid,
        success: function (data) {
            $.each(JSON.parse(data), function (key, result) {
                if (key == 'saved') {
                    if (result.errors.length > 0) {
                        $('#start')
                            .addClass('btn-danger')
                            .find('span')
                            .removeClass('fas fa-spinner fa-spin btn-primary')
                            .html('Import Failed');

                        alert('Failed to complete import of aggregate data. ERROR:' + result.errors);
                    }
                    else {
                        const recordCount = Object.keys(result.ids).length;

                        $('#start')
                            .addClass('btn-success')
                            .find('span')
                            .removeClass('fas fa-spinner fa-spin btn-primary')
                            .html('Imported ' + recordCount + ' Records');
                    }
                }
                else {
                    const $row = $('.pid:contains(' + key + ')').parent();
                    const spinner = 'fas fa-spinner fa-spin';
                    const progressDiv = $row.find('.aggregateProgress');

                    if (result.errors.length > 0) {
                        progressDiv.html(result.errors);
                    }
                    else {
                        progressDiv.html('N/A');
                    }
                }
            })
        }
    });
};