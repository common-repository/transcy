<?php

namespace Illuminate\Utils;

use Illuminate\Traits\MemorySingletonTrait;
use Illuminate\Utils\QueryTranslations;
use Illuminate\Configuration;
use Illuminate\Services\AppService;

class HelperTranslations
{
    use MemorySingletonTrait;

    protected $tableName = 'transcy_translations';

    protected $appService;

    protected $advancedLang;

    protected $query;

    protected $queryWPDB;

    public function __construct()
    {
        global $wpdb;
        $this->tableName    = sprintf('%s%s', $wpdb->prefix, $this->tableName);

        $this->query        = QueryTranslations::getInstance();

        $this->queryWPDB    = $wpdb;

        $this->appService     = AppService::getInstance();
    }

    /**
     * Sync resource post, taxonomy to table translate with default language
     *
     * @return bool
     */
    public function syncResourceToTranslate()
    {
        //Sync resource post
        $defaultLang = getDefaultLang();
        $posts       = $this->queryWPDB->get_results($this->queryWPDB->prepare(
            "SELECT * FROM {$this->queryWPDB->posts} WHERE post_type IN (" . $this->escapeArray(Helper::getResourcePosts()) . ") 
            AND ID NOT IN (SELECT translate_id FROM %i as tran01 WHERE tran01.lang = %s AND tran01.type = 'post')
            AND ID NOT IN (SELECT translate_id FROM  %i as tran02 WHERE tran02.lang <>  %s AND tran02.type = 'post')
            LIMIT 0, 100000",
            $this->tableName,
            $defaultLang,
            $this->tableName,
            $defaultLang
        ));

        if (!empty($posts)) {
            foreach ($posts as $value) {
                $this->query->add($value->ID, $value->ID, 'post', $value->post_type, $defaultLang);
            }
        }

        //Sync resource taxonomy
        $taxonomy     = $this->queryWPDB->get_results($this->queryWPDB->prepare(
            "SELECT * FROM {$this->queryWPDB->term_taxonomy} WHERE taxonomy IN (" . $this->escapeArray(Helper::getResourceTerms()) . ")
            AND term_id NOT IN (SELECT translate_id FROM %i as tran01 WHERE tran01.lang = %s AND tran01.type = 'term')
            AND term_id NOT IN (SELECT translate_id FROM %i as tran02 WHERE tran02.lang <> %s AND tran02.type = 'term')
            LIMIT 0, 100000",
            $this->tableName,
            $defaultLang,
            $this->tableName,
            $defaultLang
        ));

        if (!empty($taxonomy)) {
            foreach ($taxonomy as $key => $value) {
                $this->query->add($value->term_id, $value->term_id, 'term', $value->taxonomy, $defaultLang);
            }
        }

        return true;
    }

    /**
     * update migrate translate
     *
     * @return bool
     */
    public function updateMigrateTranslate()
    {
        //Sync resource post
        $defaultLang = getDefaultLang();
        $translates  = $this->queryWPDB->get_results("SELECT translate_id, post_type, count(translate_id) as count FROM {$this->tableName} group by translate_id, post_type having COUNT(translate_id) > 1");
        if (!empty($translates)) {
            foreach ($translates as $itemTranslate) {
                $hasTranslateDefault = $this->query->get($itemTranslate->translate_id, $itemTranslate->post_type, $defaultLang);
                if (!empty($hasTranslateDefault)) {
                    $this->queryWPDB->delete($this->tableName, ['id' => $hasTranslateDefault->id]);
                }
            }
        }
    }

    /**
     * escape array
     *
     * @return string
     */
    public function escapeArray($arr)
    {
        $escaped = array();
        foreach ($arr as $k => $v) {
            if (is_numeric($v))
                $escaped[] = $this->queryWPDB->prepare('%d', $v);
            else
                $escaped[] = $this->queryWPDB->prepare('%s', $v);
        }
        return implode(',', $escaped);
    }

    /**
     * Get advanced language current active
     *
     * @return bool
     */
    public static function getAdvancedLangCurrent()
    {
        $lang = self::getLangCurrent();
        if (in_array($lang, getAdvancedLang())) {
            return $lang;
        }
        return getDefaultLang();
    }

    /**
     * Get advanced language current active
     *
     * @return bool
     */
    public static function getLangCurrent()
    {
        $listLang   = getListLang();
        if (isSwicherTypeFriendly() && !empty($listLang)) {
            $actualLink = (empty($_SERVER['HTTPS']) ? 'http' : 'https') . "://$_SERVER[HTTP_HOST]$_SERVER[REQUEST_URI]";
            $domain     = Helper::getDomain();
            foreach ($listLang as $lang) {
                $checkLang = sprintf('%s/%s/', $domain, $lang);
                if (strpos($actualLink, $checkLang) !== false) {
                    return $lang;
                }
            }
        }

        if (isset($_GET['lang']) && !empty($_GET['lang'])) {
            return $_GET['lang'];
        }
        return getDefaultLang();
    }

    public function deactive()
    {
        $transcyPost  = Configuration::POST_STATUS_DEACTIVE;
        $transcyTerm  = Configuration::TERM_TAXONOMY_DEACTIVE;

        $results      = $this->queryWPDB->get_results("SELECT * FROM {$this->tableName} WHERE object_id != translate_id");
        if (!empty($results)) {
            foreach ($results as $value) {
                switch ($value->type) {
                    case 'post':
                        $this->queryWPDB->query("UPDATE {$this->queryWPDB->posts} SET post_type = '{$transcyPost}{$value->post_type}' WHERE ID = {$value->translate_id}");
                        break;
                    case 'term':
                        $this->queryWPDB->query("UPDATE {$this->queryWPDB->term_taxonomy} SET taxonomy = '{$transcyTerm}{$value->post_type}' WHERE term_id = {$value->translate_id}");
                        break;
                }
            }
        }
    }

    public function reActive()
    {
        $results      = $this->queryWPDB->get_results("SELECT * FROM {$this->tableName} WHERE object_id != translate_id");
        if (!empty($results)) {
            foreach ($results as $value) {
                switch ($value->type) {
                    case 'post':
                        $this->queryWPDB->query("UPDATE {$this->queryWPDB->posts} SET post_type = '{$value->post_type}' WHERE ID = {$value->translate_id}");
                        break;
                    case 'term':
                        $this->queryWPDB->query("UPDATE {$this->queryWPDB->term_taxonomy} SET taxonomy = '{$value->post_type}' WHERE term_id = {$value->translate_id}");
                        break;
                }
            }
        }
    }

    public function uninstall()
    {
        $defaultLang  = getDefaultLang();

        //Post
        $listPost     = $this->queryWPDB->get_results("SELECT post.ID FROM {$this->queryWPDB->posts} post RIGHT JOIN {$this->tableName} as tran ON tran.translate_id = post.ID   
                                                       WHERE tran.type = 'post' AND tran.lang != '{$defaultLang}'");
        if (!empty($listPost)) {
            $listPost     = wp_list_pluck($listPost, 'ID');
            $this->queryWPDB->query("DELETE FROM {$this->queryWPDB->posts} WHERE ID IN (" . $this->escapeArray($listPost) . ")");
            $this->queryWPDB->query("DELETE FROM {$this->queryWPDB->postmeta} WHERE post_id IN (" . $this->escapeArray($listPost) . ")");
        }

        //Term 
        $listTerm     = $this->queryWPDB->get_results("SELECT term.term_id FROM {$this->queryWPDB->term_taxonomy} term RIGHT JOIN {$this->tableName} as tran ON tran.translate_id = term.term_id   
                                                      WHERE tran.type = 'term' AND tran.lang != '{$defaultLang}'");
        if (!empty($listTerm)) {
            $listTerm     = wp_list_pluck($listTerm, 'term_id');
            $this->queryWPDB->query("DELETE FROM {$this->queryWPDB->term_taxonomy} WHERE term_id IN (" . $this->escapeArray($listTerm) . ")");
            $this->queryWPDB->query("DELETE FROM {$this->queryWPDB->termmeta} WHERE term_id IN (" . $this->escapeArray($listTerm) . ")");
        }

        //Drop Table
        $this->queryWPDB->query("DROP TABLE IF EXISTS {$this->tableName}");
    }

    public function deleteOption()
    {
        //Delete option
        $key       = [
            'transcy_apikey',
            'transcy_app_apikey',
            'transcy_client_data',
            '_transcy_upgrader_tracking',
            '_transcy_time_sync_resources',
            '_transcy_registered_site'
        ];
        $this->queryWPDB->query("DELETE FROM {$this->queryWPDB->options} WHERE option_name IN (" . $this->escapeArray($key) . ")");
    }
}
