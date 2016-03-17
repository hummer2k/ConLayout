<?php

namespace ConLayout\Layout;

use ConLayout\Generator\GeneratorInterface;
use Zend\View\Model\ModelInterface;

/**
 * @package ConLayout
 * @author Cornelius Adams (conlabz GmbH) <cornelius.adams@conlabz.de>
 */
interface LayoutInterface
{
    /**
     * block id of root view model
     */
    const BLOCK_ID_ROOT = 'root';

    /**
     * block id of view model returned by controller
     */
    const BLOCK_ID_ACTION_RESULT = 'action.result';

    /**
     * delimiter block_id::cpature_to
     */
    const CAPTURE_TO_DELIMITER = '::';

    /**
     * @param array $generators load only given generators or all if empty
     * @return mixed
     */
    public function generate(array $generators = []);

    /**
     * @return mixed
     */
    public function buildTree();

    /**
     * load the layout
     */
    public function load();

    /**
     * set root view model/layout
     *
     * @param ModelInterface $root
     */
    public function setRoot(ModelInterface $root);

    /**
     * @param string $name
     * @param GeneratorInterface $generator
     * @param int $priority
     * @return mixed
     */
    public function attachGenerator($name, GeneratorInterface $generator, $priority = 1);

    /**
     * removes a generator
     *
     * @param string $name
     * @return mixed
     */
    public function detachGenerator($name);
}
