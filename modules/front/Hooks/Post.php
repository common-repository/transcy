<?php

namespace TranscyFront\Hooks;

use \Illuminate\Interfaces\IHook;
use \Illuminate\Utils\HelperTranslations;
use \Illuminate\Utils\QueryTranslations;

class Post implements IHook
{
    protected $whereClauseDeault;

    protected $whereClauseAdvanced;

    protected $postNameTable;

    protected $langCurrent;

    protected $defaultLang;

    protected $queryTranslate;

    public function __construct()
    {
        global $wpdb;
        $this->langCurrent = HelperTranslations::getAdvancedLangCurrent();
        $this->defaultLang = getDefaultLang();
        //Default Lang

        $this->whereClauseDeault    = " AND {{posts_table}}.ID NOT IN (SELECT translate_id FROM {$wpdb->prefix}transcy_translations WHERE type = 'post')";

        $this->whereClauseAdvanced  = " AND {{posts_table}}.ID NOT IN (SELECT object_id FROM {$wpdb->prefix}transcy_translations as tran1 WHERE tran1.type = 'post' AND tran1.lang = '{$this->langCurrent}')
                                        AND {{posts_table}}.ID NOT IN (SELECT translate_id FROM {$wpdb->prefix}transcy_translations as tran2 WHERE tran2.type = 'post' AND tran2.lang != '{$this->langCurrent}')";

        $this->queryTranslate       = QueryTranslations::getInstance();
    }

    public function registerHooks()
    {
        // Rewrites next and previous post links to filter them by language
        add_filter('posts_where', array($this, 'postsWhere'), 99, 1);

        add_filter('get_previous_post_where', array($this, 'getPreviousPostWhere'));
        add_filter('get_next_post_where', array($this, 'getPreviousPostWhere'));
    }

    /**
     * @param string $whereClause
     *
     * @return string
     */
    public function postsWhere($whereClause)
    {
        global $wpdb;
        $this->postNameTable = $wpdb->posts;
        return $this->queryPostWhere($whereClause);
    }

    /**
     * @param string $whereClause
     *
     * @return string
     */
    public function getPreviousPostWhere($whereClause)
    {
        $this->postNameTable = 'p';
        return $this->queryPostWhere($whereClause);
    }

    public function replacePostTable(string $table, string $query)
    {
        return str_replace('{{posts_table}}', $this->postNameTable, $query);
    }

    public function queryPostWhere($whereClause)
    {
        //Query with Advanced Lang
        if ($this->defaultLang != $this->langCurrent) {
            $whereClause .= $this->replacePostTable($this->postNameTable, $this->whereClauseAdvanced);
        } else {
            $whereClause .= $this->replacePostTable($this->postNameTable, $this->whereClauseDeault);
        }
        return $whereClause;
    }
}
