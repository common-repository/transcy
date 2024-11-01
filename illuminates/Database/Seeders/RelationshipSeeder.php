<?php

namespace Illuminate\Database\Seeders;

use Illuminate\Utils\QueryTranslations;

class RelationshipSeeder
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function run()
    {
        global $wpdb;
        //Query builder
        $query              = QueryTranslations::getInstance();

        $relationshipTable  = sprintf('%s%s', $wpdb->prefix, 'transcy_relationships');
        if ($wpdb->get_var("SHOW TABLES LIKE '$relationshipTable'") == $relationshipTable) {
            $relationships = $wpdb->get_results("SELECT * FROM $relationshipTable LIMIT 0, 100000");
            if (!empty($relationships)) {
                foreach ($relationships as $key => $item) {
                    //Get item tranlsate
                    if ($item->id != $item->relationship_id && $item->lang != getDefaultLang() && in_array($item->lang, getAdvancedLang())) {
                        //Post
                        if (in_array($item->prefix, ['post', 'nav_menu_item'])) {
                            $type      = 'post';
                            $postType  = $wpdb->get_var($wpdb->prepare("SELECT post_type from $wpdb->posts where ID = %d", $item->id));
                        }

                        //Term
                        if (in_array($item->prefix, ['term', 'nav_menu'])) {
                            $type      = 'term';
                            $postType  = $wpdb->get_var($wpdb->prepare("SELECT taxonomy from $wpdb->term_taxonomy where term_id = %d", $item->id));
                        }
                        $query->add($item->id, $item->relationship_id, $type, $postType, $item->lang);
                    }
                }
            }
        }
    }
}
