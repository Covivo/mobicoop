<?php

namespace App\Normalizer;

use ApiPlatform\Core\Serializer\AbstractItemNormalizer;

/**
 * Custom ItemNormalizer to be able to post an object with an id.
 */
class ItemNormalizer extends AbstractItemNormalizer
{
    private const IDENTIFIER = 'id';

    /**
     * @param mixed  $data
     * @param string $class
     * @param string $format
     *
     * @return object
     */
    public function denormalize($data, $class, $format = null, array $context = [])
    {
        if (is_array($data)) {
            foreach ($data as $key => &$value) {
                if (self::IDENTIFIER === $key) {
                    unset($data[$key]);
                }
            }
        }

        $context['api_denormalize'] = true;

        if (!isset($context['resource_class'])) {
            $context['resource_class'] = $class;
        }

        return parent::denormalize($data, $class, $format, $context);
    }
}
