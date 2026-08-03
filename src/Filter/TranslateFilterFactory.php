<?php

declare(strict_types=1);

namespace ConLayout\Filter;

use Laminas\I18n\Translator\TranslatorInterface;
use Psr\Container\ContainerInterface;
use Laminas\ServiceManager\Factory\FactoryInterface;

class TranslateFilterFactory implements FactoryInterface
{
    public function __invoke(ContainerInterface $container, $requestedName, ?array $options = null): TranslateFilter
    {
        if ($container->has(TranslatorInterface::class)) {
            return new TranslateFilter($container->get(TranslatorInterface::class));
        }
        return new TranslateFilter(new class implements TranslatorInterface {
            public function translate($message, $textDomain = 'default', $locale = null): string
            {
                return (string) $message;
            }

            public function translatePlural(
                $singular,
                $plural,
                $number,
                $textDomain = 'default',
                $locale = null
            ): string {
                return 1 === $number ? (string) $singular : (string) $plural;
            }
        });
    }
}
