<?php

namespace EventStreamApi;

use ApiPlatform\Core\Operation\PathSegmentNameGeneratorInterface;
use Doctrine\Inflector\Inflector;

/**
 * A path segment generator that produces camelCase resource names.
 */
final class PathSegmentGenerator implements PathSegmentNameGeneratorInterface
{
    private Inflector $inflector;

    public function __construct(Inflector $inflector)
    {
        $this->inflector = $inflector;
    }

    public function getSegmentName(string $name, bool $collection = true): string
    {
        return $collection ? lcfirst($this->inflector->pluralize($name)) : lcfirst($name);
    }
}