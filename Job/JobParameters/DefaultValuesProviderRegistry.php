<?php
/**
 * User: Andreas Penz <office@dopa.media>
 * Date: 04.10.17
 */

namespace Dopamedia\Batch\Job\JobParameters;

use Dopamedia\PhpBatch\Job\JobParameters\DefaultValuesProviderInterface;
use Dopamedia\PhpBatch\Job\JobParameters\DefaultValuesProviderRegistryInterface;
use Dopamedia\PhpBatch\Job\JobParameters\NonExistingDefaultValuesProviderException;
use Dopamedia\PhpBatch\JobInterface;

/**
 * Class DefaultValuesProviderRegistry
 * @package Dopamedia\Batch\Job\JobParameters
 */
class DefaultValuesProviderRegistry implements DefaultValuesProviderRegistryInterface
{
    /**
     * @var DefaultValuesProviderInterface[]
     */
    private $defaultValuesProviders;

    /**
     * DefaultValuesProviderRegistry constructor.
     * @param DefaultValuesProviderInterface[] $defaultValuesProviders
     */
    public function __construct(array $defaultValuesProviders)
    {
        $this->defaultValuesProviders = $defaultValuesProviders;
    }

    /**
     * @inheritDoc
     */
    public function get(JobInterface $job): DefaultValuesProviderInterface
    {
        foreach ($this->defaultValuesProviders as $defaultValuesProvider) {
            if ($defaultValuesProvider->supports($job) === true) {
                return $defaultValuesProvider;
            }
        }

        throw new NonExistingDefaultValuesProviderException(
            sprintf('No default values provider has been defined for the Job "%s"', $job->getName())
        );
    }
}