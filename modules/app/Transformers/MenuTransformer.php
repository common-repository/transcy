<?php

namespace TranscyApp\Transformers;

class MenuTransformer extends BaseTransformer
{
    /**
     * Default Transform
     *
     * @param object $model
     * @param array $options = []
     *
     * @return array
     */
    public function toArray($model, $options = [])
    {
        $transform = [
            'term_id'        => $model->term_id,
            'name'           => $this->entityDecode($model->name)
        ];

        /*
        * Apply the filters for this menu transform
        */
        return apply_filters('transcy/transform/menu', $transform, $model, $options);
    }

    /**
     * Default Transform
     *
     * @param object $model
     * @param array $options = []
     *
     * @return array
     */
    public function inforItem($model, $options = [])
    {
        $model = wp_setup_nav_menu_item($model);
        $transform = [
            'ID'             => $model->ID,
            'name'           => $model->title,
            'url'            => $model->url
        ];

        /*
        * Apply the filters for this menu transform
        */
        return apply_filters('transcy/transform/menu', $transform, $model, $options);
    }
}
