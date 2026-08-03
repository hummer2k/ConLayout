<?php

declare(strict_types=1);

namespace ConLayout\Filter;

use Psr\Container\ContainerInterface;
use Laminas\ServiceManager\Factory\FactoryInterface;
use Laminas\ServiceManager\MutableCreationOptionsInterface;

class TranslateFilterFactory implements FactoryInterface
{
    /**
     * @param $requestedName
     * @param array $options
     */
    public function __invoke(ContainerInterface $container, $requestedName, ?array $options = null): TranslateFilter
    {
        $translator = $container->get('MvcTranslator');
        return new TranslateFilter($translator);
    }
}
