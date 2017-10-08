<?php
/**
 * User: Andreas Penz <office@dopa.media>
 * Date: 08.10.17
 */

namespace Dopamedia\Batch\Model;

use Dopamedia\PhpBatch\JobInstanceFactoryInterface;
use Dopamedia\PhpBatch\JobInstanceInterface;
use Magento\Framework\ObjectManagerInterface;

/**
 * Class JobInstanceFactory
 * @package Dopamedia\Batch\Model
 */
class JobInstanceFactory implements JobInstanceFactoryInterface
{
    /**
     * @var ObjectManagerInterface
     */
    private $objectManager;

    /**
     * JobInstanceFactory constructor.
     * @param ObjectManagerInterface $objectManager
     */
    public function __construct(ObjectManagerInterface $objectManager)
    {
        $this->objectManager = $objectManager;
    }

    /**
     * @inheritDoc
     */
    public function create(): JobInstanceInterface
    {
        return $this->objectManager->create(JobInstance::class);
    }

}