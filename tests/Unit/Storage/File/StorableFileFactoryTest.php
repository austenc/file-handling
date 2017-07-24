<?php
namespace Czim\FileHandling\Test\Unit\Storage\File;

use Czim\FileHandling\Contracts\Support\MimeTypeHelperInterface;
use Czim\FileHandling\Contracts\Support\UrlDownloaderInterface;
use Czim\FileHandling\Storage\File\RawStorableFile;
use Czim\FileHandling\Storage\File\SplFileInfoStorableFile;
use Czim\FileHandling\Storage\File\StorableFileFactory;
use Czim\FileHandling\Support\Content\RawContent;
use Czim\FileHandling\Test\TestCase;
use Mockery;
use SplFileInfo;

/**
 * Class StorableFileFactoryTest
 *
 * @uses \SplFileInfo
 * @uses \Czim\FileHandling\Support\Content\RawContent
 */
class StorableFileFactoryTest extends TestCase
{
    const XML_TEST_FILE = 'tests/resources/test.xml';


    /**
     * @test
     */
    function it_makes_a_storable_file_instance_from_spl_file_info()
    {
        $factory = new StorableFileFactory($this->getMockMimeTypeHelper(), $this->getMockDownloader());

        $path = realpath(dirname(__DIR__) . '/../../../' . static::XML_TEST_FILE);
        $info = new SplFileInfo($path);

        $file = $factory->makeFromFileInfo($info);

        static::assertInstanceOf(SplFileInfoStorableFile::class, $file);
        static::assertEquals('test.xml', $file->name());
        static::assertEquals(766, $file->size());
        static::assertEquals('application/xml', $file->mimeType());
    }

    /**
     * @test
     */
    function it_makes_a_storable_file_instance_with_custom_name_and_mimetype()
    {
        $factory = new StorableFileFactory($this->getMockMimeTypeHelper(), $this->getMockDownloader());

        $path = realpath(dirname(__DIR__) . '/../../../' . static::XML_TEST_FILE);
        $info = new SplFileInfo($path);

        $file = $factory->makeFromFileInfo($info, 'other_name.xml', 'image/gif');

        static::assertInstanceOf(SplFileInfoStorableFile::class, $file);
        static::assertEquals('other_name.xml', $file->name());
        static::assertEquals('image/gif', $file->mimeType());
    }

    /**
     * @test
     */
    function it_makes_a_storable_file_instance_from_local_path()
    {
        $factory = new StorableFileFactory($this->getMockMimeTypeHelper(), $this->getMockDownloader());

        $path = realpath(dirname(__DIR__) . '/../../../' . static::XML_TEST_FILE);

        $file = $factory->makeFromLocalPath($path);

        static::assertInstanceOf(SplFileInfoStorableFile::class, $file);
        static::assertEquals('test.xml', $file->name());
        static::assertEquals(766, $file->size());
        static::assertEquals('application/xml', $file->mimeType());
    }

    /**
     * @test
     */
    function it_makes_a_storable_file_instance_from_a_url()
    {
        $downloader = $this->getMockDownloader();

        $path = realpath(dirname(__DIR__) . '/../../../' . static::XML_TEST_FILE);

        $downloader->shouldReceive('download')
            ->with('http://test.com/test.xml?page=23')
            ->andReturn($path);

        $factory = new StorableFileFactory($this->getMockMimeTypeHelper(), $downloader);

        $file = $factory->makeFromUrl('http://test.com/test.xml?page=23');

        static::assertInstanceOf(SplFileInfoStorableFile::class, $file);
        static::assertEquals('test.xml', $file->name());
        static::assertEquals(766, $file->size());
        static::assertEquals('application/xml', $file->mimeType());
    }

    /**
     * @test
     * @expectedException \Czim\FileHandling\Exceptions\CouldNotRetrieveRemoteFileException
     */
    function it_throws_an_exception_if_url_could_not_be_downloaded_from()
    {
        $downloader = $this->getMockDownloader();

        $downloader->shouldReceive('download')
            ->with('http://test.com/test.xml')
            ->andThrow(\ErrorException::class, 'testing');

        $factory = new StorableFileFactory($this->getMockMimeTypeHelper(), $downloader);

        $factory->makeFromUrl('http://test.com/test.xml');
    }

    /**
     * @test
     */
    function it_makes_a_storable_file_from_a_datauri()
    {
        // todo
    }

    /**
     * @test
     */
    function it_makes_a_storable_file_instance_from_raw_data()
    {
        $factory = new StorableFileFactory($this->getMockMimeTypeHelper(), $this->getMockDownloader());

        $rawData = file_get_contents(realpath(dirname(__DIR__) . '/../../../' . static::XML_TEST_FILE));

        $file = $factory->makeFromRawData($rawData, 'testing.xml');

        static::assertInstanceOf(RawStorableFile::class, $file);
        static::assertEquals('testing.xml', $file->name());
        static::assertEquals(766, $file->size());
        static::assertEquals('application/xml', $file->mimeType());

        // And from a raw content instance
        $file = $factory->makeFromRawData(new RawContent($rawData), 'testing.xml');

        static::assertInstanceOf(RawStorableFile::class, $file);
        static::assertEquals('testing.xml', $file->name());
        static::assertEquals(766, $file->size());
        static::assertEquals('application/xml', $file->mimeType());
    }

    /**
     * @test
     */
    function it_marks_a_file_uploaded_with_fluent_syntax()
    {
        $factory = new StorableFileFactory($this->getMockMimeTypeHelper(), $this->getMockDownloader());

        $path = realpath(dirname(__DIR__) . '/../../../' . static::XML_TEST_FILE);
        $info = new SplFileInfo($path);

        $file = $factory->makeFromFileInfo($info);
        static::assertFalse($file->isUploaded());

        $file = $factory->uploaded()->makeFromFileInfo($info);
        static::assertTrue($file->isUploaded());

        $file = $factory->makeFromFileInfo($info);
        static::assertFalse($file->isUploaded(), 'Should not be uploaded for next call');
    }


    /**
     * @return Mockery\MockInterface|MimeTypeHelperInterface
     */
    protected function getMockMimeTypeHelper()
    {
        $mock = Mockery::mock(MimeTypeHelperInterface::class);

        $mock->shouldReceive('guessMimeTypeForPath')->andReturn('application/xml');
        $mock->shouldReceive('guessMimeTypeForContent')->andReturn('application/xml');

        return $mock;
    }

    /**
     * @return Mockery\MockInterface|UrlDownloaderInterface
     */
    protected function getMockDownloader()
    {
        return Mockery::mock(UrlDownloaderInterface::class);
    }

}