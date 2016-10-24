<?php

namespace Tests\Renderer;

use MODXRenderer\MODXRenderer;
use MODXRenderer\MODXParser;
use MODXRenderer\MODXTag;
use MODXRenderer\MODXChunkTag;
use MODXRenderer\MODXPlaceholderTag;

use Slim\Http\Response;

/**
 * MODXRendererTestCase
 */
class MODXRendererTestCase extends \PHPUnit_Framework_TestCase
{

    const APP_CORE_PATH = '/Volumes/Media/_git/sr_modxrenderer/slim-core/';
    const PUBLIC_BASE_PATH = '/Volumes/Media/_git/sr_modxrenderer/public/';
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
     * @test instatiate chunk tag
     * @depends testConstructRenderer
     */
    public function testInstantiateChunkTag(MODXParser $parser) {

        $chunk = new MODXChunkTag($parser);
        $this->assertEquals(true, ($chunk instanceof MODXChunkTag));
        return $chunk;
    }
    /**
     * @test process chunk tag
     * @depends testInstantiateChunkTag
     */
    public function testProcessChunkTag(MODXChunkTag $chunk) {

        $properties = ['test_prop' => 'test_value'];
        $content= '[[+test_prop]]';
        $result = $chunk->process($properties, $content);
        $this->assertEquals($result, $properties['test_prop']);

    }


}
