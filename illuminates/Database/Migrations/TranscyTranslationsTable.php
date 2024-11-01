<?php

namespace Illuminate\Database\Migrations;

class TranscyTranslationsTable
{
    protected $tableName = 'transcy_translations';

    public function __construct()
    {
        global $wpdb;
        $this->tableName = sprintf('%s%s', $wpdb->prefix, $this->tableName);
    }

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        global $wpdb;
        if ($wpdb->get_var("SHOW TABLES LIKE '$this->tableName'") != $this->tableName) {
            $sql = "CREATE TABLE IF NOT EXISTS $this->tableName(
                        `id` bigint unsigned NOT NULL AUTO_INCREMENT,
                        `object_id` bigint NOT NULL,
                        `translate_id` bigint NOT NULL,
                        `type` varchar(45) NOT NULL,
                        `post_type` varchar(45) NOT NULL,
                        `lang` varchar(10) NOT NULL,
                        `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
                        `updated_at` DATETIME ON UPDATE CURRENT_TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                        PRIMARY KEY (`id`),
                        UNIQUE KEY `id_UNIQUE` (`id`),
                        UNIQUE KEY `UC_TRANSLATE` (`object_id`,`post_type`,`lang`)
                    )";

            if (!function_exists('dbDelta')) {
                require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
            }

            dbDelta($sql);
        }
    }
}
