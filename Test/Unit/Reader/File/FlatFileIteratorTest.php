<?php
/**
 * User: Andreas Penz <office@dopa.media>
 * Date: 01.10.17
 */

namespace Dopamedia\Batch\Test\Unit\Reader\File;

use Dopamedia\Batch\Reader\File\FlatFileIterator;
use Symfony\Component\Filesystem\Exception\FileNotFoundException;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\Filesystem\FilesystemFactory;
use PHPUnit\Framework\TestCase;

class FlatFileIteratorTest extends TestCase
{
    /**
     * @var \PHPUnit_Framework_MockObject_MockObject|FilesystemFactory
     */
    protected $filesystemFactoryMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject|Filesystem
     */
    protected $filesystemMock;

    /**
     * @var string
     */
    protected $filePath;

    protected function setUp()
    {
        $this->filesystemFactoryMock = $this->getMockBuilder(FilesystemFactory::class)
            ->disableOriginalConstructor()
            ->setMethods(['create'])
            ->getMock();

        $this->filesystemMock = $this->createMock(Filesystem::class);

        $this->filePath = realpath(__DIR__) . '/FlatFileIteratorTest/_files/dummy.csv';
    }

    public function testConstructWithAbsentFile()
    {
        $this->filesystemFactoryMock->expects($this->once())
            ->method('create')
            ->willReturn($this->filesystemMock);

        $this->filesystemMock->expects($this->once())
            ->method('exists')
            ->with('absent.csv')
            ->willReturn(false);

        $this->expectException(FileNotFoundException::class);
        $this->expectExceptionMessage('File "absent.csv" could not be found');

        new FlatFileIterator(
            '',
            'absent.csv',
            [],
            $this->filesystemFactoryMock
        );
    }

    public function testConstructWithUnknownReaderOption()
    {
        $this->filesystemFactoryMock->expects($this->once())
            ->method('create')
            ->willReturn($this->filesystemMock);

        $this->expectExceptionMessage(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Option "setUnknown" does not exist in reader "Box\Spout\Reader\CSV\Reader"');

        new FlatFileIterator(
            'csv',
            $this->filePath,
            ['reader_options' => ['unknown' => true]],
            $this->filesystemFactoryMock
        );
    }

    public function testCurrent()
    {
        $this->filesystemFactoryMock->expects($this->once())
            ->method('create')
            ->willReturn($this->filesystemMock);

        $flatFileIterator = new FlatFileIterator(
            'csv',
            $this->filePath,
            ['reader_options' => ['fieldDelimiter' => ';']],
            $this->filesystemFactoryMock
        );

        $this->assertEquals(['header', 'row'], $flatFileIterator->current());

        $flatFileIterator->next();
        $flatFileIterator->next();
        $flatFileIterator->next();

        $this->assertNull($flatFileIterator->current());
    }

    public function testKey()
    {
        $this->filesystemFactoryMock->expects($this->once())
            ->method('create')
            ->willReturn($this->filesystemMock);

        $flatFileIterator = new FlatFileIterator(
            'csv',
            $this->filePath,
            ['reader_options' => ['fieldDelimiter' => ';']],
            $this->filesystemFactoryMock
        );

        $flatFileIterator->rewind();
        $flatFileIterator->next();

        $this->assertEquals(2, $flatFileIterator->key());
    }

    public function testValid()
    {
        $this->filesystemFactoryMock->expects($this->once())
            ->method('create')
            ->willReturn($this->filesystemMock);

        $flatFileIterator = new FlatFileIterator(
            'csv',
            $this->filePath,
            ['reader_options' => ['fieldDelimiter' => ';']],
            $this->filesystemFactoryMock
        );

        $flatFileIterator->rewind();
        $flatFileIterator->next();

        $this->assertTrue($flatFileIterator->valid());

        $flatFileIterator->next();
        $flatFileIterator->next();

        $this->assertFalse($flatFileIterator->valid());
    }

    public function testRewind()
    {
        $this->filesystemFactoryMock->expects($this->once())
            ->method('create')
            ->willReturn($this->filesystemMock);

        $flatFileIterator = new FlatFileIterator(
            'csv',
            $this->filePath,
            ['reader_options' => ['fieldDelimiter' => ';']],
            $this->filesystemFactoryMock
        );

        $flatFileIterator->rewind();

        $this->assertEquals(['header', 'row'], $flatFileIterator->current());

        $flatFileIterator->next();
        $flatFileIterator->rewind();

        $this->assertEquals(['header', 'row'], $flatFileIterator->current());
    }

    public function testGetHeaders()
    {
        $this->filesystemFactoryMock->expects($this->once())
            ->method('create')
            ->willReturn($this->filesystemMock);

        $flatFileIterator = new FlatFileIterator(
            'csv',
            $this->filePath,
            ['reader_options' => ['fieldDelimiter' => ';']],
            $this->filesystemFactoryMock
        );

        $this->assertEquals(['header', 'row'], $flatFileIterator->getHeaders());
    }

}
