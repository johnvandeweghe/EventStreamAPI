<?php
namespace PostChat\Api;

use Doctrine\Inflector\InflectorFactory;
use PHPUnit\Framework\TestCase;

class PathSegmentGeneratorTest extends TestCase
{
    public function testGetSegmentNameCamelsSingular()
    {
        $className = "GlobalTag";
        $expectedUriResource = "globalTag";

        $segmentGenerator = new PathSegmentGenerator(InflectorFactory::create()->build());

        //This doesn't actually occur in our configuration, but it technically could, so we test it.
        self::assertEquals($expectedUriResource, $segmentGenerator->getSegmentName($className, false));
    }

    public function testGetSegmentNameCamelsCollection()
    {
        $className = "GlobalTag";
        $expectedUriResource = "globalTags";

        $segmentGenerator = new PathSegmentGenerator(InflectorFactory::create()->build());

        self::assertEquals($expectedUriResource, $segmentGenerator->getSegmentName($className, true));
    }

    public function testGetSegmentNameCamelsCollectionByDefault()
    {
        $className = "GlobalTag";
        $expectedUriResource = "globalTags";

        $segmentGenerator = new PathSegmentGenerator(InflectorFactory::create()->build());

        self::assertEquals($expectedUriResource, $segmentGenerator->getSegmentName($className));
    }
}