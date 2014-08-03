<?php
namespace ConLayout\Service;

use Zend\View\Model\ViewModel,
    \Zend\Permissions\Acl\AclInterface,
    \Zend\Permissions\Acl\Role\RoleInterface;

/**
 * Modifier
 *
 * @author Cornelius Adams (conlabz GmbH) <cornelius.adams@conlabz.de>
 */
class LayoutModifier
{   
    /**
     *
     * @var ViewModel
     */
    protected $layout;
    
    /**
     *
     * @var array
     */
    protected $createdBlocks;
    
    /**
     *
     * @var default captureTo
     */
    protected $captureTo = 'childHtml';
    
    /**
     *
     * @var boolean
     */
    protected $isDebug = false;
    
    /**
     *
     * @var AclInterface
     */
    protected $acl;
    
    /**
     *
     * @var string
     */
    protected $role;
    
    /**
     *
     * @var AclInterface
     */
    protected static $defaultAcl;
    
    /**
     *
     * @var RoleInterface
     */
    protected static $defaultRole;
    
    /**
     * 
     * @param \Zend\View\Model\ViewModel $layout
     * @param \Zend\Config\Config $createdBlocks
     */
    public function __construct(ViewModel $layout, $createdBlocks)
    {
        $this->layout   = $layout;
        $this->createdBlocks = $createdBlocks;
    }
    
    /**
     * 
     * @param type $blocks
     * @param type $parent
     * @return \ConLayout\Service\Layout\Modifier
     */
    public function addBlocksToLayout(array $blocks = null, $parent = null)
    {
        if (null === $blocks) {
            $blocks = $this->createdBlocks;
        }
        if (null === $parent) {
            $parent = $this->layout;
        }
        foreach ($blocks as $captureTo => $blocks) {
            foreach ($blocks as $block) {
                if (!$this->isAllowed($block)) continue;
                $captureTo = !is_string($captureTo) ? $this->captureTo : $captureTo;
                $blockInstance = $block['instance'];
                if ($this->isDebug) {
                    $block['instance'] = $this->addDebugBlock($block['instance'], $captureTo);
                }
                $append = (isset($block['append']) && false === $block['append']) ? false : true;
                $parent->addChild($block['instance'], $captureTo, $append);
                if (isset($block['children'])) {
                    $this->addBlocksToLayout($block['children'], $blockInstance);
                }
            }
        }
        return $this;
    }
    
    /**
     * check if block is allowed
     * 
     * @param array $block
     * @return boolean
     */
    protected function isAllowed(array $block)
    {
        if (null !== $this->getAcl()) {
            $resourceName = isset($block['resource'])
                ? $block['resource']
                : $block['name'];
            if ($this->getAcl()->hasResource($resourceName)) {
                return $this->getAcl()->isAllowed($this->getRole(), $resourceName);
            }
        }
        return true;
    }
    
    /**
     * wrap ViewModel around block and set a debugger template
     * 
     * @param \Zend\View\Model\ViewModel $block
     * @return \Zend\View\Model\ViewModel
     */
    protected function addDebugBlock(ViewModel $block, $captureTo)
    {
        $debugBlock = new ViewModel(array(
            'blockName' => $block->getVariable('nameInLayout'),
            'blockTemplate' => $block->getTemplate(),
            'blockClass' => get_class($block)
        ));
        $debugBlock->setCaptureTo($captureTo);
        $debugBlock->setTemplate('blocks/debug');
        $debugBlock->addChild($block);
        return $debugBlock;
    }
    
    /**
     * 
     * @param bool $flag
     * @return \ConLayout\Service\LayoutModifier
     */
    public function setIsDebug($flag = true)
    {
        $this->isDebug = (bool) $flag;
        return $this;
    }
    
    /**
     * 
     * @param string $captureTo
     */
    public function setCaptureTo($captureTo)
    {
        $this->captureTo = (string) $captureTo;
        return $this;
    }
    
    /**
     * 
     * @return ViewModel
     */
    public function getLayout()
    {
        return $this->layout;
    }

    /**
     * 
     * @return array
     */
    public function getCreatedBlocks()
    {
        return $this->createdBlocks;
    }

    /**
     * 
     * @return AclInterface
     */
    public function getAcl()
    {
        if (null !== $this->acl) {
            return $this->acl;
        }
        if (null !== static::$defaultAcl) {
            return static::$defaultAcl;
        }
        return null;
    }

    /**
     * 
     * @return string|RoleInterface
     */
    public function getRole()
    {
        if (null !== $this->role) {
            return $this->role;
        }
        if (null !== static::$defaultRole) {
            return static::$defaultRole;
        }
        return null;
    }

    /**
     * 
     * @param \Zend\View\Model\ViewModel $layout
     * @return \ConLayout\Service\LayoutModifier
     */
    public function setLayout(ViewModel $layout)
    {
        $this->layout = $layout;
        return $this;
    }

    /**
     * 
     * @param type $createdBlocks
     * @return \ConLayout\Service\LayoutModifier
     */
    public function setCreatedBlocks($createdBlocks)
    {
        $this->createdBlocks = $createdBlocks;
        return $this;
    }

    /**
     * 
     * @param \Zend\Permissions\Acl\AclInterface $acl
     * @return \ConLayout\Service\LayoutModifier
     */
    public function setAcl(AclInterface $acl)
    {
        $this->acl = $acl;
        return $this;
    }
    
    /**
     * 
     * @param \Zend\Permissions\Acl\AclInterface $acl
     */
    public static function setDefaultAcl(AclInterface $acl)
    {
        static::$defaultAcl = $acl;
    }

    /**
     * 
     * @param string|RoleInterface $role
     * @return \ConLayout\Service\LayoutModifier
     */
    public function setRole($role)
    {
        $this->role = $role;
        return $this;
    }

    /**
     * 
     * @param \Zend\Permissions\Acl\Role\RoleInterface|string $role
     */
    public static function setDefaultRole($role)
    {
        static::$defaultRole = $role;
    }
}
