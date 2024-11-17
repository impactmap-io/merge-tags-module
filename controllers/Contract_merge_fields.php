<?php

defined('BASEPATH') or exit('No direct script access allowed');

class Contract_merge_fields extends AdminController
{
    public function __construct()
    {
        parent::__construct();
        $this->load->model('custom_fields_model');
    }

    public function index()
    {
        show_404();
    }

    public function get_fields()
    {
        if (!has_permission('contracts', '', 'view')) {
            ajax_access_denied();
        }

        $custom_fields = $this->get_contract_custom_fields();
        echo json_encode($custom_fields);
    }

    private function get_contract_custom_fields()
    {
        // Retrieve the custom fields for the Contract entity
        $custom_fields = $this->custom_fields_model->get_by_type('contracts');
        $fields = [];
        foreach ($custom_fields as $field) {
            $fields[] = [
                'name' => $field['name'],
                'key' => '{' . $field['slug'] . '}'
            ];
        }
        return $fields;
    }
}