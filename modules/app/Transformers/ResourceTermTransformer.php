<?php

namespace TranscyApp\Transformers;

class ResourceTermTransformer extends BaseTransformer
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
            'name'           => $this->entityDecode($model->name),
            'description'    => $model->description,
            'thumbnail'      => $this->getThumbnail($model),
            'slug'           => $model->slug
        ];

        /*
        * Apply the filters for this article transform
        */
        return apply_filters('transcy/transform/term', $transform, $model, $options);
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
        if (!empty($thumbnailID = get_term_meta($model->term_id, 'thumbnail_id', true))) {
            return wp_get_attachment_image_url($thumbnailID);
        }
        return null;
    }
}
