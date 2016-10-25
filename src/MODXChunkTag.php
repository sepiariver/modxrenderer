<?php
/**
 * Represents chunk tags.
 *
 * [[$chunk_name]] Represents a chunk with name chunk_name.
 */

namespace MODXRenderer;

class MODXChunkTag extends MODXTag
{
    /**
     * Overrides MODXTag::__construct to set the Placeholder Tag token
     * {@inheritdoc}
     */
    public function __construct(MODXParser $parser, $maxIterations = 10)
    {
        parent :: __construct($parser);
        $this->setCacheable(false);
        $this->setToken('$');
        $this->maxIterations = $maxIterations;
        $this->chunkPath = MODXRenderer::$chunk_path;
    }

    /**
     * Processes the MODXChunkTag, recursively processing nested tags.
     *
     * Tags in the properties of the tag itself, or the content returned by the
     * tag element are processed.  Non-cacheable nested tags are only processed
     * if this tag element is also non-cacheable.
     *
     * {@inheritdoc}
     */
    public function process($properties = null, $content = null)
    {
        parent :: process($properties, $content);
        if (!$this->_processed) {
            $this->_output = $this->_content;
            if ($this->_output !== null && is_string($this->_output) && !empty($this->_output)) {

                /* turn the processed properties into placeholders */
                $scope = $this->parser->toPlaceholders($this->_properties, '', '.', true);
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
            /* remove the placeholders set from the properties of this element and restore global values */
            if (isset($scope['keys'])) {
                $this->parser->unsetPlaceholders($scope['keys']);
            }
            if (isset($scope['restore'])) {
                $this->parser->toPlaceholders($scope['restore']);
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
    public function getContent(array $options = array())
    {
        if (!is_string($this->_content)) {
            if (isset($options['content'])) {
                $this->_content = $options['content'];
            } else {
                $chunk = $this->chunkPath.$this->get('name').'.tpl';
                $this->_content = file_get_contents($chunk);
            }
        }

        return $this->_content;
    }

    /**
     * MODXChunkTag instances cannot be cacheable.
     *
     * @return bool Always returns false
     */
    public function isCacheable()
    {
        return false;
    }

    /**
     * MODXChunkTag instances cannot be cacheable.
     *
     * {@inheritdoc}
     */
    public function setCacheable($cacheable = false)
    {
    }
}
