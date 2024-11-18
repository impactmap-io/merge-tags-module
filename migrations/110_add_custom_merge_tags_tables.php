<?php
defined('BASEPATH') or exit('No direct script access allowed');

class Migration_Add_custom_merge_tags_tables_110 extends App_module_migration
{
    public function up()
    {
        log_message('debug', 'Starting custom merge tags migration');
        
        try {
            // Existing migration code...
            log_message('debug', 'Migration completed successfully');
        } catch (Exception $e) {
            log_message('error', 'Migration failed: ' . $e->getMessage());
            throw $e;
        }

        // Create main mapping table for custom tags
        $this->db->query("
            CREATE TABLE IF NOT EXISTS `tblcustom_merge_tags` (
                `id` int(11) NOT NULL AUTO_INCREMENT,
                `custom_tag` varchar(255) NOT NULL,         -- Our custom tag e.g., [contract_reference]
                `perfex_tag` varchar(255) NOT NULL,         -- Original Perfex tag e.g., {contract_id}
                `description` text,                         -- Human readable description
                `category_id` int(11),                      -- Optional grouping
                `is_active` tinyint(1) DEFAULT 1,           -- Enable/disable without deletion
                `display_order` int(11) DEFAULT 0,          -- For custom ordering in UI
                `created_at` datetime DEFAULT CURRENT_TIMESTAMP,
                PRIMARY KEY (`id`),
                UNIQUE KEY `custom_tag` (`custom_tag`)      -- Prevent duplicate custom tags
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
        ");

        // Create categories table for organizing tags
        $this->db->query("
            CREATE TABLE IF NOT EXISTS `tblcustom_merge_tag_categories` (
                `id` int(11) NOT NULL AUTO_INCREMENT,
                `name` varchar(150) NOT NULL,               -- Category name
                `description` text,                         -- Optional description
                `display_order` int(11) DEFAULT 0,          -- For custom ordering in UI
                `created_at` datetime DEFAULT CURRENT_TIMESTAMP,
                PRIMARY KEY (`id`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
        ");

        // Add foreign key relationship
        $this->db->query("
            ALTER TABLE `tblcustom_merge_tags`
            ADD CONSTRAINT `fk_tag_category` 
            FOREIGN KEY (`category_id`) 
            REFERENCES `tblcustom_merge_tag_categories` (`id`) 
            ON DELETE SET NULL;
        ");
    }

    public function down()
    {
        // Remove foreign key constraint first
        $this->db->query("
            ALTER TABLE `tblcustom_merge_tags`
            DROP FOREIGN KEY IF EXISTS `fk_tag_category`;
        ");

        // Drop the tables in reverse order
        $this->db->query("DROP TABLE IF EXISTS `tblcustom_merge_tags`");
        $this->db->query("DROP TABLE IF EXISTS `tblcustom_merge_tag_categories`");
    }
}