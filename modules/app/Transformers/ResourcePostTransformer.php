<?php

namespace TranscyApp\Transformers;

class ResourcePostTransformer extends BaseTransformer
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
            'ID'             => $model->ID,
            'post_name'      => $model->post_name,
            'post_title'     => $this->entityDecode($model->post_title),
            'post_content'   => $model->post_content,
            'post_excerpt'   => $model->post_excerpt,
            'thumbnail'      => $this->getThumbnail($model),
            'post_status'    => $model->post_status
        ];

        /*
        * Apply the filters for this resource transform
        */
        return apply_filters('transcy/transform/resource', $transform, $model, $options);
    }

    /**
     * Get thumbnail
     *
     * @param object $model
     *
     * @return string
     */
    public function getThumbnail($model)
    {
        if (!empty($thumbnailUrl = get_the_post_thumbnail_url($model, 'thumbnail'))) {
            return $thumbnailUrl;
        }
        return null;
    }
}
