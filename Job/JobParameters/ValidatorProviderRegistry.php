<?php
/**
 * User: Andreas Penz <office@dopa.media>
 * Date: 04.10.17
 */

namespace Dopamedia\Batch\Job\JobParameters;

use Dopamedia\PhpBatch\Job\JobParameters;
use Dopamedia\PhpBatch\Job\JobParameters\NonExistingValidatorProviderException;
use Dopamedia\PhpBatch\Job\JobParameters\ValidatorProviderInterface;
use Dopamedia\PhpBatch\JobInterface;

/**
 * Class ValidatorProviderRegistry
 * @package Dopamedia\Batch\Job\JobParameters
 */
class ValidatorProviderRegistry implements JobParameters\ValidatorProviderRegistryInterface
{
    /**
     * @var ValidatorProviderInterface[]
     */
    private $validatorProviders;

    /**
     * ValidatorProviderRegistry constructor.
     * @param array $validatorProviders
     */
    public function __construct(array $validatorProviders = [])
    {
        $this->validatorProviders = $validatorProviders;
    }

    /**
     * @inheritDoc
     */
    public function get(JobInterface $job): ValidatorProviderInterface
    {
        foreach ($this->validatorProviders as $validatorProvider) {
            if ($validatorProvider->supports($job) === true) {
                return $validatorProvider;
            }
        }

        throw new NonExistingValidatorProviderException(
            sprintf('No validator provider has been defined for the Job "%s"', $job->getName())
        );
    }
}