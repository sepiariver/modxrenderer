# MODXRenderer

MODXRenderer is modelled after Slim's PHP view renderer, and can be used in a Slim app as such. Alternatively it can modify any PSR-7 response object, or simply parse a string with MODX Revolution template syntax.

The current beta release supports MODX "Chunk" tags and "Placeholder" tags.

## Why?

- Familiar (and much-loved by the author) MODX template syntax in any application or PHP environment >= 5.6.
- Isolated parser functionality decoupled from the $modx container (or any container for that matter).
- Unit tested with > 90% coverage.

## Installation

```
git clone https://github.com/sepiariver/modxrenderer

cd modxrenderer

composer install # optionally --no-dev --no-scripts
```

## Usage

### render()

The way to use MODXRenderer in a Slim app is to call the `render()` method, which takes a PSR-compliant `Response` object, and a template name.

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


