<?php
/**
 * User: Andreas Penz <office@dopa.media>
 * Date: 01.10.17
 */

namespace Dopamedia\Batch\Test\Unit\Reader\File;

use Box\Spout\Reader\ReaderFactory;
use Dopamedia\Batch\Reader\File\FileIterator;
use Symfony\Component\Filesystem\Exception\FileNotFoundException;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\Filesystem\FilesystemFactory;
use PHPUnit\Framework\TestCase;

class FileIteratorTest extends TestCase
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

        $this->filePath = realpath(__DIR__) . '/FileIteratorTest/_files/dummy.csv';
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

        new FileIterator(
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

        new FileIterator(
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

        $fileIterator = new FileIterator(
            'csv',
            $this->filePath,
            ['reader_options' => ['fieldDelimiter' => ';']],
            $this->filesystemFactoryMock
        );

        $this->assertEquals(['header', 'row'], $fileIterator->current());

        $fileIterator->next();
        $fileIterator->next();
        $fileIterator->next();

        $this->assertNull($fileIterator->current());
    }

    public function testKey()
    {
        $this->filesystemFactoryMock->expects($this->once())
            ->method('create')
            ->willReturn($this->filesystemMock);

        $fileIterator = new FileIterator(
            'csv',
            $this->filePath,
            ['reader_options' => ['fieldDelimiter' => ';']],
            $this->filesystemFactoryMock
        );

        $fileIterator->rewind();
        $fileIterator->next();

        $this->assertEquals(2, $fileIterator->key());
    }

    public function testValid()
    {
        $this->filesystemFactoryMock->expects($this->once())
            ->method('create')
            ->willReturn($this->filesystemMock);

        $fileIterator = new FileIterator(
            'csv',
            $this->filePath,
            ['reader_options' => ['fieldDelimiter' => ';']],
            $this->filesystemFactoryMock
        );

        $fileIterator->rewind();
        $fileIterator->next();

        $this->assertTrue($fileIterator->valid());

        $fileIterator->next();
        $fileIterator->next();

        $this->assertFalse($fileIterator->valid());
    }

    public function testRewind()
    {
        $this->filesystemFactoryMock->expects($this->once())
            ->method('create')
            ->willReturn($this->filesystemMock);

        $fileIterator = new FileIterator(
            'csv',
            $this->filePath,
            ['reader_options' => ['fieldDelimiter' => ';']],
            $this->filesystemFactoryMock
        );

        $fileIterator->rewind();

        $this->assertEquals(['header', 'row'], $fileIterator->current());

        $fileIterator->next();
        $fileIterator->rewind();

        $this->assertEquals(['header', 'row'], $fileIterator->current());
    }

    public function testGetHeaders()
    {
        $this->filesystemFactoryMock->expects($this->once())
            ->method('create')
            ->willReturn($this->filesystemMock);

        $fileIterator = new FileIterator(
            'csv',
            $this->filePath,
            ['reader_options' => ['fieldDelimiter' => ';']],
            $this->filesystemFactoryMock
        );

        $this->assertEquals(['header', 'row'], $fileIterator->getHeaders());
    }

}
