<?php
namespace ConLayout\Block;

/**
 * Dummy block demo
 * @codeCoverageIgnore
 * @package ConLayout
 * @author Cornelius Adams (conlabz GmbH) <cornelius.adams@conlabz.de> *
 */
class Dummy extends AbstractBlock
{
    protected $template = 'blocks/dummy';
    
    protected $limit;
    
    public function init()
    {
        // do stuff
        $this->setVariables(array(
            'title' => 'Dummy Block',
            'text' => 'This is a dummy block.'
        ));
        $this->limit = $this->getRequest()->getQuery('limit', 10);
    }
    
    public function getArticles()
    {
        $articles = array(
            array(
                'title' => 'Lorem ipsum',
                'text' => 'dolor sit amet.'
            ),
            array(
                'title' => 'Lorem ipsum',
                'text' => 'dolor sit amet.'
            ),
            array(
                'title' => 'Lorem ipsum',
                'text' => 'dolor sit amet.'
            ),
            array(
                'title' => 'Lorem ipsum',
                'text' => 'dolor sit amet.'
            ),
        );
        return array_slice($articles, 0, $this->limit);
    }
    
    public function setLimit($limit)
    {
        $this->limit = $limit;
        return $this;
    }
}
