<?php

namespace TranscyAdmin\Hooks\Resources;

use Illuminate\Interfaces\IHook;
use Illuminate\Utils\Helper;
use Illuminate\Utils\QueryTranslations;

class Menu implements IHook
{
    protected $queryTranslate;

    protected $wpdbQuery;

    public function __construct()
    {
        global $wpdb;

        $this->queryTranslate = new QueryTranslations();

        $this->wpdbQuery      = $wpdb;
    }

    public function registerHooks()
    {
        add_action('pre_get_posts',  array($this, 'preGetPosts'), 99, 1);

        //add_action('wp_update_nav_menu', array($this, 'updateMenu'), 99, 1);
    }

    /**
     * @param string $whereClause
     *
     * @return string
     */
    public function preGetPosts($query)
    {
        $query->set('suppress_filters', false);
    }

    public function updateMenu($menuId)
    {
        //Call action to add trigger update
    }
}
