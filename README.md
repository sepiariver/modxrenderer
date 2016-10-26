# MODXRenderer

MODXRenderer is modelled after Slim's PHP view renderer, and can be used in a Slim app as such. Alternatively it can modify any PSR-7 response object, or simply parse a string with MODX Revolution template syntax.

The current beta release supports MODX "Chunk" tags and "Placeholder" tags.

The project is managed on [Github](https://github.com/sepiariver/modxrenderer/). Documentation is rendered at: [https://sepiariver.github.io/modxrenderer/](https://sepiariver.github.io/modxrenderer/).

## Why?

- Familiar (and much-loved by the author) MODX template syntax in any application or PHP environment >= 5.6.
- Isolated parser functionality decoupled from the `$modx` container (or any container for that matter).
- [Unit tested with > 95% coverage](https://sepiariver.github.io/modxrenderer/test-results/). (Some paths intentionally omitted due to inability to reproduce test case. Help on this would be appreciated.)

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

### render()

The most predictable pattern in which to use MODXRenderer in a Slim app is to call the `render()` method in a route closure. The method takes a PSR-7 `Response` object, which the Slim route provides, and a template name.

Example:

```
$renderer = $this->get('renderer'); // from DI Container
$renderer->render($response, 'myView.tpl');
```

Optionally you can pass in an array of data with which to populate placeholders in the template.

```
$args = $myDataLayer->getDataArray();
$renderer->render($response, 'myView.tpl', $args);
```

_NOTE: unlike the native MODX environment, the MODXRenderer does not include automatic input sanitization. Un-sanitized inputs, for example those from `$args` set in request parameters, can be reflected in the parsed content and pose a security risk. Take care to sanitize untrusted data before passing it to the view renderer._

### MODXParser methods

You can also directly access the MODXParser methods to perform ad hoc parsing and rendering of strings or template/Chunk files.

```
$content = file_get_contents('/path/to/template.tpl');
$renderer->processElementTags('', $content);
```
