<?php

namespace TranscyApp\Hooks;

use Illuminate\Interfaces\IHook;

class Post implements IHook
{
    public function registerHooks()
    {
        if (isMethodGetApi()) {
            add_filter('posts_where', array($this, 'postsWhere'), 99, 1);
        }
    }

    /**
     * @param string $whereClause
     *
     * @return string
     */
    public function postsWhere($whereClause)
    {
        global $wpdb;
        $whereClause .=  " AND {$wpdb->posts}.ID NOT IN (SELECT translate_id FROM {$wpdb->prefix}transcy_translations WHERE type = 'post')";
        return $whereClause;
    }
}
