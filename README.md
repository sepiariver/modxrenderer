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





