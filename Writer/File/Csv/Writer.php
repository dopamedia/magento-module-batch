<?php
/**
 * User: Andreas Penz <office@dopa.media>
 * Date: 16.10.17
 */

namespace Dopamedia\Batch\Writer\File\Csv;

use Box\Spout\Writer\CSV\WriterFactory as CSVWriterFactory;
use Box\Spout\Writer\CSV\Writer as CSVWriter;
use Box\Spout\Common\Helper\GlobalFunctionsHelper;
use Dopamedia\Batch\Writer\File\AbstractFileWriter;
use Dopamedia\PhpBatch\Item\ItemWriterInterface;
use Dopamedia\PhpBatch\Step\StepExecutionAwareInterface;
use Symfony\Component\Filesystem\Filesystem;

/**
 * Class Writer
 * @package Dopamedia\Batch\Writer\File\Csv
 */
class Writer extends AbstractFileWriter implements
    ItemWriterInterface,
    StepExecutionAwareInterface
{
    /**
     * @var CSVWriterFactory
     */
    private $csvWriterFactory;

    /**
     * @var GlobalFunctionsHelper
     */
    private $globalFunctionsHelper;

    /**
     * Writer constructor.
     * @param Filesystem $filesystem
     * @param CSVWriterFactory $csvWriterFactory
     * @param GlobalFunctionsHelper $globalFunctionsHelper
     */
    public function __construct(
        Filesystem $filesystem,
        CSVWriterFactory $csvWriterFactory,
        GlobalFunctionsHelper $globalFunctionsHelper
    )
    {
        parent::__construct($filesystem);
        $this->csvWriterFactory = $csvWriterFactory;
        $this->globalFunctionsHelper = $globalFunctionsHelper;
    }

    /**
     * @inheritDoc
     */
    public function write(array $items)
    {
        $exportDirectory = dirname($this->getPath());

        if ($this->filesystem->exists($exportDirectory) === false) {
            $this->filesystem->mkdir($exportDirectory);
        }

        $csvWriter = $this->createCsvWriter();
        $csvWriter->openToFile($this->getPath());
        $csvWriter->addRows($items);
        $csvWriter->close();
    }

    /**
     * @return CsvWriter
     */
    private function createCsvWriter(): CsvWriter
    {
        /** @var CSVWriter $csvWriter */
        $csvWriter = $this->csvWriterFactory->create();
        $csvWriter->setGlobalFunctionsHelper($this->globalFunctionsHelper);

        return $csvWriter;
    }
}