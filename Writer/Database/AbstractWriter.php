<?php
/**
 * User: Andreas Penz <office@dopa.media>
 * Date: 02.10.17
 */

namespace Dopamedia\Batch\Writer\Database;

use Dopamedia\PhpBatch\Step\StepExecutionAwareInterface;
use Dopamedia\PhpBatch\Step\StepExecutionAwareTrait;

/**
 * Class AbstractWriter
 * @package Dopamedia\Batch\Writer\Database
 */
abstract class AbstractWriter implements StepExecutionAwareInterface
{
    use StepExecutionAwareTrait;
}