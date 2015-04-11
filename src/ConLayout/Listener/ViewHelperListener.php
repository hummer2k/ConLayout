<?php

namespace ConLayout\Listener;

use ConLayout\AssetPreparer\AssetPreparerInterface;
use ConLayout\LayoutInterface;
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
     * @var LayoutInterface
     */
    protected $layout;

    /**
     *
     * @var HelperPluginManager
     */
    protected $viewHelperManager;

    /**
     *
     * @var array
     */
    protected $helperConfig = array();

    /**
     * value preparers for view helpers in format:
     *   'helperName' => array(ConLayout\AssetPreparer\AssetPreparerInterface)
     *
     * @var array
     */
    protected $assetPreparers = array();

    /**
     * applies view helpers
     *
     * @param MvcEvent $e
     * @return LayoutModifierListener
     */
    public function applyHelpers(MvcEvent $e)
    {
        $layoutConfig = $this->layoutService->getLayoutConfig();
        foreach ($this->getHelperConfig() as $helper => $config) {
            if (!isset($layoutConfig[$helper])) continue;
            $defaultMethod = isset($config['defaultMethod']) ? $config['defaultMethod'] : '__invoke';
            $viewHelper = $this->viewHelperManager->get($helper);
            if (!is_array($layoutConfig[$helper])) {
                $layoutConfig[$helper] = array($layoutConfig[$helper]);
            }
            foreach ($layoutConfig[$helper] as $method => $value) {
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
