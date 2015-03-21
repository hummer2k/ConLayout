<?php
namespace ConLayout\Listener;

use ConLayout\Debugger;
use ConLayout\Service\BlocksBuilder;
use ConLayout\Service\LayoutModifier;
use ConLayout\Service\LayoutService;
use ConLayout\ValuePreparer\ValuePreparerInterface;
use Zend\EventManager\EventManagerInterface;
use Zend\EventManager\ListenerAggregateInterface;
use Zend\EventManager\ListenerAggregateTrait;
use Zend\Mvc\MvcEvent;
use Zend\View\Model\ViewModel;
use Zend\View\Renderer\PhpRenderer;

/**
 * @package ConLayout
 * @author Cornelius Adams (conlabz GmbH) <cornelius.adams@conlabz.de>
 */
class LayoutModifierListener
    implements ListenerAggregateInterface
{
    use ListenerAggregateTrait;
    
    /**
     *
     * @var LayoutService
     */
    protected $layoutService;
    
    /**
     *
     * @var BlocksBuilder
     */
    protected $blocksBuilder;
    
    /**
     *
     * @var ViewModel
     */
    protected $layout;
    
    /**
     *
     * @var LayoutModifier
     */
    protected $layoutModifier;
    
    /**
     *
     * @var PhpRenderer
     */
    protected $viewRenderer;
    
    /**
     *
     * @var array
     */
    protected $helperConfig = array();
    
    /**
     * value preparers for view helpers in format:
     *   'helperName' => array(ConLayout\ValuePreparer\ValuePreparerInterface)
     *  
     * @var array
     */
    protected $valuePreparers = array();

    /**
     *
     * @var Debugger
     */
    protected $debugger;
    
    /**
     * 
     * @param LayoutService $layoutService
     * @param BlocksBuilder $blocksBuilder
     * @param LayoutModifier $layoutModifier
     */
    public function __construct(
        LayoutService $layoutService, 
        BlocksBuilder $blocksBuilder, 
        LayoutModifier $layoutModifier,
        ViewModel $layout,
        PhpRenderer $viewRenderer,
        Debugger $debugger,
        $helperConfig = array()
    )
    {
        $this->layoutService    = $layoutService;
        $this->blocksBuilder    = $blocksBuilder;
        $this->layoutModifier   = $layoutModifier;
        $this->layout           = $layout;  
        $this->viewRenderer     = $viewRenderer;
        $this->debugger         = $debugger;
        $this->helperConfig     = $helperConfig;
    }
    
    /**
     * 
     * @param EventManagerInterface $events
     */
    public function attach(EventManagerInterface $events)
    {
        $this->listeners[] = $events->attach(MvcEvent::EVENT_RENDER, array($this, 'addBlocksToLayout'));
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
            $viewHelper = $this->viewRenderer->plugin($helper);
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
        if (!isset($this->valuePreparers[$helper])) {
            return $value;
        }
        /* @var $valuePreparer ValuePreparerInterface */
        foreach ($this->valuePreparers[$helper] as $valuePreparer) {
            $value = $valuePreparer->prepare($value);
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
    public function addBlocksToLayout(MvcEvent $e)
    {
        /* @var $layout ViewModel */
        $layout = $e->getViewModel();
        if ($layout->terminate()) {
            return;
        }
        // retrieve block config
        $blockConfig    = $this->layoutService->getBlockConfig();
        $this->blocksBuilder->setBlockConfig($blockConfig);
        // create and retrieve block instances
        $createdBlocks  = $this->blocksBuilder->create()
            ->getCreatedBlocks();
        // add blocks to layout
        if ($this->debugger->isEnabled()) {
            $this->viewRenderer->plugin('headlink')
                ->appendStylesheet('css/con-layout.css');
        }
        $this->layoutModifier->addBlocksToLayout($createdBlocks, $this->layout);
        return $this;
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
     * @param ValuePreparerInterface $valuePreparer
     * @return ActionHandlesListener
     */
    public function addValuePreparer($helper, ValuePreparerInterface $valuePreparer)
    {
        $this->valuePreparers[$helper][] = $valuePreparer;
        return $this;
    }
}
