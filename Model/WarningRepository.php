<?php
/**
 * User: Andreas Penz <office@dopa.media>
 * Date: 28.09.17
 */

namespace Dopamedia\Batch\Model;

use Dopamedia\Batch\Api\WarningRepositoryInterface;
use Dopamedia\Batch\Model\ResourceModel\Warning as ResourceWarning;
use Dopamedia\Batch\Model\WarningFactory;
use Dopamedia\PhpBatch\WarningInterface;
use Magento\Framework\Exception\CouldNotSaveException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Phrase;
use Magento\Rule\Model\AbstractModel;

/**
 * Class WarningRepository
 * @package Dopamedia\Batch\Model
 */
class WarningRepository implements WarningRepositoryInterface
{
    /**
     * @var ResourceWarning
     */
    private $resource;

    /**
     * @var \Dopamedia\Batch\Model\WarningFactory
     */
    private $warningFactory;

    /**
     * WarningRepository constructor.
     * @param ResourceWarning $resource
     * @param \Dopamedia\Batch\Model\WarningFactory $warningFactory
     */
    public function __construct(
        ResourceWarning $resource,
        WarningFactory $warningFactory
    )
    {
        $this->resource = $resource;
        $this->warningFactory = $warningFactory;
    }

    /**
     * @param int $warningId
     * @return WarningInterface
     * @throws NoSuchEntityException
     */
    public function getById(int $warningId): WarningInterface
    {
        /** @var Warning $waring */
        $waring = $this->warningFactory->create();
        $this->resource->load($waring, $warningId);
        if ($waring->getId() === null) {
            throw new NoSuchEntityException(
                new Phrase('Warning with id "%1" does not exist.', [$warningId])
            );
        }

        return $waring;
    }

    /**
     * @param WarningInterface|AbstractModel $warning
     * @return WarningInterface
     * @throws CouldNotSaveException
     */
    public function save(WarningInterface $warning): WarningInterface
    {
        try {
            $this->resource->save($warning);
        } catch (\Exception $e) {
            throw new CouldNotSaveException(__($e->getMessage()));
        }

        return $warning;
    }
}