<?php

defined('BASEPATH') or exit('No direct script access allowed');

hooks()->add_action('app_admin_footer', 'contract_merge_fields_add_footer');
hooks()->add_action('app_admin_head', 'contract_merge_fields_add_head');

function contract_merge_fields_add_head()
{
    $CI = &get_instance();
    if ($CI->uri->segment(2) === 'contracts') {
        echo "<!-- Contract Merge Fields Module Head Start -->\n";
        echo '<link href="' . module_dir_url('contract_merge_fields', 'assets/css/merge_fields.css') . '" rel="stylesheet" type="text/css" />';
        echo "\n<!-- Contract Merge Fields Module Head End -->\n";
    }
}

function contract_merge_fields_add_footer()
{
    $CI = &get_instance();
    if ($CI->uri->segment(2) === 'contracts') {
        echo "<!-- Contract Merge Fields Module Footer Start -->\n";
        
        // Add a direct test button to verify our code is running
        echo '<div id="test-merge-fields-button" style="position: fixed; top: 10px; right: 10px; z-index: 9999;">
                <button type="button" class="btn btn-warning">Test Meta Fields Button</button>
              </div>';
        
        // Debug script
        $script = '<script>
            console.log("Contract Merge Fields Module Loaded");
            $(function() {
                console.log("DOM Ready - Contract Merge Fields");
                console.log("Looking for merge fields link:", $("a:contains(\'available_merge_fields\')").length);
                console.log("Current URL segments:", window.location.pathname);
                
                // Test button click handler
                $("#test-merge-fields-button button").on("click", function() {
                    alert("Test button clicked - module is working");
                });
                
                // Try multiple selectors to find where to inject our button
                var $contentTab = $("#tab_content");
                console.log("Content tab found:", $contentTab.length);
                
                var $editorArea = $(".tc-content");
                console.log("Editor area found:", $editorArea.length);
                
                // Direct injection attempt
                var $button = $(`
                    <div class="tw-flex tw-justify-end tw-mb-4">
                        <button type="button" class="btn btn-primary" id="show-merge-fields">
                            <i class="fa fa-code"></i> Meta Fields
                        </button>
                    </div>
                `);
                
                // Try to inject before the editor
                if($editorArea.length) {
                    $editorArea.first().before($button);
                    console.log("Button injected before editor");
                }
            });
        </script>';
        
        echo '<script src="' . module_dir_url('contract_merge_fields', 'assets/js/merge_fields.js') . '"></script>';
        echo $script;
        echo "\n<!-- Contract Merge Fields Module Footer End -->\n";
    }
}