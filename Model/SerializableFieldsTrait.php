<?php
/**
 * User: Andreas Penz <office@dopa.media>
 * Date: 02.10.17
 */

namespace Dopamedia\Batch\Model;

use Magento\Framework\DataObject;
use Magento\Framework\Serialize\SerializerInterface;

/**
 * Trait SerializableFieldTrait
 * @package Dopamedia\Batch\Model
 */
trait SerializableFieldsTrait
{
    /**
     * @param SerializerInterface $serializer
     * @param string $key
     * @return array
     */
    public function getSerializedData(
        SerializerInterface $serializer,
        string $key
    ): array
    {
        if (is_string($this->getData($key))) {
            return $serializer->unserialize($this->getData($key));
        } elseif ($this->getData($key) === null) {
            return [];
        }

        return is_array($this->getData($key)) ? $this->getData($key) : [];
    }

    /**
     * @param SerializerInterface $serializer
     * @param string $key
     * @param array $data
     * @return DataObject
     */
    public function setSerializedData(
        SerializerInterface $serializer,
        string $key,
        array $data
    ): DataObject
    {
        return $this->setData($key, $serializer->serialize($data));
    }

    /**
     * @param SerializerInterface $serializer
     * @return void
     */
    public function serializeDataBeforeSave(SerializerInterface $serializer): void
    {
        foreach ($this->getSerializableFields() as $serializableField) {
            $data = $this->getData($serializableField);

            if (is_array($data)) {
                $this->setSerializedData($serializer, $serializableField, $data);
            }
        }
    }
}