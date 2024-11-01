<?php

namespace TranscyApp\Repositories;

use Illuminate\BaseClass\Repository;
use Illuminate\Utils\QueryTranslations;
use Illuminate\Configuration;

class BaseRepository extends Repository
{
    protected $queryTranslate;

    protected $queryWPDB;

    public function __construct()
    {
        global $wpdb;
        $this->queryTranslate    = QueryTranslations::getInstance();
        $this->queryWPDB         = $wpdb;
    }

    public function updateTagTC($id, $args)
    {
        $argsUpdate = [];
        if (isset($args['post_title'])) {
            $argsUpdate['post_title'] = $args['post_title'];
        }

        if (isset($args['post_content'])) {
            $argsUpdate['post_content'] = $args['post_content'];
        }

        if (isset($args['post_excerpt'])) {
            $argsUpdate['post_excerpt'] = $args['post_excerpt'];
        }
        if (!empty($argsUpdate)) {
            $this->queryWPDB->update($this->queryWPDB->posts, $argsUpdate, ['ID' => $id]);
        }
    }

    public function updateTagTCWithTerm($id, $args)
    {
        if (isset($args['name']) && strpos($args['name'], Configuration::TAG_TRANSLATE) !== false) {
            $this->queryWPDB->update($this->queryWPDB->terms, ['name' => $args['name']], ['term_id' => $id]);
        }

        if (isset($args['description']) && strpos($args['description'], Configuration::TAG_TRANSLATE) !== false) {
            $this->queryWPDB->update($this->queryWPDB->term_taxonomy, ['description' => $args['description']], ['term_id' => $id]);
        }
    }

    public function updateTimePost($object, $translate)
    {
        if ($object instanceof \WP_Post && $translate instanceof \WP_Post) {
            $args = [
                'post_date'         => $object->post_date,
                'post_date_gmt'     => $object->post_date_gmt,
                'post_modified'     => $object->post_modified,
                'post_modified_gmt' => $object->post_modified_gmt
            ];
            $this->queryWPDB->update($this->queryWPDB->posts, $args, ['ID' => $translate->ID]);
        }
    }

    public function clonePostMeta($objectID, $translateID)
    {
        $metaData = $this->queryWPDB->get_results("SELECT meta_key, meta_value FROM {$this->queryWPDB->postmeta} WHERE post_id = $objectID AND meta_key != '_wp_old_slug'");
        if (is_array($metaData) && !empty($metaData)) {
            $sqlQuery = "INSERT INTO {$this->queryWPDB->postmeta} (post_id, meta_key, meta_value) ";
            foreach ($metaData as $metaItem) {
                $metaValue      = addslashes($metaItem->meta_value);
                $sqlQuerySel[]  = "SELECT $translateID, '$metaItem->meta_key', '$metaValue'";
            }
            $sqlQuery .= implode(" UNION ALL ", $sqlQuerySel);
            $this->queryWPDB->query($sqlQuery);
        }
    }

    public function cloneTermMeta($objectID, $translateID)
    {
        $metaData = $this->queryWPDB->get_results("SELECT meta_key, meta_value FROM {$this->queryWPDB->termmeta} WHERE term_id = $objectID AND meta_key != '_wp_old_slug'");
        if (is_array($metaData) && !empty($metaData)) {
            $sqlQuery = "INSERT INTO {$this->queryWPDB->termmeta} (term_id, meta_key, meta_value) ";
            foreach ($metaData as $metaItem) {
                $metaValue      = addslashes($metaItem->meta_value);
                $sqlQuerySel[]  = "SELECT $translateID, '$metaItem->meta_key', '$metaValue'";
            }
            $sqlQuery .= implode(" UNION ALL ", $sqlQuerySel);
            $this->queryWPDB->query($sqlQuery);
        }
    }
}
