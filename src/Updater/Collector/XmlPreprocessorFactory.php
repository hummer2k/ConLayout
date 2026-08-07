<?php

declare(strict_types=1);

namespace ConLayout\Updater\Collector;

use ConLayout\Options\ModuleOptions;
use ConLayout\Updater\Collector\Element\ElementInterface;
use Laminas\ServiceManager\Factory\FactoryInterface;
use Psr\Container\ContainerInterface;

class XmlPreprocessorFactory implements FactoryInterface
{
    public function __invoke(ContainerInterface $container, string $requestedName, ?array $options = null): XmlPreprocessor
    {
        /* @var $options ModuleOptions */
        $options = $container->get(ModuleOptions::class);
        $elements = [];
        foreach ($options->getXmlPreprocessor()['elements'] ?? [] as $service) {
            $element = $container->get($service);
            if (!$element instanceof ElementInterface) {
                throw new \InvalidArgumentException(sprintf(
                    'Element service %s must implement %s.',
                    (is_object($element) ? $element::class : gettype($element)),
                    ElementInterface::class,
                ));
            }
            $elements[] = $element;
        }
        return new XmlPreprocessor($elements);
    }
}
