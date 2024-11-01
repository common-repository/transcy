<?php

namespace Illuminate\BaseClass;

use Illuminate\Traits\SpineTrait;
use Illuminate\Traits\MemorySingletonTrait;

class Repository
{
    use SpineTrait;
    use MemorySingletonTrait;

    /**
     * Retrive the post by id.
     *
     * @param int $id
     * @param string $type
     *
     * @return array|\WP_Post|null
     */
    public function findPostById(int $id, string $type = 'post')
    {
        $args = [
            'p' => $id,
            'post_type'   => $type,
            'numberposts' => 1
        ];

        //Retrieves an array of the latest posts, or posts matching the given criteria.
        $posts = get_posts($args);

        //Shift an element off the beginning of array
        return array_shift($posts);
    }

    /**
     * Retrive post array by WP_Query
     *
     * @param \WP_Query $query
     *
     * @return array
     */
    public function wpQueryToArray($query)
    {
        if (is_null($query)) {
            return [];
        }

        $posts = [];

        if ($query->have_posts()) {
            while ($query->have_posts()) {
                $query->the_post();

                $posts[] = $query->post;
            };
        }

        return $posts;
    }

    /**
     * Retrive post array by WP_Term_Query
     *
     * @param \WP_Query $query
     *
     * @return array
     */
    public function wpQueryTermToArray($query)
    {
        if (is_null($query)) {
            return [];
        }

        $terms = [];

        if ($query->terms) {
            foreach ($query->terms as $term) {
                $terms[] = $term;
            };
        }

        return $terms;
    }
}
