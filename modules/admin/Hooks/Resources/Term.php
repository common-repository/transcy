<?php

namespace TranscyAdmin\Hooks\Resources;

use Illuminate\Interfaces\IHook;
use Illuminate\Utils\HelperTranslations;
use Illuminate\Utils\Helper;
use Illuminate\Utils\QueryTranslations;

class Term implements IHook
{
    protected $whereClause;

    protected $queryTranslate;

    protected $wpdbQuery;

    public function __construct()
    {
        global $wpdb;
        $this->wpdbQuery        = $wpdb;

        $this->whereClause      = " AND t.term_id NOT IN (SELECT translate_id FROM {$this->wpdbQuery->prefix}transcy_translations WHERE type = 'term')";

        $this->queryTranslate   = new QueryTranslations();
    }

    public function registerHooks()
    {
        add_filter('terms_clauses', array($this, 'termsClauses'), 99, 1);

        //Save term
        add_action('saved_term', array($this, 'savedTerm'), 99, 3);
        add_action('delete_term', array($this, 'deleteTerm'), 99, 3);
    }

    public function termsClauses($clauses)
    {
        $clauses['where'] .= $this->whereClause;

        return $clauses;
    }

    public function savedTerm(int $termId, int $ttId, string $taxonomy)
    {
        //Trigger hook to app
        if (!in_array($taxonomy, Helper::getResourceTerms()) || $taxonomy == 'nav_menu') {
            return;
        }

        //Set feature image to translate
        $this->mapFeatureImage($termId, $taxonomy);

        $GLOBALS['appService']->changeResource([
            "type"      => $taxonomy,
            "id"        => $termId
        ]);
    }

    /**
     * Hook active save post
     *
     * @param $term_id
     */
    public function deleteTerm(int $termId, int $ttId, string $taxonomy)
    {
        //Trigger hook to app
        if (!in_array($taxonomy, Helper::getResourceTerms()) || $taxonomy == 'nav_menu') {
            return;
        }

        //Add to table translate
        $translates = $this->queryTranslate->getTranslateWithoutLang($termId, $taxonomy);
        if (!empty($translates)) {
            foreach ($translates as $value) {
                $this->wpdbQuery->delete($this->wpdbQuery->prefix . 'transcy_translations', ['id' => $value->id]);
                if ($value->translate_id != $termId) {
                    $this->wpdbQuery->delete($this->wpdbQuery->term_taxonomy, ['term_id' => $value->translate_id, 'term_taxonomy_id' => $value->translate_id]);
                    $this->wpdbQuery->delete($this->wpdbQuery->terms, ['term_id' => $value->translate_id]);
                    $this->wpdbQuery->delete($this->wpdbQuery->term_relationships, ['term_taxonomy_id' => $value->translate_id]);
                }
            }
        }

        $GLOBALS['appService']->changeResource([
            "type"      => $taxonomy,
            "id"        => $termId
        ]);
    }

    public function mapFeatureImage($termId, $taxonomy)
    {
        $listTranslate = $this->queryTranslate->getTranslateWithoutLangDefault($termId, $taxonomy);
        if (!empty($listTranslate)) {
            if (!empty($thumbnailID = get_term_meta($termId, 'thumbnail_id'))) {
                foreach ($listTranslate as $translate) {
                    update_term_meta($translate->translate_id, 'thumbnail_id', $thumbnailID);
                }
            } else {
                foreach ($listTranslate as $translate) {
                    delete_term_meta($translate->translate_id, 'thumbnail_id');
                }
            }
        }
    }
}
