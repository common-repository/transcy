<?php

namespace TranscyApp\Repositories;

use Illuminate\Configuration;
use Illuminate\Utils\Helper;

class ResourcePostRepository extends BaseRepository
{
    /**
     * Retrive the articles by conds
     *
     * @param array $search
     * @param string orderBy
     * @param string $order
     *
     * @return mixed
     */
    public function search($type = 'post', $search = [], $orderBy = 'publish_date', $order = 'DESC')
    {
        $args = [
            'post_type'         => $type,
            'post_status'       => Configuration::POST_STATUS_SYNC,
            'paged'             => intval($search['page'] ?? 1),
            'posts_per_page'    => intval($search['per_page'] ?? 10),
            'orderby'           => $orderBy,
            'order'             => $order
        ];

        $query = new \WP_Query($args);

        //reset query
        wp_reset_query();

        return $query;
    }

    /**
     * Translate resource by type
     *
     * @param string $type
     * @param int id
     * @param array $body
     *
     * @return mixed
     */
    public function translate($postType = 'post', $object = null, $body = [])
    {
        //Check translate exist
        $translateObject = $this->queryTranslate->get($object->ID, $postType, $body['locale']);

        //Update translate
        if (!empty($translateObject)) {
            return $this->updateTranslate($object, $translateObject->translate_id, $body);
        }

        //Create translate
        return $this->createTranslate($object, $body);
    }

    /**
     * Create resource by type
     *
     * @param int objectID
     * @param array body
     *
     * @return mixed
     */
    public function createTranslate(object $object, array $body)
    {
        //Args create translate
        $args = array(
            'menu_order'        => $object->menu_order,
            'to_ping'           => $object->to_ping,
            'post_type'         => $object->post_type,
            'post_status'       => $object->post_status,
            'post_password'     => $object->post_password,
            'post_parent'       => $object->post_parent,
            'post_author'       => $object->post_author,
            'ping_status'       => $object->ping_status,
            'comment_status'    => $object->comment_status,
            'post_date'         => $object->post_date,
            'post_date_gmt'     => $object->post_date_gmt,
            'post_modified'     => $object->post_modified,
            'post_modified_gmt' => $object->post_modified_gmt,
        );
        $args = array_merge($args, $this->getArgs($body, $object));

        $translateID = wp_insert_post($args);

        if ($translateID instanceof \WP_Error) {
            return $translateID;
        }

        //Set to translation table
        $this->queryTranslate->add($object->ID, $translateID, 'post', $object->post_type, $body['locale']);
        $translateObject = get_post($translateID);

        //Set term to translate
        $this->setTermToTranslateObject($object, $translateObject, $body);

        //Clone Post Meta
        $this->clonePostMeta($object->ID, $translateObject->ID);

        //Update Title
        $this->updateTagTC($translateID, $args);

        return $translateObject;
    }

    /**
     * Update resource by type
     *
     * @param int translateID
     * @param array $body
     *
     * @return mixed
     */
    public function updateTranslate($object, $translateID = 0, $body = [])
    {
        $args = [
            'ID' => $translateID
        ];
        $args = array_merge($args, $this->getArgs($body, $object));

        $update = wp_update_post($args);
        if ($update instanceof \WP_Error) {
            return $update;
        }

        $translate = get_post($update);

        //Update Title
        $this->updateTagTC($translateID, $args);

        //Update Time Resource
        $this->updateTimePost($object, $translate);

        return $translate;
    }

    /**
     * Convert data body
     *
     * @param array body
     *
     * @return array
     */
    public function getArgs($body = [], $object = null)
    {
        $args = [];
        if (is_object($object)) {
            $args = [
                'post_title'    => $object->post_title,
                'post_content'  => $object->post_content,
                'post_excerpt'  => $object->post_excerpt,
                'post_name'     => sprintf('%s-%s', $object->post_name, $body['locale']),
            ];
        }

        if (isset($body['content']) && !empty($body['content'])) {
            $args['post_content'] = $body['content'];
        }

        if (isset($body['excerpt']) && !empty($body['excerpt'])) {
            $args['post_excerpt'] = $body['excerpt'];
        }

        if (isset($body['slug']) && !empty($body['slug'])) {
            $args['post_name'] = $body['slug'];
        }

        if (isset($body['title']) && !empty($body['title'])) {
            $args['post_title'] = $body['title'];
        }

        return $args;
    }

    public function setTermToTranslateObject($object, $translateObject, $body)
    {
        //Get all type taxonomy of object. Ex: Category, tag, product_tag, product_category...
        $taxonomies = get_object_taxonomies($object->post_type);
        if (empty($taxonomies)) {
            return;
        }

        foreach ($taxonomies as $taxonomy) {
            //Check term active translate
            if (!in_array($taxonomy, Helper::getResourceTerms())) {
                continue;
            }
            $terms          = [];
            $listTerms      = wp_get_object_terms($object->ID, $taxonomy);
            if (is_array($listTerms) && !empty($listTerms)) {
                foreach ($listTerms as $objectTerm) {
                    //Check term has translate
                    $hasTranslated = $this->queryTranslate->get($objectTerm->term_id, $taxonomy, $body['locale']);

                    if (!empty($hasTranslated)) {
                        $termTranslate  = get_term_by('id', $hasTranslated->translate_id, $taxonomy);
                        $terms[]        = $termTranslate->slug;
                    } else {
                        $terms[]        = $objectTerm->slug;
                    }
                }
            }
            wp_set_object_terms($translateObject->ID, $terms, $taxonomy, false);
        }
    }

    /**
     * Delete resource by type
     *
     * @param string $type
     * @param object $object
     * @param array $body
     *
     * @return mixed
     */
    public function delete($postType = 'post', $object = null, $body = [])
    {
        $hasTranslated = $this->queryTranslate->get($object->ID, $postType, $body['locale']);

        if (!empty($hasTranslated)) {
            $delete = wp_delete_post($hasTranslated->translate_id, true);
            if (!$delete) {
                return __('Deleted error', 'transcy');
            }
            //Delete translate from table
            $this->queryTranslate->delete($hasTranslated->id);
            return true;
        }
        return __('Not found translate deleted', 'transcy');
    }
}
