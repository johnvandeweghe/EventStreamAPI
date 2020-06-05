<?php
namespace Productively\Api;

use PHPUnit\Framework\TestCase;

class PathSegmentGeneratorTest extends TestCase
{
    public function testGetSegmentNameCamelsSingular()
    {
        $className = "GlobalTag";
        $expectedUriResource = "globalTag";

        $segmentGenerator = new PathSegmentGenerator();

        //This doesn't actually occur in our configuration, but it technically could, so we test it.
        $this->assertEquals($expectedUriResource, $segmentGenerator->getSegmentName($className, false));
    }

    public function testGetSegmentNameCamelsCollection()
    {
        $className = "GlobalTag";
        $expectedUriResource = "globalTags";

        $segmentGenerator = new PathSegmentGenerator();

        $this->assertEquals($expectedUriResource, $segmentGenerator->getSegmentName($className, true));
    }
}