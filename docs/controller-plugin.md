# Layout manager controller plugin

````php
<?php

class IndexController extends AbstractActionController
{
    public function indexAction()
    {
        /* @var $layoutManager \ConLayout\Controller\Plugin\LayoutManager */
        $layoutManager = $this->layoutManager();

        // add a custom handle
        $layoutManager->addHandle('my-custom-handle', 5);

        // remove a handle
        $layoutManager->removeHandle('my-custom-handle');

        // retrieve a block by ID
        $header = $layoutManager->getBlock('header');

        // add a block programatically
        $block = new ViewModel();
        $block->setCaptureTo('sidebar');
        $block->setOption('order', 10);

        $layoutManager->addBlock('my.block', $block);

        // ...
    }
}
````