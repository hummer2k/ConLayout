<?php

declare(strict_types=1);

namespace ConLayoutTest\Generator;

use ConLayout\Filter\BasePathFilter;
use ConLayout\Filter\CacheBusterFilter;
use ConLayout\ConfigProvider;
use ConLayout\Generator\ViewHelperGenerator;
use ConLayoutTest\AbstractTestCase;
use Laminas\Config\Config;
use Laminas\Filter\FilterPluginManager;
use Laminas\View\Helper\BasePath as BasePathHelper;
use Laminas\View\Helper\Doctype;
use Laminas\View\Helper\HeadLink;
use Laminas\View\Helper\HeadMeta;
use Laminas\View\Helper\HeadScript;
use Laminas\View\Helper\HeadTitle;
use Laminas\View\HelperPluginManager;

class ViewHelperGeneratorTest extends AbstractTestCase
{
    protected function getLayoutStructure(): Config
    {
        return new Config([
            ViewHelperGenerator::INSTRUCTION => [
                'headLink' => [
                    'main'   => [
                        'href' => '/css/main.css',
                    ],
                    'test'   => [
                        'method' => 'prependStylesheet',
                        'href' => '/css/test.css',
                    ],
                ],
                'headTitle' => [
                    'default' => ['value' => 'My Title'],
                    'another' => [
                        'value' => 'Another Title',
                        'after' => 'default',
                    ],
                    'first' => [
                        'method' => 'prepend',
                        'value' => 'First',
                    ],
                ],
                'headMeta' => [
                    'charset' => [
                        'method' => 'setCharset',
                        'charset' => 'utf8',
                    ],
                    'desc' => [
                        'name' => 'description',
                        'content' => 'My description',
                    ],
                    'keywords' => [
                        'name' => 'keywords',
                        'content' => 'keyword1, keyword2, keyword3',
                    ],
                ],
                'headScript' => [
                    'jquery-ui' => [
                        'src' => '/js/jquery-ui.min.js',
                    ],
                    'jquery' => [
                        'method' => 'prependFile',
                        'src' => '/js/jquery.min.js',
                    ],
                    'modernizr' => [
                        'method' => 'appendFile',
                        'src' => '/js/modernizr.js',
                        'type' => 'text/javascript',
                        'attrs' => ['conditional' => 'lt IE 9'],
                    ],
                    'will-be-ignored' => false,
                ],
            ],
        ]);
    }

    public function testGenerateViewHelpers(): void
    {
        $config = (new ConfigProvider())()['con-layout'];
        $helperPluginManager = new HelperPluginManager($this->sm);

        $filterManager = new FilterPluginManager($this->sm);

        $basePathFilter = new BasePathFilter(new BasePathHelper('/assets'));
        $filterManager->setService('basePath', $basePathFilter);
        $cacheBusterFilter = new CacheBusterFilter(__DIR__ . '/_files');
        $filterManager->setService('cacheBuster', $cacheBusterFilter);

        $generator = new ViewHelperGenerator(
            $helperPluginManager,
            $config['view_helpers'],
        );

        $generator->setFilterManager($filterManager);

        /* @var $headLink HeadLink */
        $headLink = $helperPluginManager->get('headLink');
        /* @var $headScript HeadScript */
        $headScript = $helperPluginManager->get('headScript');
        /* @var $doctype Doctype */
        $doctype = $helperPluginManager->get('doctype');
        $doctype();
        /* @var $headTitle HeadTitle */
        $headTitle = $helperPluginManager->get('headTitle');
        /* @var $headMeta HeadMeta */
        $headMeta = $helperPluginManager->get('headMeta');

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
            'content="My&#x20;description" name="description"',
            'content="keyword1,&#x20;keyword2,&#x20;keyword3" name="keywords"',
        ];

        foreach ($contains as $expected) {
            $this->assertStringContainsString($expected, $headMeta->toString());
        }
    }
}
