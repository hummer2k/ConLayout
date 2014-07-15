<?php
namespace ConLayout\Block;

use Zend\View\Model\ViewModel;
/**
 * Dummy block demo
 * 
 * @package ConLayout
 * @author Cornelius Adams (conlabz GmbH) <cornelius.adams@conlabz.de> *
 */
class Dummy
    extends ViewModel
    implements BlockInterface
{
    protected $template = 'blocks/dummy';
    
    public function init()
    {
        // do stuff        
        $this->setVariables(array(
            'title' => 'Dummy Block',
            'text' => 'This is a dummy block.'
        ));
    }
    
    public function getArticles($limit = 10)
    {
        $articles = array(
            array(
                'title' => 'LOL',
                'text' => 'Lorem ipsum.'
            ),
            array(
                'title' => 'HAHA',
                'text' => 'lorem ipsum 2k3 blubb'
            ),
            array(
                'title' => 'BLUBBB',
                'text' => 'Lorem Ipsum dolor'
            )
        );
        return array_slice($articles, 0, $limit);
    }
}
