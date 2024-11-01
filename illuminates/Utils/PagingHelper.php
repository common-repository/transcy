<?php

namespace Illuminate\Utils;

use Illuminate\Configuration;

class PagingHelper
{
    /**
     * Render paging info by WP_Query
     *
     * @param WP_Query $query
     *
     * @return array
     */
    public static function render($query)
    {
        return [
            'current_page'  => $query->query['paged'] ?? 1,
            'per_page'      => $query->query['posts_per_page'] ?? 10,
            'found_posts'   => $query->found_posts ?? 0,
            'max_num_pages' => $query->max_num_pages ?? 0
        ];
    }

    /**
     * Render paging info by WP_Query
     *
     * @param WP_Query $query
     *
     * @return array
     */
    public static function renderTerm(\WP_REST_Request $request, $query)
    {
        $taxonomy       = reset($query->query_vars['taxonomy']);
        $foundTerms     = wp_count_terms($taxonomy, array('hide_empty' => false));
        $number         = $request['per_page'] ?? Configuration::DEFAULT_PAGINATION_ITEMS;
        $totalpages     = ceil($foundTerms / $number);

        return [
            'current_page'  => $request['page'] ?? Configuration::DEFAULT_CURRENT_PAGE,
            'per_page'      => $number,
            'found_terms'   => (int)$foundTerms ?? 0,
            'max_num_pages' => $totalpages ?? 0
        ];
    }
}
