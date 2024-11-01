<?php

namespace Illuminate\Utils;

use Illuminate\Traits\MemorySingletonTrait;

class QueryTranslations
{
    use MemorySingletonTrait;

    protected $tableName = 'transcy_translations';

    public function __construct()
    {
        global $wpdb;
        $this->tableName = sprintf('%s%s', $wpdb->prefix, $this->tableName);
    }

    /**
     * @param int $objectID
     * @param int $translateID
     * @param string $type
     * @param string $postType
     * * @param string $lang
     * @return bool|int|\mysqli_result|resource|null
     */
    public function add(int $objectID, int $translateID, string $type, string $postType, string $lang)
    {
        global $wpdb;
        //Check exist has translate
        if (!empty($row = $this->get($objectID, $postType, $lang))) {
            return $row;
        }
        return $wpdb->insert(
            $this->tableName,
            array(
                'object_id'     => $objectID,
                'translate_id'  => $translateID,
                'type'          => $type,
                'post_type'     => $postType,
                'lang'          => $lang,
            )
        );
    }

    /**
     * @param int $objectID
     * @param string $postType
     * @param string $lang
     * @return array|object|\stdClass[]|null
     */
    public function get(int $objectID, string $postType, string $lang)
    {
        global $wpdb;
        return $wpdb->get_row($wpdb->prepare("SELECT * FROM $this->tableName WHERE object_id = %d AND post_type = %s AND lang = %s", $objectID, $postType, $lang));
    }

    /**
     * @param int $translateID
     * @param string $postType
     * @param string $lang
     * @return array|object|\stdClass[]|null
     */
    public function getFromTranslateID(int $translateID, string $postType, string $lang)
    {
        global $wpdb;
        return $wpdb->get_row($wpdb->prepare("SELECT * FROM $this->tableName WHERE translate_id = %d AND post_type = %s AND lang = %s", $translateID, $postType, $lang));
    }

    /**
     * @param int $translateID
     * @param string $postType
     * @param string $lang
     * @return array|object|\stdClass[]|null
     */
    public function getFromTranslateIDWithoutLang(int $translateID, string $postType)
    {
        global $wpdb;
        return $wpdb->get_row($wpdb->prepare("SELECT * FROM $this->tableName WHERE translate_id = %d AND post_type = %s", $translateID, $postType));
    }

    /**
     * @param int $objectID
     * @param string $postType
     * @param string $lang
     * @return array|object|\stdClass[]|null
     */
    public function getTranslateWithoutLangDefault(int $objectID, string $postType)
    {
        global $wpdb;
        return $wpdb->get_results($wpdb->prepare("SELECT * FROM $this->tableName WHERE object_id = %d AND post_type = %s AND lang != %s", $objectID, $postType, getDefaultLang()));
    }

    /**
     * @param int $objectID
     * @param string $postType
     * @param string $lang
     * @return array|object|\stdClass[]|null
     */
    public function getTranslateWithoutLang(int $objectID, string $postType)
    {
        global $wpdb;
        return $wpdb->get_results($wpdb->prepare("SELECT * FROM $this->tableName WHERE object_id = %d AND post_type = %s", $objectID, $postType));
    }

    /**
     * @param int $objectID
     * @param string $postType
     * @param string $lang
     * @return array|object|\stdClass[]|null
     */
    public function delete(int $id)
    {
        global $wpdb;
        return $wpdb->delete($this->tableName, ['id' => $id]);
    }
}
