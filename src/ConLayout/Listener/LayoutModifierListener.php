<?php
namespace ConLayout\Listener;

use ConLayout\AssetPreparer\AssetPreparerInterface;
use ConLayout\Debugger;
use ConLayout\LayoutManagerInterface;
use ConLayout\Service\LayoutService;
use Zend\EventManager\EventManagerInterface;
use Zend\EventManager\ListenerAggregateInterface;
use Zend\EventManager\ListenerAggregateTrait;
use Zend\Mvc\MvcEvent;
use Zend\View\HelperPluginManager;
use Zend\View\Model\ViewModel;

/**
 * @package ConLayout
 * @author Cornelius Adams (conlabz GmbH) <cornelius.adams@conlabz.de>
 */
class LayoutModifierListener
    implements ListenerAggregateInterface
{
    use ListenerAggregateTrait;

    const DEBUG_CSS = '/css/con-layout.css';
            
    /**
     *
     * @var LayoutManagerInterface
     */
    protected $layoutManager;
    
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
     *
     * @var Debugger
     */
    protected $debugger;

    /**
     *
     * @var int
     */
    protected static $anonymousSuffix = 1;

    /**
     * 
     * @param LayoutManagerInterface $layoutService
     */
    public function __construct(
        LayoutManagerInterface $layoutManager,
        HelperPluginManager $viewHelperManager,
        Debugger $debugger,
        $helperConfig = []
    )
    {
        $this->layoutManager     = $layoutManager;
        $this->viewHelperManager = $viewHelperManager;
        $this->debugger          = $debugger;
        $this->helperConfig      = $helperConfig;
    }
    
    /**
     * 
     * @param EventManagerInterface $events
     */
    public function attach(EventManagerInterface $events)
    {
        $this->listeners[] = $events->attach(MvcEvent::EVENT_RENDER, array($this, 'injectBlocks'));
        $this->listeners[] = $events->attach(MvcEvent::EVENT_RENDER, array($this, 'setLayoutTemplate'));
        $this->listeners[] = $events->attach(MvcEvent::EVENT_RENDER, array($this, 'applyHelpers'));
    }
    
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
    private function prepareHelperValue($value, $helper)
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
        
    /**
     * set configured layout template if no template was set
     * e.g. through controller layout plugin
     * 
     * @param MvcEvent $e
     * @return LayoutModifierListener
     */
    public function setLayoutTemplate(MvcEvent $e)
    {
        /* @var $layout ViewModel */
        $layout = $e->getViewModel();
        $template = $layout->getTemplate();
        if ($template === '') {
            $layout->setTemplate($this->layoutService->getLayoutTemplate());
        }
        return $this;
    }
    
    /**
     * 
     * @param MvcEvent $e
     * @return LayoutModifierListener
     */
    public function injectBlocks(MvcEvent $e)
    {
        /* @var $layout ViewModel */
        $layout = $e->getViewModel();
        if ($layout->terminate()) {
            return;
        }

        foreach ($layout->getChildren() as $layoutChild) {
            $blockId = $this->determineAnonymousBlockId($layoutChild);
            $layoutChild->setVariable(
                'nameInLayout',
                $blockId
            );
            $layoutChild->setVariable('blockType', 'custom');
            $this->layoutManager->addBlock($blockId, $layoutChild);
        }
        $layout->clearChildren();

        $this->layoutManager
            ->loadLayout()
            ->injectBlocks($layout);

        if ($this->debugger->isEnabled()) {
            $this->viewHelperManager->get('headlink')
                ->appendStylesheet(self::DEBUG_CSS);
        }
        return $this;
    }

    /**
     *
     * @param ViewModel $viewModel
     * @return string
     */
    protected function determineAnonymousBlockId(ViewModel $viewModel)
    {
        $blockName = $viewModel->getVariable(
            'nameInLayout',
            sprintf(
                'anonymous.%s.%s',
                $viewModel->captureTo(),
                self::$anonymousSuffix++
            )
        );
        return $blockName;
    }
    
    /**
     * retrieve helper config
     * 
     * @return array
     */
    public function getHelperConfig()
    {
        return $this->helperConfig;
    }

    /**
     * 
     * @param array $helperConfig
     * @return LayoutModifierListener
     */
    public function setHelperConfig(array $helperConfig)
    {
        $this->helperConfig = $helperConfig;
        return $this;
    }
    
    /**
     * 
     * @param string $helper view helper
     * @param AssetPreparerInterface $assetPreparer
     * @return ActionHandlesListener
     */
    public function addAssetPreparer($helper, AssetPreparerInterface $assetPreparer)
    {
        $this->assetPreparers[$helper][] = $assetPreparer;
        return $this;
    }

    /**
     *
     * @return LayoutService
     */
    public function getLayoutService()
    {
        return $this->layoutService;
    }
}
