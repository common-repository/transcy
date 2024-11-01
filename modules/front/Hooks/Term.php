<?php

namespace TranscyFront\Hooks;

use \Illuminate\Interfaces\IHook;
use \Illuminate\Utils\HelperTranslations;
use \Illuminate\Utils\QueryTranslations;
use Illuminate\Utils\Helper;

class Term implements IHook
{
    protected $joinClauseDeault;

    protected $whereClauseDeault;

    protected $joinClauseAdvanced;

    protected $whereClauseAdvanced;

    protected $langCurrent;

    protected $defaultLang;

    protected $queryTranslate;

    public function __construct()
    {
        global $wpdb;
        $this->langCurrent = HelperTranslations::getAdvancedLangCurrent();
        $this->defaultLang = getDefaultLang();
        //Default Lang
        $this->whereClauseDeault    = " AND t.term_id NOT IN (SELECT translate_id FROM {$wpdb->prefix}transcy_translations WHERE type = 'term')";

        //Advanced Lang
        $this->whereClauseAdvanced  = " AND t.term_id NOT IN (SELECT object_id FROM {$wpdb->prefix}transcy_translations as tran1 WHERE tran1.type = 'term' AND tran1.lang = '{$this->langCurrent}')
                                        AND t.term_id NOT IN (SELECT translate_id FROM {$wpdb->prefix}transcy_translations as tran2 WHERE tran2.type = 'term' AND tran2.lang != '{$this->langCurrent}')";

        $this->queryTranslate       = QueryTranslations::getInstance();
    }

    public function registerHooks()
    {
        add_filter('terms_clauses', array($this, 'termsClauses'), 99, 1);
        //add_filter('get_the_terms', array($this, 'getTheTerms'), 99, 3);
    }

    public function termsClauses($clauses)
    {
        //Query with Advanced Lang
        if ($this->defaultLang != $this->langCurrent) {
            $clauses['where'] .= $this->whereClauseAdvanced;
        } else {
            $clauses['where'] .= $this->whereClauseDeault;
        }
        return $clauses;
    }

    public function getTheTerms($terms, $postID, $taxonomy)
    {
        if (!in_array($taxonomy, Helper::getResourceTerms())) {
            return $terms;
        }
        
        if ($this->langCurrent != $this->defaultLang) {
            if (is_object($terms)) {
                $hasTransalte = $this->queryTranslate->get($terms->term_id, $taxonomy, $this->langCurrent);
                if (!empty($hasTransalte)) {
                    return get_term($hasTransalte->translate_id);
                }
                return $terms;
            }
            if(is_array($terms)){
                $tempTerms = [];
                foreach ($terms as $term) {
                    $hasTransalte = $this->queryTranslate->get($term->term_id, $taxonomy, $this->langCurrent);
                    if (!empty($hasTransalte)) {
                        $tempTerms[] = get_term($hasTransalte->translate_id);
                    }else{
                        $tempTerms[] = $term;
                    }
                }
                return $tempTerms;
            }
        }
        return $terms;
    }
}
