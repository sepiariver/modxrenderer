<?php

namespace Tests\Renderer;

use MODXRenderer\MODXRenderer;
use Slim\Http\Response;

/**
 * MODXRendererTestCase
 */
class MODXRendererTestCase extends \PHPUnit_Framework_TestCase
{

    const APP_CORE_PATH = '/Users/srMBP/_git/sr_modxrenderer/slim-core/';
    const PUBLIC_BASE_PATH = '/Users/srMBP/_git/sr_modxrenderer/public/';
    const SITE_URL = 'http://modxrenderer.local/';

    /**
     * @test constructor
     */
    public function testConstructRenderer () {

        require self::APP_CORE_PATH . 'vendor/autoload.php';

        $rendererSettings = array(
            'template_path' => self::APP_CORE_PATH . 'tests/Renderer/templates/',
            'chunk_path' => self::APP_CORE_PATH . 'tests/Renderer/chunks/',
        );
        $siteSettings = array(
            'site_name' => 'MODXRenderer Test Suite',
            'site_css' => ['sepia' => 'color sepia'],
        );
        $renderer = new MODXRenderer($rendererSettings, $siteSettings);

        $success = ($renderer instanceof MODXRenderer);

        $this->assertEquals($success, true);

        return $renderer;
    }

    /**
     * @test render template
     * @depends testConstructRenderer
     */
    public function testRenderTemplate(MODXRenderer $renderer) {
        $response = new Response();
        $args = ['test_arg' => 'MODXRenderer Test Arg'];
        $renderer->render($response, 'testTemplate.tpl', $args);

        $this->assertEquals(trim($response->getBody()->__toString()), 'Test Arg: MODXRenderer Test Arg');

    }
    /**
     * @test render site setting
     * @depends testConstructRenderer
     */
    public function testSiteSetting(MODXRenderer $renderer) {
        $response = new Response();
        $renderer->render($response, 'testSiteSetting.tpl');
        $this->assertEquals(trim($response->getBody()->__toString()), 'MODXRenderer Test Suite');

    }
    /**
     * @test render nested site setting
     * @depends testConstructRenderer
     */
    public function testNestedSiteSetting(MODXRenderer $renderer) {
        $response = new Response();
        $renderer->render($response, 'testSiteSettingNested.tpl');
        $this->assertEquals(trim($response->getBody()->__toString()), 'color sepia');

    }
    /**
     * @test render chunk in template
     * @depends testConstructRenderer
     */
    public function testChunk(MODXRenderer $renderer) {
        $response = new Response();
        $renderer->render($response, 'testRenderChunk.tpl');
        $this->assertEquals(trim($response->getBody()->__toString()), 'MODXRenderer Test Chunk');

    }
    /**
     * @test render nested chunks in template
     * @depends testConstructRenderer
     */
    public function testChunkNested(MODXRenderer $renderer) {
        $response = new Response();
        $renderer->render($response, 'testRenderChunkNested.tpl');
        $this->assertEquals(trim($response->getBody()->__toString()), 'MODXRenderer Test Nested Chunk: MODXRenderer Test Chunk');

    }
}
