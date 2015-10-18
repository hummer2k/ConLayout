<?php

namespace ConLayout\Filter;

use Zend\Filter\FilterInterface;
use Zend\I18n\Translator\TranslatorInterface;

/**
 * @package ConLayout
 * @author Cornelius Adams (conlabz GmbH) <cornelius.adams@conlabz.de>
 */
class TranslateFilter implements FilterInterface
{
    /**
     *
     * @var TranslatorInterface
     */
    protected $translator;

    /**
     *
     * @param TranslatorInterface $translator
     */
    public function __construct(TranslatorInterface $translator)
    {
        $this->translator = $translator;
    }

    /**
     *
     * @param string $value
     * @return string
     */
    public function filter($value)
    {
        return $this->translator->translate((string) $value);
    }
}
