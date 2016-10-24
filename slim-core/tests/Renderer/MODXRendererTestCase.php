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
     * @expectedException InvalidArgumentException
     */
    public function testConstructRendererFailure () {

        require self::APP_CORE_PATH . 'vendor/autoload.php';
        // expectedException
        $renderer = new MODXRenderer([],[]);

    }

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
    /**
     * @test instatiate chunk tag
     * @depends testConstructRenderer
     */
    public function testInstantiateChunkTag(MODXParser $parser) {

        $chunk = new MODXChunkTag($parser);
        $chunk->set('name','TestChunkTag');
        $this->assertEquals(true, ($chunk instanceof MODXChunkTag));
        $this->assertEquals('$', $chunk->getToken());
        $this->assertEquals(false, $chunk->isCacheable());
        $this->assertEquals('[[$TestChunkTag]]', $chunk->getTag());
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
    /**
     * @test process chunk tag
     * @depends testInstantiateChunkTag
     */
    public function testSetPropsChunkTag(MODXChunkTag $chunk) {

        $propertiesArray1 = [
            ['test_prop_array', '', '', '', 'test prop array value', 1],
        ];
        $propertiesArray2 = [
            'test_prop_array2' => 'test prop array value 2',
        ];
        $propertiesString = '? &test_prop3=`test_value3` &test_prop4=`test_value4`';

        $arraySuccess1 = $chunk->setProperties($propertiesArray1);
        $arraySuccess2 = $chunk->setProperties($propertiesArray2, true);
        $stringSuccess = $chunk->setProperties($propertiesString, true);

        $this->assertEquals(true, $arraySuccess1);
        $this->assertEquals(true, $arraySuccess2);
        $this->assertEquals(true, $stringSuccess);

    }

}
