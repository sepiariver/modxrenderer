<?php

namespace Tests\Renderer;

use SepiaRiver\MODXRenderer;
use SepiaRiver\MODXParser;
use SepiaRiver\MODXChunkTag;
use Slim\Http\Response;

/**
 * MODXRendererTestCase.
 */
class SingleTestCase extends \PHPUnit_Framework_TestCase
{
    const APP_CORE_PATH = '/Volumes/Media/_git/sr_modxrenderer/';
    const PUBLIC_BASE_PATH = '/Volumes/Media/_git/sr_modxrenderer/docs/';
    const SITE_URL = 'http://modxrenderer.local/';

    public static $siteSettings = array(
        'site_name' => 'MODXRenderer Test Suite',
        'site_css' => ['sepia' => 'color sepia'],
    );
    /**
     * @test constructor
     */
    public function testConstructRenderer()
    {
        $rendererSettings = array(
            'template_path' => self::APP_CORE_PATH.'tests/templates/',
            'chunk_path' => self::APP_CORE_PATH.'tests/chunks/',
            'site_prefix' => '+',
        );

        $renderer = new MODXRenderer($rendererSettings, self::$siteSettings);

        $success = ($renderer instanceof MODXRenderer);

        $this->assertEquals($success, true);

        return $renderer;
    }

    /**
     * @test render template with filters
     * @depends testConstructRenderer
     */
    public function testRenderFilters(MODXRenderer $renderer)
    {
        $response = new Response();
        $args = [
            'one' => 'one',
            'two' => 'two',
            'isempty' => '',
            'notempty' => ' ',
        ];
        $renderer->render($response, 'testFilter.tpl', $args);
        var_dump($response->getBody()->__toString());
        //$this->assertEquals(trim($response->getBody()->__toString()), 'Test Arg: MODXRenderer Test Arg');
    }

}
