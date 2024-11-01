<?php

namespace TranscyFront\Hooks;

use \Illuminate\Interfaces\IHook;
use \Illuminate\Utils\HelperTranslations;
use \Illuminate\Utils\QueryTranslations;

class Switcher implements IHook
{
    protected $langCurrent;

    protected $langDefault;

    protected $langAdvanced;

    protected $langBasic;

    protected $locationSwitcher;

    protected $queryTranslate;

    public function __construct()
    {
        $this->langCurrent         = HelperTranslations::getLangCurrent();
        $this->langDefault         = getDefaultLang();
        $this->langAdvanced        = getAdvancedLang();
        $this->langBasic           = getBasicLang();
        $this->queryTranslate      = QueryTranslations::getInstance();
        $this->locationSwitcher    = getLocationSwitcher();
    }

    public function registerHooks()
    {
        add_filter('wp_nav_menu_items', array($this, 'navMenuSwitcher'), 999, 2);

        add_action('wp_head', array($this, 'loadSwitcher'));

        add_action('wp_footer', array($this, 'loadUrlByLang'));
    }

    public function getHtmlSwitcher($position = 'floating')
    {
        $role = 0;
        if (is_user_logged_in()) {
            $current_user   = wp_get_current_user();
            $user_roles     = reset($current_user->roles);
            if (!in_array($user_roles, ['customer', 'author', 'subscriber'])) {
                $role = 1;
            }
        }
        return sprintf('<div id="transcy-wp__switcher" class="transcy-switch notranslate" data-role="%s" data-position="%s"></div>', $role, $position);
    }

    public function navMenuSwitcher($items, $args)
    {
        if ($this->switcherDisplayOnMenu()) {
            $position        = $this->locationSwitcher['position'];
            $themeLocation   = $this->locationSwitcher['slug'];
            $locationActive  = get_nav_menu_locations();
            if ($themeLocation == $args->theme_location && array_key_exists($themeLocation, $locationActive)) {
                if ($position == 'first') {
                    $items  = '<li class="transcy_swithcer_nav">' . $this->getHtmlSwitcher('embedded') . '</li>' . $items;
                } else {
                    $items  = $items . '<li class="transcy_swithcer_nav">' . $this->getHtmlSwitcher('embedded') . '</li>';
                }
            }
        }
        return $items;
    }

    public function loadSwitcher()
    {
        if (!$this->switcherDisplayOnMenu()) {
            echo $this->getHtmlSwitcher();
        }
    }

    public function loadUrlByLang()
    {
        if (is_single() || is_singular()) {
            $postObjectID     = get_the_ID();
            if (empty($postObjectID)) {
                $postObjectID = get_queried_object_id();
            }
            if (empty($postObjectID)) {
                return [];
            }

            $postObject        = get_post($postObjectID);

            if (in_array($this->langCurrent, $this->langAdvanced)) {
                $hasTranslate       = $this->queryTranslate->getFromTranslateID($postObject->ID, $postObject->post_type, $this->langCurrent);
                if (!empty($hasTranslate)) {
                    $postObjectID   = $hasTranslate->object_id;
                    $postObject     = get_post($postObjectID);
                }
            }

            //Default Lang Url
            $urlSwitcher = $this->getUrlSwitcher($postObject);
            echo "<script type='text/javascript'> var urlSwitcher = " . json_encode($urlSwitcher) . ";\n </script>";
            return;
        }

        if (is_archive()) {
            $objectTerm       = get_queried_object();
            if (empty($objectTerm)) {
                $objectTermID = get_queried_object_id();
            } else {
                if ($objectTerm instanceof \WP_Term) {
                    $objectTermID = $objectTerm->term_id;
                }
            }
            if (empty($objectTermID)) {
                echo "<script type='text/javascript'> var urlSwitcher = '';\n </script>";
                return;
            }
            $objectTerm        = get_term($objectTermID);

            if (in_array($this->langCurrent, $this->langAdvanced)) {
                $hasTranslate       = $this->queryTranslate->getFromTranslateID($objectTerm->term_id, $objectTerm->taxonomy, $this->langCurrent);
                if (!empty($hasTranslate)) {
                    $objectTermID   = $hasTranslate->object_id;
                    $objectTerm     = get_term($objectTermID);
                }
            }

            //Default Lang Url
            $urlSwitcher = $this->getUrlSwitcher($objectTerm);
            echo "<script type='text/javascript'> var urlSwitcher = " . json_encode($urlSwitcher) . ";\n </script>";
            return;
        }

        echo "<script type='text/javascript'> var urlSwitcher = '';\n </script>";
        return;
    }

    public function getUrlSwitcher($object = null)
    {
        if (empty($object)) {
            return [];
        }
        //Default Lang Url
        $urlSwiicher = [
            $this->langDefault => $this->getPermalink($object)
        ];
        //Basic Lang Url
        if (!empty($this->langBasic)) {
            foreach ($this->langBasic as $lang) {
                $urlSwiicher[$lang] = $this->getPermalink($object);
            }
        }
        //Advanced Lang
        if (!empty($this->langAdvanced)) {
            //Type post
            if ($object instanceof \WP_Post) {
                $type       = 'post';
                $postType   = $object->post_type;
                $objectID   = $object->ID;
            }
            //Type Term
            if ($object instanceof \WP_Term) {
                $type       = 'term';
                $postType   = $object->taxonomy;
                $objectID   = $object->term_id;
            }
            foreach ($this->langAdvanced as $lang) {
                $hasTranslate       = $this->queryTranslate->get($objectID, $postType, $lang);
                if (!empty($hasTranslate)) {
                    switch ($type) {
                        case 'post':
                            $objectTranslate = get_post($hasTranslate->translate_id);
                            break;
                        case 'term':
                            $objectTranslate = get_term($hasTranslate->translate_id);
                            break;
                    }
                    $urlSwiicher[$lang] = $this->getPermalink($objectTranslate);
                } else {
                    $urlSwiicher[$lang] = $this->getPermalink($object);
                }
            }
        }
        return $urlSwiicher;
    }

    public function getPermalink($object = null)
    {
        if (empty($object)) {
            return '';
        }
        //Type post
        if ($object instanceof \WP_Post) {
            return get_permalink($object);
        }
        //Type Term
        if ($object instanceof \WP_Term) {
            return get_term_link($object);
        }
        return '';
    }

    public function switcherDisplayOnMenu()
    {
        if (!empty($this->locationSwitcher)) {
            return true;
        }

        return false;
    }
}
