<?php
/**
 * User: Andreas Penz <office@dopa.media>
 * Date: 02.10.17
 */

namespace Dopamedia\Batch\Model;

use Magento\Framework\DataObject;
use Magento\Framework\Serialize\SerializerInterface;

/**
 * Interface SerializableFieldsInterface
 * @package Dopamedia\Batch\Model
 */
interface SerializableFieldsInterface
{
    /**
     * @return string[]
     */
    public function getSerializableFields(): array;

    /**
     * @param SerializerInterface $serializer
     * @param string $key
     * @return array
     */
    public function getSerializedData(SerializerInterface $serializer, string $key): array;

    /**
     * @param SerializerInterface $serializer
     * @param string $key
     * @param array $data
     * @return DataObject
     */
    public function setSerializedData(SerializerInterface $serializer, string $key, array $data): DataObject;

    /**
     * @param SerializerInterface $serializer
     * @return void
     */
    public function serializeDataBeforeSave(SerializerInterface $serializer): void;
}