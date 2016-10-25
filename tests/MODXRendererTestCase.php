<?php

namespace Tests\Renderer;

use MODXRenderer\MODXRenderer;
use MODXRenderer\MODXParser;
use MODXRenderer\MODXChunkTag;
use Slim\Http\Response;

/**
 * MODXRendererTestCase.
 */
class MODXRendererTestCase extends \PHPUnit_Framework_TestCase
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
     * @expectedException InvalidArgumentException
     */
    public function testConstructRendererFailure()
    {
        // expectedException
        $renderer = new MODXRenderer([], []);
    }

    /**
     * @test constructor
     */
    public function testConstructRenderer()
    {
        $rendererSettings = array(
            'template_path' => self::APP_CORE_PATH.'tests/templates/',
            'chunk_path' => self::APP_CORE_PATH.'tests/chunks/',
        );

        $renderer = new MODXRenderer($rendererSettings, self::$siteSettings);

        $success = ($renderer instanceof MODXRenderer);

        $this->assertEquals($success, true);

        return $renderer;
    }

    /**
     * @test render fetch fail data
     * @depends testConstructRenderer
     * @expectedException InvalidArgumentException
     */
    public function testRenderFetchFailData(MODXRenderer $renderer)
    {
        $renderer->fetch('testTemplate.tpl', ['template' => 'this causes error']);
    }
    /**
     * @test render fetch fail template
     * @depends testConstructRenderer
     * @expectedException RuntimeException
     */
    public function testRenderFetchFailTemplate(MODXRenderer $renderer)
    {
        $renderer->fetch('non-existent.tpl');
    }
    /**
     * @test render get attributes
     * @depends testConstructRenderer
     */
    public function testRenderGetAttributes(MODXRenderer $renderer)
    {
        $expected = json_encode(self::$siteSettings);
        $result = json_encode($renderer->getAttributes());
        $this->assertEquals($expected, $result);
        $shouldFail = $renderer->getAttribute('non-existent-attribute');
        $this->assertEquals(false, $shouldFail);
    }

    /**
     * @test render set attributes
     * @depends testConstructRenderer
     */
    public function testRenderSetAttributes(MODXRenderer $renderer)
    {
        $newAttributes = ['test_set_attributes' => 'test_set_attributes_value'];
        $renderer->setAttributes($newAttributes);
        $expected = json_encode($newAttributes);
        $result = json_encode($renderer->getAttributes());
        $this->assertEquals($expected, $result);
    }

    /**
     * @test render add attribute
     * @depends testConstructRenderer
     */
    public function testRenderAddAttribute(MODXRenderer $renderer)
    {
        $attributes = $renderer->getAttributes();
        $attributes['added_attribute'] = 'added attribute value';
        $renderer->addAttribute('added_attribute', 'added attribute value');
        $this->assertEquals('added attribute value', $renderer->getAttribute('added_attribute'));
        $expected = json_encode($attributes);
        $result = json_encode($renderer->getAttributes());
        $this->assertEquals($expected, $result);
    }
    /**
     * @test collect element tags
     * @depends testConstructRenderer
     */
    public function testRenderCollectTags(MODXRenderer $renderer)
    {
        $content = file_get_contents(self::APP_CORE_PATH.'tests/templates/testCollectElements.tpl');
        $result = [];
        $renderer->collectElementTags($content, $result);
        //var_dump($result);
        $this->assertEquals(7, count($result));
        $success = 0;
        foreach ($result as $tagArray) {
            if (strpos($content, $tagArray[0]) !== false) {
                ++$success;
            }
        }
        $this->assertEquals(7, $success);
    }
    /**
     * @test render template
     * @depends testConstructRenderer
     */
    public function testRenderTemplate(MODXRenderer $renderer)
    {
        $response = new Response();
        $args = ['test_arg' => 'MODXRenderer Test Arg'];
        $renderer->render($response, 'testTemplate.tpl', $args);

        $this->assertEquals(trim($response->getBody()->__toString()), 'Test Arg: MODXRenderer Test Arg');
    }
    /**
     * @test render site setting
     * @depends testConstructRenderer
     */
    public function testSiteSetting(MODXRenderer $renderer)
    {
        $response = new Response();
        $renderer->render($response, 'testSiteSetting.tpl');
        $this->assertEquals(trim($response->getBody()->__toString()), 'MODXRenderer Test Suite');
    }
    /**
     * @test render nested site setting
     * @depends testConstructRenderer
     */
    public function testNestedSiteSetting(MODXRenderer $renderer)
    {
        $response = new Response();
        $renderer->render($response, 'testSiteSettingNested.tpl');
        $this->assertEquals(trim($response->getBody()->__toString()), 'color sepia');
    }
    /**
     * @test render chunk in template
     * @depends testConstructRenderer
     */
    public function testChunk(MODXRenderer $renderer)
    {
        $response = new Response();
        $renderer->render($response, 'testRenderChunk.tpl');
        $this->assertEquals(trim($response->getBody()->__toString()), 'MODXRenderer Test Chunk');
    }

    /**
     * @test render nested chunks in template
     * @depends testConstructRenderer
     */
    public function testChunkNested(MODXRenderer $renderer)
    {
        $response = new Response();
        $renderer->render($response, 'testRenderChunkNested.tpl');
        $this->assertEquals(trim($response->getBody()->__toString()), 'MODXRenderer Test Nested Chunk: MODXRenderer Test Chunk');
    }
    /**
     * @test parser process elements
     * @depends testConstructRenderer
     */
    public function testParserProcessElementTags(MODXRenderer $renderer)
    {
        $content = file_get_contents(self::APP_CORE_PATH.'tests/templates/testProcessElements.tpl');
        $content1 = $content;
        $content2 = $content;
        $content3 = $content;

        $renderer->processElementTags('', $content, true, true);
        $expected = "Site Setting: MODXRenderer Test Suite
Nested Site Setting: color sepia
Chunk: MODXRenderer Test Chunk

Nested Chunk: MODXRenderer Test Nested Chunk: MODXRenderer Test Chunk


Test Arg: MODXRenderer Test Arg
Test Not Found Tag:\040
Test Uncacheable Tag:\040
";
        //var_dump($content);
        $this->assertEquals($expected, $content);

        $renderer->processElementTags('', $content1, false, true);
        $expected1 = "Site Setting: MODXRenderer Test Suite
Nested Site Setting: color sepia
Chunk: MODXRenderer Test Chunk

Nested Chunk: MODXRenderer Test Nested Chunk: MODXRenderer Test Chunk


Test Arg: MODXRenderer Test Arg
Test Not Found Tag:\040
Test Uncacheable Tag: [[!+uncacheable_tag]]
";
        $this->assertEquals($expected1, $content1);

        $renderer->processElementTags('', $content2, false, false);
        $expected2 = 'Site Setting: MODXRenderer Test Suite
Nested Site Setting: color sepia
Chunk: MODXRenderer Test Chunk

Nested Chunk: MODXRenderer Test Nested Chunk: MODXRenderer Test Chunk


Test Arg: MODXRenderer Test Arg
Test Not Found Tag: [[+not_found_tag]]
Test Uncacheable Tag: [[!+uncacheable_tag]]
';
        $this->assertEquals($expected2, $content2);
    }
    /**
     * @test instatiate chunk tag
     * @depends testConstructRenderer
     */
    public function testInstantiateChunkTag(MODXParser $parser)
    {
        $chunk = new MODXChunkTag($parser);
        $chunk->set('name', 'TestChunkTag');
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
    public function testProcessChunkTag(MODXChunkTag $chunk)
    {
        $properties = ['test_prop' => 'test_value'];
        $content = '[[+test_prop]]';
        $result = $chunk->process($properties, $content);
        $this->assertEquals($result, $properties['test_prop']);
        $this->assertEquals('[[+test_prop]]', $chunk->getContent());
    }
    /**
     * @test get chunk content
     * @depends testConstructRenderer
     */
    public function testChunkTagContent(MODXParser $parser)
    {
        $chunk = new MODXChunkTag($parser);
        $chunk->set('name', 'testChunkEmpty');
        $this->assertEquals('', $chunk->getContent());
        // This method is kinda dumb but we'll test it anyways
        $chunk->setContent('test');
        $this->assertEquals('test', $chunk->get('name'));

        return $chunk;
    }
    /**
     * @test get chunk test
     * @depends testConstructRenderer
     */
    public function testParserGetChunk(MODXParser $parser)
    {
        $result = $parser->getChunk('testChunkPropString', array(
            'prop_string' => 'testing getChunk',
            'prop_string2' => 'prop string with amp; in key',
        ));
        $this->assertEquals('MODXRenderer Test Chunk Prop String: testing getChunkprop string with amp; in key
', $result);
    }
    /**
     * @test process chunk tag
     * @depends testInstantiateChunkTag
     */
    public function testSetPropsChunkTag(MODXChunkTag $chunk)
    {
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
    /**
     * @test render chunk in template
     * @depends testConstructRenderer
     */
    public function testChunkPropString(MODXRenderer $renderer)
    {
        $response = new Response();
        $renderer->render($response, 'testChunkPropString.tpl');

        $this->assertEquals(trim($response->getBody()->__toString()), 'Chunk with prop string: MODXRenderer Test Chunk Prop String: MODXRenderer Test Suiteprop string with amp value');
    }
    /**
     * @test render toplaceholders w object
     * @depends testConstructRenderer
     */
    public function testRenderToPlaceholders(MODXRenderer $renderer)
    {
        $obj = (object) array(
            'test_object_to' => 'placeholders',
            'nested_object_ph' => ['inside nested object' => 'is a nested value'],
        );
        $renderer->toPlaceholders($obj);
        $this->assertEquals('placeholders', $renderer->data['test_object_to']);
        $this->assertEquals('is a nested value', $renderer->data['nested_object_ph.inside nested object']);
    }
    /**
     * @test render setplaceholders
     * @depends testConstructRenderer
     */
    public function testRenderSetPlaceholders(MODXRenderer $renderer)
    {
        $obj = (object) array(
            'test_object_set' => 'placeholders',
            'flat_object' => 'scalar values',
        );
        $renderer->setPlaceholders($obj);
        $this->assertEquals('placeholders', $renderer->data['test_object_set']);
        $this->assertEquals('scalar values', $renderer->data['flat_object']);
        $renderer->unsetPlaceholders('flat_object');
        $this->assertArrayNotHasKey('flat_object', $renderer->data);
    }
    /**
     * @test render set processing
     * @depends testConstructRenderer
     */
    public function testRenderSetProcessing(MODXRenderer $renderer)
    {
        $renderer->setProcessingElement(null);
        $this->assertEquals(false, $renderer->isProcessingElement());
        $renderer->setProcessingElement('yes');
        $this->assertEquals(true, $renderer->isProcessingElement());
    }
}
