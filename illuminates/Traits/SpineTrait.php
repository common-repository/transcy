<?php

namespace Illuminate\Traits;

use Illuminate\Utils\Helper;

trait SpineTrait
{
    /**
     * Get field option
     *
     * @param int $id
     *
     * @return string
     */
    public function fieldOption($selector)
    {
        return $this->field($selector, 'option');
    }

    /**
     * Convert HTML entities to their corresponding characters
     *
     * @param string $text
     *
     * @return string
     */
    public function entityDecode($text)
    {
        return html_entity_decode($text);
    }

    /**
     * Time ago
     *
     * @param date $date
     *
     * @return string
     */
    public function timeAgo($date, $returnDate = true)
    {
        return Helper::humanTime($date, $returnDate);
    }
}
