<?php

namespace TranscyFront\Hooks;

use \Illuminate\Interfaces\IHook;
use \Illuminate\Utils\HelperTranslations;
use \Illuminate\Utils\QueryTranslations;

class Menu implements IHook
{
    protected $joinClauseDeault;

    protected $whereClauseDeault;

    protected $joinClauseAdvanced;

    protected $whereClauseAdvanced;

    protected $langCurrent;

    protected $defaultLang;

    protected $queryTranslate;

    public function __construct()
    {
        $this->langCurrent = HelperTranslations::getAdvancedLangCurrent();
        $this->defaultLang = getDefaultLang();

        $this->queryTranslate    = QueryTranslations::getInstance();
    }

    public function registerHooks()
    {
        add_filter('wp_get_nav_menu_items', array($this, 'getNavMenuItem'), PHP_INT_MAX, 1);
        
        add_filter('get_post_metadata', array($this, 'getParrentMenu'), PHP_INT_MAX, 3);

        add_filter('page_link', array($this, 'pageLink'), 99, 2);
    }

    /**
     * Get translate menu on store front
     *
     * @param $menuObj
     */
    public function getParrentMenu($value, $object_id, $meta_key)
    {
        if ($this->defaultLang != $this->langCurrent && $meta_key == '_menu_item_menu_item_parent') {
            global $wpdb;
            $parentID = $wpdb->get_var("SELECT meta_value FROM $wpdb->postmeta WHERE post_id = $object_id AND meta_key = '_menu_item_menu_item_parent'");
            if ($parentID > 0) {
                $hasTranslate = $this->queryTranslate->get($parentID, 'nav_menu_item', $this->langCurrent);
                if (!empty($hasTranslate)) {
                    return (int)$hasTranslate->translate_id;
                }
                return $value;
            }
        }
        return $value;
    }

    public function getNavMenuItem($items)
    {
        if ($this->defaultLang != $this->langCurrent){
            foreach ($items as $key => $item) {
                $hasTranslate = $this->queryTranslate->get($item->ID, 'nav_menu_item', $this->langCurrent);
                if (!empty($hasTranslate)) {
                    $items[$key] = wp_setup_nav_menu_item(get_post($hasTranslate->translate_id));
                }
            }
        }
        return $items;
    }

    public function pageLink($link, $postId)
    {
        $post         = get_post($postId);
        $hasTranslate = $this->queryTranslate->get($postId, $post->post_type, $this->langCurrent);
        if ($this->defaultLang != $this->langCurrent && !empty($hasTranslate) && $post->post_type == 'page') {
            return get_page_link($hasTranslate->translate_id);
        }
        return $link;
    }
}
