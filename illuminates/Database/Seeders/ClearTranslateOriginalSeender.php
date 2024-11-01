<?php

namespace Illuminate\Database\Seeders;

class ClearTranslateOriginalSeender
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function run()
    {
        global $wpdb;
        $translateTable  = sprintf('%s%s', $wpdb->prefix, 'transcy_translations');
        if ($wpdb->get_var("SHOW TABLES LIKE '$translateTable'") == $translateTable) {
            $relationships = $wpdb->get_results("SELECT id FROM $translateTable WHERE object_id = translate_id LIMIT 0, 100000");
            if(!empty($relationships)){
                $escaped = [];
                foreach ($relationships as $id) {
                    $escaped[] = $wpdb->prepare('%d', $id->id);
                }
                $wpdb->query("DELETE FROM {$translateTable} WHERE id IN (" . implode(',', $escaped) . ")");
            }
        }
    }
}
