# MODXRenderer

MODXRenderer is modeled after Slim's PHP view renderer, and can be used in a Slim app as such. Alternatively it can modify any PSR-7 response object, or simply parse a string with MODX Revolution template syntax.

The current alpha release supports MODX "Chunk" tags and "Placeholder" tags. [Upcoming project milestones](https://github.com/sepiariver/modxrenderer/milestones) include:

- Output filter support
- More testing
- Property sets
- Submit to Packagist?

**Resources**

- [Github repo](https://github.com/sepiariver/modxrenderer/)
- [Documentation](https://sepiariver.github.io/modxrenderer/)
- [Slim](http://www.slimframework.com/)
- [MODX](https://modx.com/)

## Why?

- Familiar (and much-loved by the author) MODX template syntax in any application or PHP environment >= 5.6.
- Self-contained parser functionality decoupled from the `$modx` container (or any container for that matter).
- [Unit tested with > 95% coverage](https://sepiariver.github.io/modxrenderer/test-results/). Some paths intentionally omitted due to inability to fabricate test case, especially in the MODXParser class. Help on this would be appreciated. Feel free to [contact me](https://github.com/sepiariver/) even if it's just to tell me that this whole thing is a bad idea LOL.

## Installation

```
git clone https://github.com/sepiariver/modxrenderer

cd modxrenderer

composer install # optionally --no-dev --no-scripts
```

## Usage

### Slim DI Container

When initializing MODXRenderer, the first argument is required—it must be an array with the following elements:

- 'template_path' => absolute path to the filesystem location of your template files
- 'chunk_path' => absolute path to the filesystem location of your "Chunk" template files

In this example, an optional array of "site settings" is passed to the renderer. These values will be made globally-available in the parsed template and Chunks, via placeholders with a double "++" token.

```
$container['renderer'] = function($c) {
    $settings = $c->get('settings');
    return new SepiaRiver\MODXRenderer($settings['renderer'], $settings['site']);
};
```
Nested arrays can be passed in, which will be converted to MODX Placeholder Tags with dot notation syntax:

```
$settings['site'] = array(
    'nested' => array(
        'setting' => 'value',
        ),
);
// Will populate placeholder tag [[++nested.setting]] with the string 'value'
```

### render()

The basic pattern in which to use MODXRenderer in a Slim app is to call the `render()` method in a route closure. The method takes a PSR-7 `Response` object, which Slim provides, and a template name.

Example:

```
$renderer = $this->get('renderer'); // from DI Container
$renderer->render($response, 'myView.tpl');
```
The template must exist in the `template_path` filesystem location. Optionally you can pass in an array of data with which to populate placeholders in the template.

```
$args = $myDataLayer->getDataArray();
$renderer->render($response, 'myView.tpl', $args);
```

_NOTE: unlike the native MODX environment, the MODXRenderer does not include automatic input sanitization. Un-sanitized inputs, for example those from `$args` set in request parameters, can be reflected in the parsed content and pose a security risk. Take care to sanitize untrusted data before passing it to the view renderer._

### MODXParser methods

You can also directly access the MODXParser methods to perform ad hoc parsing and rendering of strings or template and Chunk files.

#### Templates

```
$content = file_get_contents('/path/to/template.tpl');
$renderer->processElementTags('', $content);
```
The `processElementTags()` method modifies the `$content` in place by collecting all MODX Tags therein and replacing them with values from the array of "site settings" passed to the constructor.

 You can merge in ad-hoc data by setting the `$data` property on the renderer before calling the method:

 ```
 $renderer->data = $myDataArray;
 $renderer->processElementTags('', $content);
 ```

 In the calls shown above, unprocessed and un-cacheable tags will be left intact, in tag form, in the output. To remove them, pass the third and fourth arguments:

```
$renderer->processElementTags('', $content, true, true);
```
The first argument is for use internally by other methods during recursion. Pass an empty string when using the method directly.

#### Chunks

```
$output = $renderer->getChunk('chunkName', $myDataArray);
```
The `getChunk()` method will get the contents of a file with name `{$chunkName}.tpl` in the `chunk_path` filesystem location and replace MODX Tags therein with data from both the "site settings" and the array passed in as the 2nd argument, if provided.

## Other Considerations

This project is in alpha and not suitable for production.
