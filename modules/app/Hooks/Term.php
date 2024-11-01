<?php

namespace TranscyApp\Hooks;

use Illuminate\Interfaces\IHook;

class Term implements IHook
{

    public function registerHooks()
    {
        if (isMethodGetApi()) {
            add_filter('terms_clauses', array($this, 'termsClauses'), 99, 1);
        }
    }

    public function termsClauses($clauses)
    {
        global $wpdb;
        $clauses['where'] .= " AND t.term_id NOT IN (SELECT translate_id FROM {$wpdb->prefix}transcy_translations WHERE type = 'term')";

        return $clauses;
    }
}
