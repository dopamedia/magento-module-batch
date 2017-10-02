<?php
/**
 * User: Andreas Penz <office@dopa.media>
 * Date: 01.10.17
 */

namespace Dopamedia\Batch\Reader\File;

use Box\Spout\Reader\ReaderFactory;
use Box\Spout\Reader\ReaderInterface;
use Box\Spout\Reader\IteratorInterface;
use Symfony\Component\Filesystem\Exception\FileNotFoundException;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\Filesystem\FilesystemFactory;

/**
 * Class FlatFileIterator
 * @package Dopamedia\Batch\Reader\File
 */
class FlatFileIterator implements FileIteratorInterface
{
    /**
     * @var string
     */
    private $type;

    /**
     * @var string
     */
    private $filePath;

    /**
     * @var Filesystem
     */
    private $filesystem;

    /**
     * @var ReaderInterface
     */
    protected $reader;

    /**
     * @var IteratorInterface
     */
    protected $rows;

    /**
     * @var array
     */
    protected $headers;

    /**
     * FileIterator constructor.
     * @param string $type
     * @param string $filePath
     * @param array $options
     * @param FilesystemFactory $filesystemFactory
     * @throws \Box\Spout\Common\Exception\IOException
     * @throws \Box\Spout\Common\Exception\UnsupportedTypeException
     * @throws \Box\Spout\Reader\Exception\ReaderNotOpenedException
     */
    public function __construct(
        string $type,
        string $filePath,
        array $options = [],
        FilesystemFactory $filesystemFactory
    )
    {
        $this->type = $type;
        $this->filePath = $filePath;
        $this->filesystem = $filesystemFactory->create();

        if ($this->filesystem->exists($filePath) === false) {
            throw new FileNotFoundException(sprintf('File "%s" could not be found', $this->filePath));
        }

        $this->reader = ReaderFactory::create($type);
        if (isset($options['reader_options'])) {
            $this->setReaderOptions($options['reader_options']);
        }
        $this->reader->open($this->filePath);
        $this->reader->getSheetIterator()->rewind();

        $sheet = $this->reader->getSheetIterator()->current();
        $sheet->getRowIterator()->rewind();

        $this->headers = $sheet->getRowIterator()->current();
        $this->rows = $sheet->getRowIterator();
    }

    /**
     * @inheritDoc
     */
    public function current()
    {
        $data = $this->rows->current();

        if (!$this->valid() || null === $data || empty($data)) {
            $this->rewind();

            return null;
        }

        return $data;
    }

    /**
     * @inheritDoc
     */
    public function next()
    {
        $this->rows->next();
    }

    /**
     * @inheritDoc
     */
    public function key()
    {
        return $this->rows->key();
    }

    /**
     * @inheritDoc
     */
    public function valid()
    {
        return $this->rows->valid();
    }

    /**
     * @inheritDoc
     */
    public function rewind()
    {
        $this->rows->rewind();
    }

    /**
     * @inheritDoc
     */
    public function getHeaders(): array
    {
        return $this->headers;
    }

    /**
     * @param array $readerOptions
     * @return void
     * @throws \InvalidArgumentException
     */
    protected function setReaderOptions(array $readerOptions = []): void
    {
        foreach ($readerOptions as $name => $option) {
            $setter = 'set' . ucfirst($name);
            if (method_exists($this->reader, $setter)) {
                $this->reader->$setter($option);
            } else {
                $message = sprintf('Option "%s" does not exist in reader "%s"', $setter, get_class($this->reader));
                throw new \InvalidArgumentException($message);
            }
        }
    }
}