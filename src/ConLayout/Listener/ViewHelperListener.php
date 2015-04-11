<?php

namespace ConLayout\Listener;

use ConLayout\AssetPreparer\AssetPreparerInterface;
use ConLayout\Updater\LayoutUpdaterInterface;
use Zend\Config\Config;
use Zend\Mvc\MvcEvent;
use Zend\View\HelperPluginManager;

/**
 * Listener to apply view helpers from layout structure
 *
 * @package ConLayout
 * @author Cornelius Adams (conlabz GmbH) <cornelius.adams@conlabz.de>
 */
class ViewHelperListener
{
    /**
     *
     * @var LayoutUpdaterInterface
     */
    protected $updater;

    /**
     *
     * @var HelperPluginManager
     */
    protected $viewHelperManager;

    /**
     *
     * @var array
     */
    protected $helperConfig = [];

    /**
     * asset preparers for view helpers in format:
     * 'helperName' => [
     *     AssetPreparerInterface,
     *     AssetPreparerInterface
     * ]
     *
     * @var AssetPreparerInterface[]
     */
    protected $assetPreparers = [];

    /**
     * applies view helpers
     *
     * @todo refactor
     * @param MvcEvent $e
     * @return LayoutModifierListener
     */
    public function applyViewHelpers(MvcEvent $e)
    {
        $viewHelperInstructions = $this->updater->getLayoutStructure()->get(
            LayoutUpdaterInterface::INSTRUCTION_VIEW_HELPERS
        );
        if ($viewHelperInstructions instanceof Config) {
            $viewHelperInstructions = $viewHelperInstructions->toArray();
        }
        foreach ($this->helperConfig as $helper => $config) {
            if (!isset($viewHelperInstructions[$helper])) continue;
            $defaultMethod = isset($config['defaultMethod']) ? $config['defaultMethod'] : '__invoke';
            $viewHelper = $this->viewHelperManager->get($helper);
            if (!is_array($viewHelperInstructions[$helper])) {
                $viewHelperInstructions[$helper] = array($viewHelperInstructions[$helper]);
            }
            foreach ($viewHelperInstructions[$helper] as $method => $value) {
                if (!is_string($method)) {
                    $method = (is_array($value) && isset($value['method'])) ? $value['method'] : $defaultMethod;
                }
                if (is_array($value)) {
                    $args   = isset($value['args']) ? array_values($value['args']) : $value;
                    $args[0] = $this->prepareHelperValue($args[0], $helper);
                    call_user_func_array(array($viewHelper, $method), $args);
                } else if (is_string($value)) {
                    $viewHelper->{$method}($this->prepareHelperValue($value, $helper));
                }
            }
        }
        return $this;
    }

     /**
     *
     * @param mixed $value value to prepare
     * @param string $helper view helper name
     * @return mixed
     */
    protected function prepareHelperValue($value, $helper)
    {
        if (!isset($this->assetPreparers[$helper])) {
            return $value;
        }
        /* @var $assetPreparer AssetPreparerInterface */
        foreach ($this->assetPreparers[$helper] as $assetPreparer) {
            $value = $assetPreparer->prepare($value);
        }
        return $value;
    }
}
