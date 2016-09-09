<?php

namespace tests\unit\TomPHP\ConfigServiceProvider\FileReader;

use InvalidArgumentException;
use PHPUnit_Framework_TestCase;
use tests\support\TestFileCreator;
use TomPHP\ConfigServiceProvider\Exception\UnknownFileTypeException;
use TomPHP\ConfigServiceProvider\FileReader\JSONFileReader;
use TomPHP\ConfigServiceProvider\FileReader\PHPFileReader;
use TomPHP\ConfigServiceProvider\FileReader\ReaderFactory;

final class ReaderFactoryTest extends PHPUnit_Framework_TestCase
{
    use TestFileCreator;

    /**
     * @var ReaderFactory
     */
    private $factory;

    public function setUp()
    {
        $this->factory = new ReaderFactory([
            '.php'  => PHPFileReader::class,
            '.json' => JSONFileReader::class,
        ]);
    }

    public function testCreatesAReader()
    {
        $this->createTestFile('test.php');

        $reader = $this->factory->create($this->getTestPath('test.php'));

        $this->assertInstanceOf(PHPFileReader::class, $reader);
    }

    public function testCreatesAnotherReader()
    {
        $this->createTestFile('test.json');

        $reader = $this->factory->create($this->getTestPath('test.json'));

        $this->assertInstanceOf(JSONFileReader::class, $reader);
    }

    public function testReturnsTheSameReaderForTheSameFileType()
    {
        $this->createTestFile('test1.php');
        $this->createTestFile('test2.php');

        $reader1 = $this->factory->create($this->getTestPath('test1.php'));
        $reader2 = $this->factory->create($this->getTestPath('test2.php'));

        $this->assertSame($reader1, $reader2);
    }

    public function testItThrowsIfTheArgumentIsNotAFileName()
    {
        $this->expectException(InvalidArgumentException::class);

        $this->factory->create('missing-file.xxx');
    }

    public function testItThrowsIfThereIsNoRegisteredReaderForGivenFileType()
    {
        $this->createTestFile('test.unknown');

        $this->expectException(UnknownFileTypeException::class);

        $this->factory->create($this->getTestPath('test.unknown'));
    }
}
