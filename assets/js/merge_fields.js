$(function() {
    // Add the slide panel HTML to the body
    if (!$('#merge-fields-panel').length) {
        $('body').append(`
            <div id="merge-fields-panel" class="slide-panel">
                <div class="panel-header">
                    <h4>Available Merge Fields</h4>
                    <button class="close-panel"><i class="fa fa-times"></i></button>
                </div>
                <div class="panel-content">
                    <div class="search-box">
                        <input type="text" id="merge-field-search" class="form-control" placeholder="Search fields...">
                    </div>
                    <div class="merge-fields-list"></div>
                </div>
            </div>
        `);
    }

    // Load custom fields from the server
    function loadMergeFields() {
        const $list = $('.merge-fields-list');
        $list.empty();

        $.ajax({
            url: admin_url + 'contract_merge_fields/get_fields',
            success: function(response) {
                const fields = JSON.parse(response);
                fields.forEach(field => {
                    $list.append(`
                        <div class="merge-field-item" data-field="${field.key}">
                            <div class="field-name">${field.name}</div>
                            <div class="field-key">${field.key}</div>
                        </div>
                    `);
                });
            },
            error: function(xhr, status, error) {
                console.error('Error fetching custom fields:', error);
            }
        });
    }

    // Show panel when button is clicked
    $(document).on('click', '#show-merge-fields', function() {
        $('#merge-fields-panel').addClass('show');
        loadMergeFields();
    });

    // Close panel
    $('.close-panel').click(function() {
        $('#merge-fields-panel').removeClass('show');
    });

    // Search functionality
    $(document).on('input', '#merge-field-search', function() {
        const searchTerm = $(this).val().toLowerCase();
        $('.merge-field-item').each(function() {
            const text = $(this).text().toLowerCase();
            $(this).toggle(text.includes(searchTerm));
        });
    });

    // Copy field on click
    $(document).on('click', '.merge-field-item', function() {
        const field = $(this).data('field');
        const editor = tinymce.activeEditor;
        if (editor) {
            editor.execCommand('mceInsertContent', false, field);
            $('#merge-fields-panel').removeClass('show');
            alert_float('success', 'Merge field inserted');
        } else {
            navigator.clipboard.writeText(field);
            alert_float('success', 'Merge field copied to clipboard');
        }
    });
});