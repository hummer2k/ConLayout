<?php

namespace ConLayoutTest\Generator;

use ConLayout\Filter\BasePathFilter;
use ConLayout\Filter\CacheBusterFilter;
use ConLayout\Generator\ViewHelperGenerator;
use ConLayout\Listener\ViewHelperListener;
use ConLayout\Module;
use ConLayout\View\Helper\Proxy\HeadLinkProxy;
use ConLayout\View\Helper\Proxy\HeadMetaProxy;
use ConLayout\View\Helper\Proxy\HeadScriptProxy;
use ConLayout\View\Helper\Proxy\HeadTitleProxy;
use ConLayoutTest\AbstractTest;
use Laminas\Config\Config;
use Laminas\Escaper\Escaper;
use Laminas\Filter\FilterPluginManager;
use Laminas\View\Helper\Doctype;
use Laminas\View\Helper\HeadLink;
use Laminas\View\Helper\HeadMeta;
use Laminas\View\Helper\HeadScript;
use Laminas\View\Helper\HeadTitle;
use Laminas\View\HelperPluginManager;
use Laminas\View\Renderer\PhpRenderer;

/**
 * @package ConLayout
 * @author Cornelius Adams (conlabz GmbH) <cornelius.adams@conlabz.de>
 */
class ViewHelperGeneratorTest extends AbstractTest
{
    protected function getLayoutStructure()
    {
        return new Config([
            ViewHelperGenerator::INSTRUCTION => [
                'headLink' => [
                    'main'   => [
                        'href' => '/css/main.css',
                    ],
                    'test'   => [
                        'method' => 'prependStylesheet',
                        'href' => '/css/test.css'
                    ]
                ],
                'headTitle' => [
                    'default' => ['value' => 'My Title'],
                    'another' => [
                        'value' => 'Another Title',
                        'after' => 'default'
                    ],
                    'first' => [
                        'method' => 'prepend',
                        'value' => 'First'
                    ]
                ],
                'headMeta' => [
                    'charset' => [
                        'method' => 'setCharset',
                        'charset' => 'utf8'
                    ],
                    'desc' => [
                        'name' => 'description',
                        'content' => 'My description'
                    ],
                    'keywords' => [
                        'name' => 'keywords',
                        'content' => 'keyword1, keyword2, keyword3'
                    ]
                ],
                'headScript' => [
                    'jquery-ui' => [
                        'src' => '/js/jquery-ui.min.js',
                    ],
                    'jquery' => [
                        'method' => 'prependFile',
                        'src' => '/js/jquery.min.js'
                    ],
                    'modernizr' => [
                        'method' => 'appendFile',
                        'src' => '/js/modernizr.js',
                        'type' => 'text/javascript',
                        'attrs' => ['conditional' => 'lt IE 9']
                    ],
                    'will-be-ignored' => false
                ]
            ]
        ]);
    }

    public function testGenerateViewHelpers()
    {
        $config = (new Module())->getConfig();
        $helperPluginManager = new HelperPluginManager($this->sm);

        $filterManager = new FilterPluginManager($this->sm);

        $basePath = $helperPluginManager->get('basePath');
        $basePath->setBasePath('/assets');

        $basePathFilter = new BasePathFilter($basePath);
        $filterManager->setService('basePath', $basePathFilter);
        $cacheBusterFilter = new CacheBusterFilter(__DIR__ . '/_files');
        $filterManager->setService('cacheBuster', $cacheBusterFilter);

        $generator = new ViewHelperGenerator(
            $helperPluginManager,
            $config['con-layout']['view_helpers']
        );

        $generator->setFilterManager($filterManager);

        $renderer = new PhpRenderer();
        $renderer->setHelperPluginManager($helperPluginManager);

        /* @var $headLink HeadLink */
        $headLink = $helperPluginManager->get('headLink');
        $headLinkProxy = new HeadLinkProxy($headLink);
        $helperPluginManager->setService(get_class($headLinkProxy), $headLinkProxy);
        /* @var $headScript HeadScript */
        $headScript = $helperPluginManager->get('headScript');
        $headScriptProxy = new HeadScriptProxy($headScript);
        $helperPluginManager->setService(get_class($headScriptProxy), $headScriptProxy);
        /* @var $doctype Doctype */
        $doctype = $helperPluginManager->get('doctype');
        $doctype('HTML5');
        /* @var $headTitle HeadTitle */
        $headTitle = $helperPluginManager->get('headTitle');
        $headTitleProxy = new HeadTitleProxy($headTitle);
        $helperPluginManager->setService(get_class($headTitleProxy), $headTitleProxy);
        /* @var $headMeta HeadMeta */
        $headMeta = $helperPluginManager->get('headMeta');
        $headMetaProxy = new HeadMetaProxy($headMeta);
        $helperPluginManager->setService(get_class($headMetaProxy), $headMetaProxy);
        $headMeta->setView($renderer);

        $generator->generate($this->getLayoutStructure());

        foreach (['&#x2F;assets&#x2F;css&#x2F;test.css', '&#x2F;assets&#x2F;css&#x2F;main.css'] as $expected) {
            $this->assertStringContainsString($expected, $headLink->toString());
        }

        foreach (['jquery.min.js', 'jquery-ui.min.js'] as $expected) {
            $this->assertStringContainsString($expected, $headScript->toString());
        }

        $this->assertEquals('<!DOCTYPE html>', (string) $doctype);

        $headTitle->setSeparator(' | ');
        $expected = 'First | My Title | Another Title';
        $this->assertStringContainsString($expected, $headTitle->toString());

        $contains = [
            'charset="utf8"',
            'name="description" content="My&#x20;description"',
            'name="keywords" content="keyword1,&#x20;keyword2,&#x20;keyword3"',
        ];

        foreach ($contains as $expected) {
            $this->assertStringContainsString($expected, $headMeta->toString());
        }
    }
}
