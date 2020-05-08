<?php

namespace Productively\Api;

use ApiPlatform\Core\Operation\PathSegmentNameGeneratorInterface;

use Doctrine\Common\Inflector\Inflector;

/**
 * A path segment generator that produces camelCase resource names.
 */
final class PathSegmentGenerator implements PathSegmentNameGeneratorInterface
{
    /**
     * Transforms a given string to a valid path name which can be pluralized (eg. for collections).
     *
     * @param string $name usually a ResourceMetadata shortname
     *
     * @return string A string that is a part of the route name
     */
    public function getSegmentName(string $name, bool $collection = true): string
    {
        return $collection ? lcfirst(Inflector::pluralize($name)) : lcfirst($name);
    }
}