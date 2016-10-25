<?php
/**
 * Represents placeholder tags.
 *
 * [[+placeholder_key]] Represents a placeholder with name placeholder_key.
 *
 * @uses modX::getPlaceholder() To retrieve the placeholder value.
 * @package modx
 */
namespace MODXRenderer;

use MODXRenderer\MODXTag;
use MODXRenderer\MODXParser;

class MODXPlaceholderTag extends MODXTag {
    /**
     * Overrides MODXTag::__construct to set the Placeholder Tag token
     * {@inheritdoc}
     */
    function __construct(MODXParser $parser, $maxIterations = 10) {
        parent :: __construct($parser);
        $this->setCacheable(false);
        $this->setToken('+');
        $this->maxIterations = $maxIterations;
    }

    /**
     * Processes the MODXPlaceholderTag, recursively processing nested tags.
     *
     * Tags in the properties of the tag itself, or the content returned by the
     * tag element are processed.  Non-cacheable nested tags are only processed
     * if this tag element is also non-cacheable.
     *
     * {@inheritdoc}
     */
    public function process($properties= null, $content= null) {
        parent :: process($properties, $content);
        if (!$this->_processed) {
            $this->_output= $this->_content;
            if ($this->_output !== null && is_string($this->_output) && !empty($this->_output)) {
                    /* collect element tags in the content and process them */

                    $this->parser->processElementTags(
                        $this->_tag,
                        $this->_output,
                        $this->parser->isProcessingUncacheable(),
                        $this->parser->isRemovingUnprocessed(),
                        '[[',
                        ']]',
                        array(),
                        $this->maxIterations
                    );
                }
            if ($this->_output !== null || $this->parser->startedProcessingUncacheable()) {
                //TODO: output filter support
                //$this->filterOutput();
                $this->_processed = true;
            }
        }
        /* finally, return the processed element content */
        return $this->_output;
    }

    /**
     * Get the raw source content of the field.
     *
     * {@inheritdoc}
     */
    public function getContent(array $options = array()) {
        if (!is_string($this->_content)) {
            if (isset($options['content'])) {
                // @codeCoverageIgnoreStart
                $this->_content = $options['content'];
                // @codeCoverageIgnoreEnd
            } elseif (isset($this->parser->data[$this->get('name')])) {
                $this->_content = $this->parser->data[$this->get('name')];
            } else {
                $this->_content = null;
            }
        }
        return $this->_content;
    }

    /**
     * MODXPlaceholderTag instances cannot be cacheable.
     *
     * @return boolean Always returns false.
     * @codeCoverageIgnore
     */
    public function isCacheable() {
        return false;
    }

    /**
     * MODXPlaceholderTag instances cannot be cacheable.
     *
     * {@inheritdoc}
     */
    public function setCacheable($cacheable = true) {}
}
