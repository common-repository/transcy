<?php

namespace TranscyAdmin\Hooks\Resources;

use Illuminate\Configuration;
use Illuminate\Interfaces\IHook;
use Illuminate\Utils\Helper;
use Illuminate\Utils\QueryTranslations;

class Post implements IHook
{
    protected $whereClause;

    protected $queryTranslate;

    protected $wpdbQuery;

    public function __construct()
    {
        global $wpdb;
        $this->wpdbQuery      = $wpdb;

        $this->whereClause    = " AND {$this->wpdbQuery->posts}.ID NOT IN (SELECT translate_id FROM {$this->wpdbQuery->prefix}transcy_translations WHERE type = 'post')";

        $this->queryTranslate = new QueryTranslations();
        
    }

    public function registerHooks()
    {
        add_filter('posts_where', array($this, 'postsWhere'), 99, 2);

        add_filter('wp_count_posts', array($this, 'countPost'), 999, 3);

        add_filter('query', array($this, 'query'), 99, 1);

        // //Save post
        // add_action('save_post', array($this, 'savePost'), 99, 3);
        // add_action('after_delete_post', array($this, 'deletePost'), 99, 2);
    }

    /**
     * @param string $whereClause
     *
     * @return string
     */
    public function postsWhere($whereClause, $wpQuery)
    {
        if (in_array($wpQuery->query_vars['post_type'], Helper::getResourcePosts())) {
            $whereClause .= $this->whereClause;
        }
        return $whereClause;
    }

    /**
     * Filters the post counts by status for the current post type.
     *
     * @since 3.7.0
     *
     * @param stdClass $counts An object containing the current post_type's post
     *                         counts by status.
     * @param string   $type   Post type.
     * @param string   $perm   The permission to determine if the posts are 'readable'
     *                         by the current user.
     */
    public function countPost($counts, $type, $perm)
    {
        global $wpdb;

        $query = "SELECT post_status, COUNT( * ) AS num_posts FROM {$wpdb->posts} WHERE {$wpdb->posts}.post_type = %s {$this->whereClause}";

        if ('readable' === $perm && is_user_logged_in()) {
            $post_type_object = get_post_type_object($type);
            if (!current_user_can($post_type_object->cap->read_private_posts)) {
                $query .= $wpdb->prepare(
                    " AND (post_status != 'private' OR ( post_author = %d AND post_status = 'private' ))",
                    get_current_user_id()
                );
            }
        }

        $query .= ' GROUP BY post_status';

        $results = (array) $wpdb->get_results($wpdb->prepare($query, $type), ARRAY_A);
        $counts  = array_fill_keys(get_post_stati(), 0);

        foreach ($results as $row) {
            $counts[$row['post_status']] = $row['num_posts'];
        }

        $counts = (object) $counts;

        return $counts;
    }

    public function query($query)
    {
        if (strpos($query, 'post_author =') !== false && strpos($query, "SELECT COUNT") !== false) {
            $query     .= $this->whereClause;
        }

        return $query;
    }

    /**
     * Hook active save post
     *
     * @param $term_id
     */
    public function savePost(int $postID, \WP_Post $post, bool $update)
    {
        //Trigger hook to app
        if (!in_array($post->post_type, Helper::getResourcePosts()) || $post->post_type == 'nav_menu_item') {
            return;
        }

        //Check Resource Status
        if (in_array($post->post_status, Configuration::POST_STATUS_NOT_SYNC)) {
            return;
        }

        //Update post status
        $translates = $this->queryTranslate->getTranslateWithoutLangDefault($postID, $post->post_type);
        if (!empty($translates)) {
            foreach ($translates as $value) {
                $this->wpdbQuery->update($this->wpdbQuery->posts, ['post_status' => $post->post_status], ['ID' => $value->translate_id]);
            }
        }

        //Map category translate to post
        $this->mapCategoryTranslate($postID);

        //Set feature image to translate
        $this->mapFeatureImage($post);

        $GLOBALS['appService']->changeResource([
            "type"  => $post->post_type,
            "id"    => $postID
        ]);
    }

    /**
     * Hook active save post
     *
     * @param $term_id
     */
    public function deletePost(int $postID, \WP_Post $post)
    {
        //Trigger hook to app
        if (!in_array($post->post_type, Helper::getResourcePosts()) || $post->post_type == 'nav_menu_item') {
            return;
        }

        //Check Resource Status
        if (in_array($post->post_status, Configuration::POST_STATUS_NOT_SYNC)) {
            return;
        }

        //Add to table translate
        $translates = $this->queryTranslate->getTranslateWithoutLang($postID, $post->post_type);
        if (!empty($translates)) {
            foreach ($translates as $value) {
                $this->wpdbQuery->delete($this->wpdbQuery->prefix . 'transcy_translations', ['id' => $value->id]);
                if ($value->translate_id != $postID) {
                    wp_delete_post($value->translate_id, true);
                }
            }
        }

        $GLOBALS['appService']->changeResource([
            "type"  => $post->post_type,
            "id"    => $postID
        ]);
    }

    public function mapCategoryTranslate($postID)
    {
        $listTaxonomy = $this->wpdbQuery->get_results("SELECT * FROM {$this->wpdbQuery->term_relationships} WHERE object_id = {$postID}");
        if (!empty($listTaxonomy)) {
            foreach ($listTaxonomy as $item) {
                $taxonomy = get_term($item->term_taxonomy_id);

                if (!empty($taxonomy)) {
                    $listTranslate = $this->queryTranslate->getTranslateWithoutLangDefault($taxonomy->term_id, $taxonomy->taxonomy);
                    foreach ($listTranslate as $translate) {
                        $this->wpdbQuery->insert($this->wpdbQuery->term_relationships, ['term_taxonomy_id' => $translate->translate_id, 'object_id' => $postID]);
                    }
                }
            }
        }
    }

    public function mapFeatureImage($post)
    {
        $listTranslate = $this->queryTranslate->getTranslateWithoutLangDefault($post->ID, $post->post_type);
        if (!empty($listTranslate)) {
            if (!empty($thumbnailID = get_post_thumbnail_id($post))) {
                foreach ($listTranslate as $translate) {
                    update_post_meta($translate->translate_id, '_thumbnail_id', $thumbnailID);
                }
            } else {
                foreach ($listTranslate as $translate) {
                    delete_post_meta($translate->translate_id, '_thumbnail_id');
                }
            }
        }
    }
}
