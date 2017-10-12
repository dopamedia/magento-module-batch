<?php
/**
 * User: Andreas Penz <office@dopa.media>
 * Date: 12.10.17
 */

namespace Dopamedia\Batch\Model\Import;

/**
 * Class ArraySource
 * @package Dopamedia\Batch\Model\Import
 */
class ArraySource extends \Magento\ImportExport\Model\Import\AbstractSource
{
    /**
     * @var array
     */
    private $data;

    /**
     * @var int
     */
    private $position;

    /**
     * ArraySource constructor.
     * @param array $data
     */
    public function __construct(array $data)
    {
        $this->data = $data;
        $this->position = 0;
        parent::__construct(array_keys($this->current()));
    }

    /**
     * @inheritDoc
     */
    public function current()
    {
        return $this->data[$this->position];
    }

    /**
     * @inheritDoc
     */
    public function next()
    {
        ++$this->position;
    }

    /**
     * @inheritDoc
     */
    public function key()
    {
        return $this->position;
    }

    /**
     * @inheritDoc
     */
    public function valid()
    {
        return isset($this->data[$this->position]);
    }

    /**
     * @inheritDoc
     */
    public function rewind()
    {
        $this->position = 0;
    }

    /**
     * @inheritDoc
     */
    public function seek($position)
    {
        $this->position = $position;

        if (!$this->valid()) {
            throw new \OutOfBoundsException(sprintf('invalid seek position %s', $position));
        }
    }

    /**
     * @inheritDoc
     */
    public function getColNames()
    {
        $colNames = array();
        foreach ($this->data as $row) {
            foreach (array_keys($row) as $key) {
                if (!is_numeric($key) && !isset($colNames[$key])) {
                    $colNames[$key] = $key;
                }
            }
        }

        return $colNames;
    }

    /**
     * @inheritDoc
     */
    protected function _getNextRow()
    {
        $this->next();

        return $this->current();
    }

}