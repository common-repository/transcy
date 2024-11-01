<?php

namespace TranscyApp\Repositories;

class ResourceTermRepository extends BaseRepository
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
    public function search($type = 'category', $search = [], $orderBy = 'term_id', $order = 'ASC')
    {
        $paged      = intval($search['page'] ?? 1);
        $perPage    = intval($search['per_page'] ?? 10);
        $offset     = ($paged > 0) ?  $perPage * ($paged - 1) : 0;

        $args   = [
            'taxonomy'          => $type,
            'hide_empty'        => false,
            'offset'            => $offset,
            'number'            => $perPage,
            'orderby'           => $orderBy,
            'order'             => $order
        ];

        $query = new \WP_Term_Query($args);

        //reset query
        wp_reset_query();

        return $query;
    }

    /**
     * Translate resource by term type
     *
     * @param string $type
     * @param object object
     * @param array $body
     *
     * @return mixed
     */
    public function translate($termType = 'category', $object = null, $body = [])
    {
        //Check translate exist
        $translateObject = $this->queryTranslate->get($object->term_id, $termType, $body['locale']);

        //Update translate
        if (!empty($translateObject)) {
            return $this->updateTranslate($object, $translateObject->translate_id, $body);
        }

        //Create translate
        return $this->createTranslate($object, $body);
    }


    /**
     * Update resource by type
     *
     * @param string termType
     * @param int translateID
     * @param array $body
     *
     * @return mixed
     */
    public function updateTranslate($object = null, $translateID = 0, $body = [])
    {
        $args            = $this->getArgs($body, $object);
        $update          = wp_update_term($translateID, $object->taxonomy, $args);
        if ($update instanceof \WP_Error) {
            return $update;
        }

        //Update Title
        $this->updateTagTCWithTerm($translateID, $args);

        return get_term($translateID);
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
        //Get parent source
        $parent = $object->parent;
        if ($parent > 0) {
            //Get parent translation
            if (!empty($parentTranslate = $this->queryTranslate->get($parent, $object->taxonomy, $body['locale']))) {
                $parent = $parentTranslate->translate_id;
            };
        }
        $args           = $this->getArgs($body, $object);
        $args['parent'] = $parent;

        $translate      = wp_insert_term($args['name'], $object->taxonomy, $args);

        if ($translate instanceof \WP_Error) {
            return $translate;
        }
        $translateID    = $translate['term_id'];

        //Set to translation table
        $this->queryTranslate->add($object->term_id, $translateID, 'term', $object->taxonomy, $body['locale']);

        //Map post to term
        $this->mapPostToTerm($object, $translateID, $body);

        //Map child term if has translate
        $this->mapChildToTerm($object, $translateID, $body);

        //Clone Term Meta
        $this->cloneTermMeta($object->term_id, $translateID);

        //Update Title
        $this->updateTagTCWithTerm($translateID, $args);

        return get_term($translateID);
    }

    /**
     * Map post has translate to term just translate
     *
     * @return mixed
     */
    public function mapPostToTerm($object = null, $translateID = 0, $body = [])
    {
        //Check exisit
        if (empty($object) && empty($translateID) && !isset($body['locale'])) {
            return;
        }

        //Get all relationship of object ID
        $results = $this->queryWPDB->get_results("SELECT * FROM {$this->queryWPDB->term_relationships} WHERE term_taxonomy_id = $object->term_id");
        if (!empty($results)) {
            $metaData = [];
            foreach ($results as $item) {
                $post = get_post($item->object_id);
                if (empty($post)) {
                    continue;
                }
                //Get translate of post.
                $hasTranslate = $this->queryTranslate->getFromTranslateID($item->object_id, $post->post_type, $body['locale']);

                if (empty($hasTranslate)) {
                    //Set post current to translate category
                    $sql_query = "INSERT INTO {$this->queryWPDB->term_relationships} (object_id, term_taxonomy_id, term_order) VALUE ({$item->object_id},{$translateID}, 0)";
                    $this->queryWPDB->query($sql_query);
                    continue;
                }
                //Set post translate to term translate
                $metaData[] = sprintf("( %d, %d, 0 )", $hasTranslate->translate_id, $translateID);
                //Remove Post With Term
                $this->queryWPDB->delete($this->queryWPDB->term_relationships, ['object_id' => $hasTranslate->translate_id, 'term_taxonomy_id' => $item->term_taxonomy_id], ['%d', '%d']);
            }

            if (!empty($metaData)) {
                $metaData  = implode(', ', $metaData);
                $sql_query = "INSERT INTO {$this->queryWPDB->term_relationships} (object_id, term_taxonomy_id, term_order) VALUE $metaData";
                $this->queryWPDB->query($sql_query);
            }
        }
    }

    /**
     * Map child term if has translate
     *
     * @return mixed
     */
    public function mapChildToTerm($object, $translateID, $body)
    {
        $results = $this->queryWPDB->get_results("SELECT * FROM {$this->queryWPDB->term_taxonomy} WHERE parent = $object->term_id");
        if (!empty($results)) {
            foreach ($results as $item) {
                $hasTranslate = $this->queryTranslate->get($item->term_id, $item->taxonomy, $body['locale']);
                if (empty($hasTranslate)) {
                    continue;
                }
                $this->queryWPDB->update($this->queryWPDB->term_taxonomy, ['parent' => $translateID], ['term_id' => $hasTranslate->translate_id, 'term_taxonomy_id' => $hasTranslate->translate_id], ['%d', '%d']);
            }
        }
    }

    /**
     * Delete resource by type
     *
     * @param string $termType
     * @param object $object
     * @param array $body
     *
     * @return mixed
     */
    public function delete($termType = 'category', $object = null, $body = [])
    {
        $hasTranslated = $this->queryTranslate->get($object->term_id, $termType, $body['locale']);

        if (!empty($hasTranslated)) {
            $delete = wp_delete_term($hasTranslated->translate_id, $termType);
            //var_dump($delete); die();
            if (!$delete) {
                return __('Deleted error', 'transcy');
            }
            //Delete translate from table
            $this->queryTranslate->delete($hasTranslated->id);
            return true;
        }
        return __('Not found translate deleted', 'transcy');
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
                'name'          => $object->name,
                'description'   => $object->description,
                'slug'          => sprintf('%s-%s', $object->slug, $body['locale']),
            ];
        }

        if (isset($body['name']) && !empty($body['name'])) {
            $args['name'] = $body['name'];
        }

        if (isset($body['description']) && !empty($body['description'])) {
            $args['description'] = $body['description'];
        }

        if (isset($body['slug']) && !empty($body['slug'])) {
            $args['slug'] = $body['slug'];
        }

        return $args;
    }
}
