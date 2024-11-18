<?php
defined('BASEPATH') or exit('No direct script access allowed');

/*
* Module Name: Contract Merge Fields
* Description: Adds a Meta button to easily access merge fields in contracts
* Version: 1.1.0
* Requires at least: 3.2.1
*/

hooks()->add_action('after_contract_view_as_client_link', 'add_meta_fields_button');
hooks()->add_action('app_admin_footer', 'add_meta_fields_panel');

function add_meta_fields_button($contract)
{
    ?>
    <li>
        <a href="#" onclick="showMetaFieldsPanel(); return false;">
            <i class="fa fa-code"></i> <?php echo _l('Meta Fields'); ?>
        </a>
    </li>
    <?php
}

function add_meta_fields_panel()
{
    $CI = &get_instance();
    if ($CI->uri->segment(2) !== 'contracts') {
        return;
    }
    ?>
    <style>
        #meta-fields-overlay {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: rgba(0, 0, 0, 0.3);
            z-index: 99998;
        }

        #meta-fields-panel {
            visibility: hidden;
            position: fixed;
            top: 0;
            right: -400px;
            width: 400px;
            height: 100vh;
            background: #fff;
            box-shadow: -2px 0 5px rgba(0,0,0,0.1);
            z-index: 99999;
            transition: right 0.3s ease;
        }
        
        #meta-fields-panel.show {
            visibility: visible;
            right: 0;
        }

        .meta-fields-header {
            padding: 15px;
            background: #2d2d2d;
            color: white;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .meta-fields-content {
            padding: 15px;
            height: calc(100vh - 60px);
            overflow-y: auto;
        }

        .meta-field-item {
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 10px;
            border: 1px solid #ddd;
            margin-bottom: 5px;
            border-radius: 4px;
            position: relative;
            transition: all 0.2s ease;
        }

        .meta-field-item:hover {
            background: #f8f9fa;
            border-color: #0084ff;
        }

        .field-info {
            flex: 1;
            padding-right: 10px;
        }

        .field-name {
            font-weight: bold;
            margin-bottom: 5px;
        }

        .field-key {
            font-family: monospace;
            background: #f8f9fa;
            padding: 2px 6px;
            border-radius: 3px;
            font-size: 12px;
            color: #666;
            user-select: all;
        }

        .field-actions {
            display: none;
            margin-left: 10px;
            position: relative;
        }

        .meta-field-item:hover .field-actions {
            display: block;
        }
        
        .settings-btn {
            color: #666;
            width: 28px;
            height: 28px;
            padding: 0;
            display: flex;
            align-items: center;
            justify-content: center;
            border: 1px solid #ddd;
            border-radius: 4px;
            background: white;
            cursor: pointer;
            opacity: 0;
            transition: all 0.2s ease;
        }

        .meta-field-item:hover .settings-btn {
            opacity: 1;
        }

        .settings-btn:hover {
            color: #0084ff;
            border-color: #0084ff;
            background: #f8f9fa;
        }

        .action-menu {
            position: absolute;
            top: calc(100% + 5px);
            right: 0;
            min-width: 160px;
            background: white;
            border-radius: 6px;
            box-shadow: 0 4px 12px rgba(0,0,0,0.1);
            opacity: 0;
            visibility: hidden;
            transform: translateY(-10px);
            transition: all 0.2s ease;
            z-index: 100000;
        }

        .action-menu.show {
            opacity: 1;
            visibility: visible;
            transform: translateY(0);
        }

        .action-menu ul {
            list-style: none;
            padding: 6px 0;
            margin: 0;
        }

        .action-menu li {
            padding: 8px 16px;
            cursor: pointer;
            display: flex;
            align-items: center;
            gap: 10px;
            color: #666;
            transition: all 0.2s ease;
        }

        .action-menu li:hover {
            background: #f8f9fa;
            color: #0084ff;
        }

        .action-menu li i {
            font-size: 14px;
            width: 16px;
            text-align: center;
        }

        .loading-spinner {
            display: flex;
            align-items: center;
            justify-content: center;
            min-height: 200px;
        }

        .loading-spinner i {
            font-size: 24px;
            color: #666;
        }
    </style>

    <div id="meta-fields-overlay"></div>
    <div id="meta-fields-panel">
        <div class="meta-fields-header">
            <h4 class="tw-mb-0">Available Merge Fields</h4>
            <button class="btn btn-link text-white" onclick="hideMetaFieldsPanel()">
                <i class="fa fa-times"></i>
            </button>
        </div>
        <div class="meta-fields-content">
            <div class="form-group">
                <input type="text" class="form-control" id="meta-fields-search" 
                       placeholder="Search fields...">
            </div>
            <div id="meta-fields-list">
                <div class="loading-spinner">
                    <i class="fa fa-spinner fa-spin"></i>
                </div>
            </div>
        </div>
    </div>

    <script>
    function showMetaFieldsPanel() {
        $('#meta-fields-overlay').fadeIn(200);
        $('#meta-fields-panel').css('visibility', 'visible');
        $('#meta-fields-panel').addClass('show');
        $('#meta-fields-list').html('<div class="loading-spinner"><i class="fa fa-spinner fa-spin"></i></div>');

        setTimeout(loadFields, 300);
    }

    function loadFields() {
        try {
            var fields = [];
            $('.avilable_merge_fields .list-group-item').each(function() {
                fields.push({
                    name: $(this).find('b').text().trim(),
                    key: $(this).find('a').text().trim()
                });
            });

            var $list = $('#meta-fields-list');
            $list.empty();
            
            fields.forEach(function(field) {
                if (!field.name || !field.key) return;
                
                var $item = $(`
                    <div class="meta-field-item">
                        <div class="field-info">
                            <div class="field-name">${field.name}</div>
                            <div class="field-key">${field.key}</div>
                        </div>
                        <div class="field-actions">
                            <button type="button" class="btn settings-btn" data-field="${field.key}">
                                <i class="fa fa-cog"></i>
                            </button>
                            <div class="action-menu">
                                <ul>
                                    <li onclick="handleAction('insert', '${field.key}', this)">
                                        <i class="fa fa-arrow-right"></i>
                                        Insert
                                    </li>
                                    <li onclick="handleAction('copy', '${field.key}', this)">
                                        <i class="fa fa-copy"></i>
                                        Copy
                                    </li>
                                </ul>
                            </div>
                        </div>
                    </div>
                `);
                $list.append($item);
            });

            initializeActions();
        } catch (error) {
            $('#meta-fields-list').html('<div class="alert alert-danger">Error loading fields</div>');
        }
    }

    function initializeActions() {
        // Remove any existing handlers
        $(document).off('click', '.settings-btn');
        
        // Handle cog button clicks
        $(document).on('click', '.settings-btn', function(e) {
            e.preventDefault();
            e.stopPropagation();
            
            // Close any open menus first
            $('.action-menu.show').removeClass('show');
            
            // Show this button's menu
            $(this).siblings('.action-menu').addClass('show');
        });
        
        // Close menu when clicking outside
        $(document).on('click', function(e) {
            if (!$(e.target).closest('.field-actions').length) {
                $('.action-menu.show').removeClass('show');
            }
        });
    }

    function handleAction(action, fieldKey, element) {
        // Close menu immediately
        $(element).closest('.action-menu').removeClass('show');
        
        // Get the button for feedback
        const $button = $(element).closest('.field-actions').find('.settings-btn');
        
        try {
            if (action === 'insert') {
                if (insertToEditor(fieldKey)) {
                    showSuccess($button);
                } else {
                    showError($button);
                }
            } else if (action === 'copy') {
                copyField(fieldKey, $button);
            }
        } catch (error) {
            showError($button);
        }
    }

    function showSuccess($button) {
        const $icon = $button.find('i');
        $icon.removeClass('fa-cog').addClass('fa-check');
        setTimeout(() => {
            $icon.removeClass('fa-check').addClass('fa-cog');
        }, 1000);
    }

    function showError($button) {
        const $icon = $button.find('i');
        $icon.removeClass('fa-cog').addClass('fa-times');
        setTimeout(() => {
            $icon.removeClass('fa-times').addClass('fa-cog');
        }, 1000);
    }

    function insertToEditor(fieldKey) {
        if (!tinymce?.activeEditor) {
            return false;
        }

        try {
            tinymce.activeEditor.execCommand('mceInsertContent', false, fieldKey);
            alert_float('success', 'Field inserted into editor');
            hideMetaFieldsPanel();
            return true;
        } catch (error) {
            alert_float('warning', 'Editor not currently active');
            return false;
        }
    }

    function copyField(text, $button) {
        if (!text) {
            showError($button);
            return;
        }

        text = text.trim();
        
        if (navigator.clipboard && window.isSecureContext) {
            navigator.clipboard.writeText(text)
                .then(() => {
                    showSuccess($button);
                    alert_float('success', 'Field copied to clipboard');
                })
                .catch(() => {
                    fallbackCopyToClipboard(text, $button);
                });
        } else {
            fallbackCopyToClipboard(text, $button);
        }
    }

    function fallbackCopyToClipboard(text, $button) {
        try {
            const el = document.createElement('textarea');
            el.value = text;
            el.setAttribute('readonly', '');
            el.style.position = 'absolute';
            el.style.left = '-9999px';
            document.body.appendChild(el);
            el.select();
            
            const success = document.execCommand('copy');
            document.body.removeChild(el);
            
            if (success) {
                showSuccess($button);
                alert_float('success', 'Field copied to clipboard');
            } else {
                throw new Error('Copy command failed');
            }
        } catch (error) {
            showError($button);
            alert_float('danger', 'Failed to copy field');
        }
    }

    function hideMetaFieldsPanel() {
        $('#meta-fields-panel').removeClass('show');
        setTimeout(() => {
            $('#meta-fields-panel').css('visibility', 'hidden');
            $('#meta-fields-overlay').fadeOut(200);
        }, 300);
    }

    $(document).on('keyup', '#meta-fields-search', function() {
        var value = $(this).val().toLowerCase();
        $('.meta-field-item').filter(function() {
            $(this).toggle($(this).text().toLowerCase().indexOf(value) > -1);
        });
    });

    $('#meta-fields-overlay').on('click', hideMetaFieldsPanel);
    </script>
    <?php
}

function contract_merge_fields_init()
{
    $CI = &get_instance();
    
    log_message('debug', 'Contract Merge Fields init starting');
    
    // Run migrations
    if (is_dir(module_dir_path('contract_merge_fields', 'migrations'))) {
        log_message('debug', 'Migrations directory found');
        
        $CI->load->config('migration');
        $CI->load->library('migration');
        
        $migration_path = module_dir_path('contract_merge_fields', 'migrations');
        log_message('debug', 'Migration path: ' . $migration_path);
        
        $CI->config->set_item('migration_path', $migration_path);
            
        $result = $CI->migration->latest();
        log_message('debug', 'Migration result: ' . ($result ? 'success' : 'failed'));
        
        if ($result === FALSE) {
            log_message('error', 'Migration Error: ' . $CI->migration->error_string());
        }
    } else {
        log_message('debug', 'Migrations directory not found');
    }
}

function contract_merge_fields_activation()
{
    $CI = &get_instance();
    
    log_message('debug', 'Contract Merge Fields activation starting');
    
    // Run migrations on activation
    if (is_dir(module_dir_path('contract_merge_fields', 'migrations'))) {
        log_message('debug', 'Activation - Migrations directory found');
        
        $CI->load->config('migration');
        $CI->load->library('migration');
        
        $migration_path = module_dir_path('contract_merge_fields', 'migrations');
        log_message('debug', 'Activation - Migration path: ' . $migration_path);
        
        $CI->config->set_item('migration_path', $migration_path);
            
        $result = $CI->migration->latest();
        log_message('debug', 'Activation - Migration result: ' . ($result ? 'success' : 'failed'));
        
        if ($result === FALSE) {
            log_message('error', 'Activation - Migration Error: ' . $CI->migration->error_string());
        }
    } else {
        log_message('debug', 'Activation - Migrations directory not found'};

    }
    
    return true;
}

function contract_merge_fields_uninstall()
{
    $CI = &get_instance();
    
    log_message('debug', 'Contract Merge Fields uninstall starting');
    
    // Run down migrations
    if (is_dir(module_dir_path('contract_merge_fields', 'migrations'))) {
        log_message('debug', 'Uninstall - Migrations directory found');
        
        $CI->load->config('migration');
        $CI->load->library('migration');
        
        $CI->config->set_item('migration_path', 
            module_dir_path('contract_merge_fields', 'migrations'));
            
        // This will run all down() methods
        $result = $CI->migration->version(0);
        log_message('debug', 'Uninstall - Migration rollback result: ' . ($result ? 'success' : 'failed'));
        
        if ($result === FALSE) {
            log_message('error', 'Uninstall - Migration Error: ' . $CI->migration->error_string());
        }
    } else {
        log_message('debug', 'Uninstall - Migrations directory not found');
    }
    
    return true;
}