<?php

namespace ConLayout\Block\Factory;

use ConLayout\Block\BlockInterface;
use Laminas\View\Model\ModelInterface;

/**
 * @package ConLayout
 * @author Cornelius Adams (conlabz GmbH) <cornelius.adams@conlabz.de>
 */
interface BlockFactoryInterface
{
    /**
     *
     * @param string $blockId
     * @param array $specs
     * @return ModelInterface|BlockInterface
     */
    public function createBlock($blockId, array $specs);

    /**
     * @param ModelInterface $block
     * @param array $specs
     * @return mixed
     */
    public function configure(ModelInterface $block, array $specs);
}
