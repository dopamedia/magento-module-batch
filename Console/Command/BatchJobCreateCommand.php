<?php
/**
 * User: Andreas Penz <office@dopa.media>
 * Date: 28.09.17
 */

namespace Dopamedia\Batch\Console\Command;

use Dopamedia\Batch\Api\JobInstanceRepositoryInterface;
use Dopamedia\Batch\Model\JobInstance;
use Dopamedia\PhpBatch\Job\JobParametersFactory;
use Dopamedia\PhpBatch\Job\JobRegistryInterface;
use Dopamedia\PhpBatch\Job\UndefinedJobException;
use Dopamedia\PhpBatch\JobInstanceInterfaceFactory;
use Magento\Framework\Exception\CouldNotSaveException;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Magento\Framework\Console\Cli;

/**
 * Class BatchJobCreateCommand
 * @package Dopamedia\Batch\Console\Command
 */
class BatchJobCreateCommand extends Command
{
    private const ARGUMENT_NAME_JOB = 'job';
    private const ARGUMENT_NAME_CODE = 'code';
    private const OPTION_NAME_CONFIG = 'config';

    /**
     * @var JobInstanceInterfaceFactory
     */
    private $jobInstanceFactory;

    /**
     * @var JobRegistryInterface
     */
    private $jobRegistry;

    /**
     * @var JobInstanceRepositoryInterface
     */
    private $jobInstanceRepository;

    /**
     * @var JobParametersFactory
     */
    private $jobParametersFactory;

    /**
     * BatchJobCreateCommand constructor.
     * @param JobInstanceInterfaceFactory $jobInstanceFactory
     * @param JobRegistryInterface $jobRegistry
     * @param JobInstanceRepositoryInterface $jobInstanceRepository
     * @param JobParametersFactory $jobParametersFactory
     */
    public function __construct(
        JobInstanceInterfaceFactory $jobInstanceFactory,
        JobRegistryInterface $jobRegistry,
        JobInstanceRepositoryInterface $jobInstanceRepository,
        JobParametersFactory $jobParametersFactory
    )
    {
        $this->jobInstanceFactory = $jobInstanceFactory;
        $this->jobRegistry = $jobRegistry;
        $this->jobInstanceRepository = $jobInstanceRepository;
        $this->jobParametersFactory = $jobParametersFactory;
        parent::__construct();
    }

    /**
     * @inheritDoc
     */
    protected function configure()
    {
        $this
            ->setName('batch:job:create')
            ->setDescription('Create a job instance')
            ->addArgument(
                self::ARGUMENT_NAME_JOB,
                InputArgument::REQUIRED,
                'Job name'
            )
            ->addArgument(
                self::ARGUMENT_NAME_CODE,
                InputArgument::REQUIRED,
                'Job instance code'
            )
            ->addOption(
                self::OPTION_NAME_CONFIG,
                null,
                InputOption::VALUE_REQUIRED,
                'Job default parameters'
            );
    }

    /**
     * @inheritDoc
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $jobName = $input->getArgument(self::ARGUMENT_NAME_JOB);
        $code = $input->getArgument(self::ARGUMENT_NAME_CODE);
        $jsonConfig = $input->getOption(self::OPTION_NAME_CONFIG);
        $rawConfig = null === $jsonConfig ? [] : json_decode($jsonConfig, true);

        /** @var JobInstance $jobInstance */
        $jobInstance = $this->jobInstanceFactory->create()
            ->setJobName($jobName)
            ->setCode($code);

        try {
            $job = $this->jobRegistry->getJob($jobInstance->getJobName());
        } catch (UndefinedJobException $e) {
            $output->writeln(sprintf('<error>%s</error>', $e->getMessage()));
            return Cli::RETURN_FAILURE;
        }

        $jobParameters = $this->jobParametersFactory->create($job, $rawConfig);
        $jobInstance->setRawParameters($jobParameters->all());

        //TODO::implement validation

        try {
            $this->jobInstanceRepository->save($jobInstance);
        } catch (CouldNotSaveException $e) {
            $output->writeln(sprintf('<error>%s</error>', $e->getMessage()));
            return Cli::RETURN_FAILURE;
        }

        $output->writeln(sprintf(
            '<info>JobInstance with id "%s" has been created</info>',
            $jobInstance->getId())
        );

        return Cli::RETURN_SUCCESS;
    }

}