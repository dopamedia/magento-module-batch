<?php
/**
 * User: Andreas Penz <office@dopa.media>
 * Date: 28.09.17
 */

namespace Dopamedia\Batch\Console\Command;

use Dopamedia\Batch\Api\JobInstanceRepositoryInterface;
use Dopamedia\PhpBatch\ExitStatus;
use Dopamedia\PhpBatch\Job\JobParameters\ValidatorProviderResult;
use Dopamedia\PhpBatch\Job\JobParametersValidator;
use Dopamedia\PhpBatch\Job\JobRegistryInterface;
use Dopamedia\PhpBatch\Job\JobParametersFactory;
use Dopamedia\PhpBatch\Repository\JobRepositoryInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputOption;
use Magento\Framework\Console\Cli;
use Symfony\Component\Validator\ConstraintViolationListInterface;

/**
 * Class JobExecuteCommand
 * @package Dopamedia\Batch\Console\Command
 */
class JobExecuteCommand extends Command
{
    private const ARGUMENT_NAME_CODE = 'code';
    private const OPTION_NAME_CONFIG = 'config';
    private const OPTION_NAME_VERBOSE = 'verbose';

    /**
     * @var JobInstanceRepositoryInterface
     */
    private $jobInstanceRepository;

    /**
     * @var JobRegistryInterface
     */
    private $jobRegistry;

    /**
     * @var JobRepositoryInterface
     */
    private $jobRepository;

    /**
     * @var JobParametersFactory
     */
    private $jobParametersFactory;

    /**
     * @var JobParametersValidator
     */
    private $jobParametersValidator;

    /**
     * JobExecuteCommand constructor.
     * @param JobInstanceRepositoryInterface $jobInstanceRepository
     * @param JobRegistryInterface $jobRegistry
     * @param JobRepositoryInterface $jobRepository
     * @param JobParametersFactory $jobParametersFactory
     * @param JobParametersValidator $jobParametersValidator
     */
    public function __construct(
        JobInstanceRepositoryInterface $jobInstanceRepository,
        JobRegistryInterface $jobRegistry,
        JobRepositoryInterface $jobRepository,
        JobParametersFactory $jobParametersFactory,
        JobParametersValidator $jobParametersValidator
    )
    {
        $this->jobInstanceRepository = $jobInstanceRepository;
        $this->jobRegistry = $jobRegistry;
        $this->jobRepository = $jobRepository;
        $this->jobParametersFactory = $jobParametersFactory;
        $this->jobParametersValidator = $jobParametersValidator;
        parent::__construct();
    }

    /**
     * @inheritDoc
     */
    protected function configure()
    {
        $this
            ->setName('batch:job:execute')
            ->setDescription('Launch a registered job instance')
            ->addArgument(
                self::ARGUMENT_NAME_CODE,
                InputArgument::REQUIRED,
                'Job instance code'
            )
            ->addOption(
                self::OPTION_NAME_CONFIG,
                null,
                InputOption::VALUE_REQUIRED,
                'Override job configuration (formatted as json. ie: ' .
                'php bin/magento batch:job:execute --config "{\"filePath\":\"/tmp/foo.csv\"}" acme_product_import)'
            );
    }

    /**
     * @param InputInterface $input
     * @param OutputInterface $output
     * @return int
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $code = $input->getArgument(self::ARGUMENT_NAME_CODE);
        $jobInstance = $this->jobInstanceRepository->getByCode($code);
        $job = $this->jobRegistry->getJob($jobInstance->getJobName());
        $rawParameters = $jobInstance->getRawParameters();
        if ($config = $input->getOption(self::OPTION_NAME_CONFIG)) {
            $rawParameters = array_merge($rawParameters ?? [], $this->decodeConfiguration($config));
        }

        $jobParameters = $this->jobParametersFactory->create($job, $rawParameters);
        $validationErrorMessages = $this->jobParametersValidator->validate($job, $jobParameters);

        if ($validationErrorMessages->hasMessages() === true) {
            throw new \RuntimeException(
                sprintf(
                    'Job instance "%s" running the job "%s" with parameters "%s" is invalid because of "%s"',
                    $code,
                    $job->getName(),
                    print_r($jobParameters->all(), true),
                    $validationErrorMessages->__toString()
                )
            );
        }

        $jobExecution = $this->jobRepository->createJobExecution($jobInstance, $jobParameters);
        $jobExecution->setJobInstance($jobInstance);
        $jobExecution->setPid(getmygid());

        $job->execute($jobExecution);

        $this->jobRepository->updateJobExecution($jobExecution);

        $verbose = $input->getOption(self::OPTION_NAME_VERBOSE);

        if ($jobExecution->getExitStatus()->getExitCode() === ExitStatus::COMPLETED) {
            $nbWarnings = 0;

            foreach ($jobExecution->getStepExecutions() as $stepExecution) {
                $nbWarnings += count($stepExecution->getWarnings());
                if ($verbose) {
                    foreach ($stepExecution->getWarnings() as $warning) {
                        $output->writeln(sprintf('<comment>%s</comment>', $warning->getReason()));
                    }
                }
            }

            if ($nbWarnings === 0) {
                $output->writeln(
                    sprintf(
                        '<info>%s has been successfully executed.</info>',
                        $jobInstance->getCode()
                    )
                );

                return Cli::RETURN_SUCCESS;
            } else {
                $output->writeln(
                    sprintf(
                        '<comment>%s has been executed with %d warnings.</comment>',
                        $jobInstance->getCode(),
                        $nbWarnings
                    )
                );

                return Cli::RETURN_FAILURE;
            }
        } else {
            $output->writeln('<error>An error occurred during the execution.</error>');
            $output->writeln(sprintf('<error>ExitStatus: %s</error>', $jobExecution->getExitStatus()));
            $this->writeExceptions($output, $jobExecution->getFailureExceptions(), $verbose);
            foreach ($jobExecution->getStepExecutions() as $stepExecution) {
                $this->writeExceptions($output, $stepExecution->getFailureExceptions(), $verbose);
            }

            return Cli::RETURN_FAILURE;
        }
    }

    /**
     * @param OutputInterface $output
     * @param array $exceptions
     * @param bool $verbose
     * @return void
     */
    private function writeExceptions(OutputInterface $output, array $exceptions, bool $verbose): void
    {
        foreach ($exceptions as $exception) {
            $output->write(
                sprintf(
                    '<error>Error #%s in class %s: %s</error>',
                    $exception['code'],
                    $exception['class'],
                    strtr($exception['message'], $exception['messageParameters'])
                ),
                true
            );
            if ($verbose) {
                $output->write(sprintf('<error>%s</error>', $exception['trace']), true);
            }
        }
    }

    /**
     * @param string $data
     * @return array
     */
    private function decodeConfiguration(string $data): array
    {
        $config = json_decode($data, true);
        switch (json_last_error()) {
            case JSON_ERROR_DEPTH:
                $error = 'Maximum stack depth exceeded';
                break;
            case JSON_ERROR_STATE_MISMATCH:
                $error = 'Underflow or the modes mismatch';
                break;
            case JSON_ERROR_CTRL_CHAR:
                $error = 'Unexpected control character found';
                break;
            case JSON_ERROR_SYNTAX:
                $error = 'Syntax error, malformed JSON';
                break;
            case JSON_ERROR_UTF8:
                $error = 'Malformed UTF-8 characters, possibly incorrectly encoded';
                break;
            default:
                return $config;
        }

        throw new \InvalidArgumentException($error);
    }
}