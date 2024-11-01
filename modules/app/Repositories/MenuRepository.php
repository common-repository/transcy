<?php

namespace TranscyApp\Repositories;

class MenuRepository extends BaseRepository
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
    public function get($type = 'nav_menu', $search = [], $orderBy = 'publish_date', $order = 'DESC')
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
     * Retrive the articles by conds
     *
     * @param array $search
     * @param string orderBy
     * @param string $order
     *
     * @return mixed
     */
    public function getItem($object, $type = 'nav_menu_item', $search = [], $orderBy = 'publish_date', $order = 'DESC')
    {
        $args = [
            'post_type'         => $type,
            'post_status'       => 'publish',
            'paged'             => intval($search['page'] ?? 1),
            'posts_per_page'    => intval($search['per_page'] ?? 10),
            'orderby'           => $orderBy,
            'order'             => $order,
            'tax_query'         => array(
                array(
                    'taxonomy'  => $object->taxonomy,
                    'field'     => 'term_id',
                    'terms'     => $object->term_id,
                ),
            )
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
    public function translateItem($postType = 'nav_menu_item', $object = null, $body = [])
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
     * Delete resource by type
     *
     * @param string $type
     * @param int id
     * @param array $body
     *
     * @return mixed
     */
    public function deleteItem($postType = 'nav_menu_item', $object = null, $body = [])
    {
        $hasTranslated = $this->queryTranslate->get($object->ID, $postType, $body['locale']);

        if (!empty($hasTranslated)) {
            $delete              = wp_delete_post($hasTranslated->translate_id, true);
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
     * Create menu item by type
     *
     * @param int objectID
     * @param array body
     *
     * @return mixed
     */
    public function createTranslate($object = null, $body = [])
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
        $args        = array_merge($args, $this->getArgs($body, $object));

        $translateID = wp_insert_post($args);

        if ($translateID instanceof \WP_Error) {
            return $translateID;
        }

        //Set to translation table
        $this->queryTranslate->add($object->ID, $translateID, 'post', $object->post_type, $body['locale']);
        $translateObject = get_post($translateID);

        //Clone Post Meta
        $this->clonePostMeta($object->ID, $translateObject->ID);

        //Update Child Menu

        //Update Title
        $this->updateTagTC($translateID, $args);

        //Update Url
        $this->setItemUrl($translateID, $args);

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
    public function updateTranslate($object, $translateID, $body)
    {
        $args = $this->getArgs($body, $object);

        //Update Title
        $this->updateTagTC($translateID, $args);

        //Update Url
        $this->setItemUrl($translateID,  $args);

        return get_post($translateID);
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
            $object =  wp_setup_nav_menu_item($object);
            $args   = [
                'post_title'    => $object->title,
                'url'           => $object->url
            ];
        }
        if (isset($body['name']) && !empty($body['name'])) {
            $args['post_title'] = $body['name'];
        }

        if (isset($body['url']) && !empty($body['url'])) {
            $args['url'] = $body['url'];
        }

        return $args;
    }

    public function getMenu($object, $lang = '')
    {
        $join    = 'translate_id';
        if (empty($lang)) {
            $lang    = getDefaultLang();
            $join    = 'object_id';
        }

        $menuID      = $this->queryWPDB->get_var("SELECT term_taxonomy_id FROM {$this->queryWPDB->term_relationships} as term JOIN {$this->queryWPDB->prefix}transcy_translations as tran on term.term_taxonomy_id = tran.{$join} 
                                                  WHERE tran.lang = '{$lang}' AND tran.post_type = 'nav_menu' AND term.object_id = {$object->ID}");
        if (empty($menuID)) {
            return null;
        }
        return get_term($menuID);
    }

    public function getMenuTranslate($object, $menu, $body)
    {
        $type = 'nav_menu';

        $hasTranslateMenu = $this->queryTranslate->get($menu->term_id, $type, $body['locale']);

        //Update translate
        if (!empty($hasTranslateMenu)) {
            return get_term($hasTranslateMenu->translate_id);
        }

        //Create translate 
        $args       = array(
            'parent'        => 0,
        );

        $term = wp_insert_term(sprintf('%s %s %s', $menu->name, $body['locale'], strtotime("now")),  $type, $args);
        if ($term instanceof \WP_Error) {
            return __('Create menu translate faile', 'translate');
        }
        $translateMenu = get_term($term['term_id']);
        //Set translate to table
        $this->queryTranslate->add($menu->term_id, $translateMenu->term_id, 'term', $type, $body['locale']);

        $this->mapItemMenuToTranslate($menu, $translateMenu);

        return $translateMenu;
    }

    public function mapItemMenuToTranslate($menu, $translateMenu)
    {
        $menuItem = wp_get_nav_menu_items($menu);

        if (!empty($menuItem)) {
            $metaData = [];
            foreach ($menuItem as $item) {
                $metaData[] = sprintf("( '%d', '%d', '0' )", $item->ID, $translateMenu->term_id);
            }
            $count     = count($metaData);
            $metaData  = implode(', ', $metaData);
            $sql_query = "INSERT INTO {$this->queryWPDB->term_relationships} (object_id, term_taxonomy_id, term_order) VALUE  $metaData";
            $this->queryWPDB->query($sql_query);
            $this->queryWPDB->update($this->queryWPDB->term_taxonomy, ['count' => $count], ['term_taxonomy_id' => $translateMenu->term_id, 'term_id' => $translateMenu->term_id]);
        }
    }

    public function setItemUrl($translateID, $body)
    {
        update_post_meta($translateID, '_menu_item_type', 'custom');
        update_post_meta($translateID, '_menu_item_url', $body['url']);
    }

    public function locations(){
        return get_registered_nav_menus();
    }
}
