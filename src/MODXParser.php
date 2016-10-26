<?php
/**
 * Base Parser class for MODXRenderer, almost entirely from modParser class in MODX Revolution.
 * Represents the MODX parser responsible for processing MODX tags.
 *
 * This class encapsulates all of the functions for collecting and evaluating
 * element tags embedded in text content.
 */

namespace SepiaRiver;

class MODXParser
{
    /**
     * Container for MODX placeholders.
     *
     * @var array
     */
    public $data = null;
    /**
     * If the parser is currently processing a tag.
     *
     * @var bool
     */
    protected $_processingTag = false;
    /**
     * If the parser is currently processing an element.
     *
     * @var bool
     */
    protected $_processingElement = false;
    /**
     * If the parser is currently processing an uncacheable tag.
     *
     * @var bool
     */
    protected $_processingUncacheable = false;
    /**
     * If the parser is currently removing all unprocessed tags.
     *
     * @var bool
     */
    protected $_removingUnprocessed = false;
    /**
     * If the parser has ever processed uncacheable.
     *
     * @var bool
     */
    protected $_startedProcessingUncacheable = false;

    /**
     * @param array $data
     */
    public function __construct(array $data)
    {
        $prefixed = [];
        foreach ($data as $k => $v) {
            $prefixed[MODXRenderer::$site_prefix.$k] = $v;
        }
        $this->toPlaceholders($prefixed);
    }

        /**
         * Returns true if the parser is currently processing an uncacheable tag.
         *
         * @return bool
         */
        public function isProcessingUncacheable()
        {
            $result = false;
            if ($this->isProcessingTag() || $this->isProcessingElement()) {
                $result = (bool) $this->_processingUncacheable;
            }

            return $result;
        }

        /**
         * Returns true if the parser has ever processed an uncacheable tag.
         *
         * @return bool
         */
        public function startedProcessingUncacheable()
        {
            return $this->_startedProcessingUncacheable;
        }

        /**
         * Returns true if the parser is currently removing any unprocessed tags.
         *
         * @return bool
         */
        public function isRemovingUnprocessed()
        {
            $result = false;
            if ($this->isProcessingTag() || $this->isProcessingElement()) {
                $result = (bool) $this->_removingUnprocessed;
            }

            return $result;
        }

        /**
         * Returns true if the parser is currently processing a tag.
         *
         * @return bool
         */
        public function isProcessingTag()
        {
            return (bool) $this->_processingTag;
        }

        /**
         * Returns true if the parser is currently processing an element.
         *
         * @return bool
         */
        public function isProcessingElement()
        {
            return (bool) $this->_processingElement;
        }

    public function setProcessingElement($arg = null)
    {
        if (is_bool($arg)) {
            $this->_processingElement = $arg;
        } elseif ($arg === null) {
            $this->_processingElement = !$this->_processingElement ? true : false;
        } else {
            $this->_processingElement = (bool) $arg;
        }
    }

        /**
         * Collects element tags in a string and injects them into an array.
         *
         * @param string $origContent The content to collect tags from
         * @param array  &$matches    An array in which the collected tags will be
         *                            stored (by reference)
         * @param string $prefix      The characters that define the start of a tag
         *                            (default= "[[")
         * @param string $suffix      The characters that define the end of a tag
         *                            (default= "]]")
         *
         * @return int The number of tags collected from the content
         */
        public function collectElementTags($origContent, array &$matches, $prefix = '[[', $suffix = ']]')
        {
            $matchCount = 0;
            if (!empty($origContent) && is_string($origContent) && strpos($origContent, $prefix) !== false) {
                $openCount = 0;
                $offset = 0;
                $openPos = 0;
                $closePos = 0;

                $startPos = strpos($origContent, $prefix);
                $offset = $startPos + strlen($prefix);
                if (($stopPos = strrpos($origContent, $suffix)) === false) {
                    return $matchCount;
                }

                $stopPos = $stopPos + strlen($suffix);
                $length = $stopPos - $startPos;
                $content = $origContent;
                while ($length > 0) {
                    $openCount = 0;
                    $content = substr($content, $startPos);
                    $openPos = 0;
                    $offset = strlen($prefix);
// Pretty sure this can't happen due to above early return
// @codeCoverageIgnoreStart
                    if (($closePos = strpos($content, $suffix, $offset)) === false) {
                        break;
                    }
// @codeCoverageIgnoreEnd
                    $nextOpenPos = strpos($content, $prefix, $offset);
                    while ($nextOpenPos !== false && $nextOpenPos < $closePos) {
                        ++$openCount;
                        $offset = $nextOpenPos + strlen($prefix);
                        $nextOpenPos = strpos($content, $prefix, $offset);
                    }
                    $nextClosePos = strpos($content, $suffix, $closePos + strlen($suffix));
                    while ($openCount > 0 && $nextClosePos !== false) {
                        --$openCount;
                        $closePos = $nextClosePos;
                        $nextOpenPos = strpos($content, $prefix, $offset);
// @TODO figure out how to make this path happen?
// @codeCoverageIgnoreStart
                        while ($nextOpenPos !== false && $nextOpenPos < $closePos) {
                            ++$openCount;
                            $offset = $nextOpenPos + strlen($prefix);
                            $nextOpenPos = strpos($content, $prefix, $offset);
                        }
// @codeCoverageIgnoreEnd
                        $nextClosePos = strpos($content, $suffix, $closePos + strlen($suffix));
                    }
                    $closePos = $closePos + strlen($suffix);

                    $outerTagLength = $closePos - $openPos;
                    $innerTagLength = ($closePos - strlen($suffix)) - ($openPos + strlen($prefix));

                    $matches[$matchCount][0] = substr($content, $openPos, $outerTagLength);
                    $matches[$matchCount][1] = substr($content, ($openPos + strlen($prefix)), $innerTagLength);
                    ++$matchCount;

                    if ($nextOpenPos === false) {
                        $nextOpenPos = strpos($content, $prefix, $closePos);
                    }
                    if ($nextOpenPos !== false) {
                        $startPos = $nextOpenPos;
                        $length = $length - $nextOpenPos;
                    } else {
                        $length = 0;
                    }
                }
            }
            /* Debug with $modx methods:
            if ($this->modx->getDebug() === true && !empty($matches)) {
                $this->modx->log(modX::LOG_LEVEL_DEBUG, "\MODXRenderer\MODXParser::collectElementTags \$matches = " . print_r($matches, 1) . "\n");
                $this->modx->cacheManager->writeFile(MODX_CORE_PATH . 'logs/parser.log', print_r($matches, 1) . "\n", 'a'); */

            return $matchCount;
        }

                /**
                 * Collects and processes any set of tags as defined by a prefix and suffix.
                 *
                 * @param string $parentTag          The tag representing the element processing this
                 *                                   tag.  Pass an empty string to allow parsing without this recursion check
                 * @param string $content            The content to process and act on (by reference)
                 * @param bool   $processUncacheable Determines if noncacheable tags are to
                 *                                   be processed (default= false)
                 * @param bool   $removeUnprocessed  Determines if unprocessed tags should
                 *                                   be left in place in the content, or stripped out (default= false)
                 * @param string $prefix             The characters that define the start of a tag
                 *                                   (default= "[[")
                 * @param string $suffix             The characters that define the end of a tag
                 *                                   (default= "]]")
                 * @param array  $tokens             Indicates that the parser should only process tags
                 *                                   with the tokens included in this array
                 * @param int    $depth              The maximum iterations to recursively process tags
                 *                                   returned by prior passes, 0 by default
                 *
                 * @return int The number of processed tags
                 */
                public function processElementTags($parentTag, &$content, $processUncacheable = false, $removeUnprocessed = false, $prefix = '[[', $suffix = ']]', $tokens = array(), $depth = 0)
                {
                    if ($processUncacheable) {
                        $this->_startedProcessingUncacheable = true;
                    }

                    $_processingTag = $this->_processingTag;
                    $_processingUncacheable = $this->_processingUncacheable;
                    $_removingUnprocessed = $this->_removingUnprocessed;
                    $this->_processingTag = true;
                    $this->_processingUncacheable = (bool) $processUncacheable;
                    $this->_removingUnprocessed = (bool) $removeUnprocessed;
                    $depth = $depth > 0 ? $depth - 1 : 0;
                    $processed = 0;
                    $tags = array();

                    if ($collected = $this->collectElementTags($content, $tags, $prefix, $suffix, $tokens)) {
                        $tagMap = array();
                        foreach ($tags as $tag) {
                            $token = substr($tag[1], 0, 1);
                            if (!$processUncacheable && $token === '!') {
                                if ($removeUnprocessed) {
                                    $tagMap[$tag[0]] = '';
                                }
                            } elseif (!empty($tokens) && !in_array($token, $tokens)) {
                                --$collected;
                                continue;
                            }
                            if ($tag[0] === $parentTag) {
                                $tagMap[$tag[0]] = '';
                                ++$processed;
                                continue;
                            }

                            $tagOutput = $this->processTag($tag, $processUncacheable);

                            if (($tagOutput === null || $tagOutput === false) && $removeUnprocessed) {
                                $tagMap[$tag[0]] = '';
                                ++$processed;
                            } elseif ($tagOutput !== null && $tagOutput !== false) {
                                $tagMap[$tag[0]] = $tagOutput;
                                if ($tag[0] !== $tagOutput) {
                                    ++$processed;
                                }
                            }
                        }

                        $this->mergeTagOutput($tagMap, $content);
                        if ($processed > 0 && $depth > 0) {
                            $processed += $this->processElementTags($parentTag, $content, $processUncacheable, $removeUnprocessed, $prefix, $suffix, $tokens, $depth);
                        }
                    }

                    $this->_removingUnprocessed = $_removingUnprocessed;
                    $this->_processingUncacheable = $_processingUncacheable;
                    $this->_processingTag = $_processingTag;

                    return $processed;
                }

                /**
                 * Merges processed tag output into provided content string.
                 *
                 * @param array  $tagMap  An array with full tags as keys and processed output
                 *                        as the values
                 * @param string $content The content to merge the tag output with (passed by
                 *                        reference)
                 */
                public function mergeTagOutput(array $tagMap, &$content)
                {
                    if (!empty($content) && is_array($tagMap) && !empty($tagMap)) {
                        $content = str_replace(array_keys($tagMap), array_values($tagMap), $content);
                    }
                }

                /**
                 * Parses an element/tag property string or array definition.
                 *
                 * @param string $propSource A valid property string or array source to
                 *                           parse
                 *
                 * @return array An associative array of property values parsed from
                 *               the property string or array definition
                 */
                public function parseProperties($propSource)
                {
                    $properties = array();
                    if (!empty($propSource)) {
                        if (is_string($propSource)) {
                            $properties = $this->parsePropertyString($propSource, true);
                        } elseif (is_array($propSource)) {
                            foreach ($propSource as $propName => &$property) {
                                if (is_array($property) && array_key_exists('value', $property)) {
                                    $properties[$propName] = $property['value'];
                                } else {
                                    $properties[$propName] = &$property;
                                }
                            }
                        }
                    }

                    return $properties;
                }

                /**
                 * Parses an element/tag property string and returns an array of properties.
                 *
                 * @param string $string     The property string to parse
                 * @param bool   $valuesOnly Indicates only the property value should be
                 *                           returned
                 *
                 * @return array The processed properties in array format
                 */
                public function parsePropertyString($string, $valuesOnly = false)
                {
                    $properties = array();
                    $tagProps = $this->escSplit('&', $string);
                    foreach ($tagProps as $prop) {
                        $property = $this->escSplit('=', $prop);
                        if (count($property) == 2) {
                            $propName = $property[0];
                            if (substr($propName, 0, 4) == 'amp;') {
                                $propName = substr($propName, 4);
                            }
                            $propValue = $property[1];
                            $propType = 'textfield';
                            $propDesc = '';
                            $propOptions = array();
                            $pvTmp = $this->escSplit(';', $propValue);
// @codeCoverageIgnoreStart
                            if ($pvTmp && isset($pvTmp[1])) {
                                $propDesc = $pvTmp[0];
                                if (($pvTmp[1] == 'list' || $pvTmp[1] == 'combo') && isset($pvTmp[3]) && $pvTmp[3]) {
                                    if (!$valuesOnly) {
                                        $propType = self::_XType($pvTmp[1]);
                                        $options = explode(',', $pvTmp[2]);
                                        if ($options) {
                                            foreach ($options as $option) {
                                                $propOptions[] = array('name' => ucfirst($option), 'value' => $option);
                                            }
                                        }
                                    }
                                    $propValue = $pvTmp[3];
                                } elseif ($pvTmp[1] != 'list' && $pvTmp[1] != 'combo' && isset($pvTmp[2]) && $pvTmp[2]) {
                                    if (!$valuesOnly) {
                                        $propType = self::_XType($pvTmp[1]);
                                    }
                                    $propValue = $pvTmp[2];
                                } else {
                                    $propValue = $pvTmp[0];
                                }
                            }
// @codeCoverageIgnoreEnd
                            if ($propValue[0] == '`' && $propValue[strlen($propValue) - 1] == '`') {
                                $propValue = substr($propValue, 1, strlen($propValue) - 2);
                            }
                            $propValue = str_replace('``', '`', $propValue);
                            if ($valuesOnly) {
                                $properties[$propName] = $propValue;
                            }
// @codeCoverageIgnoreStart
                            else {
                                $properties[$propName] = array(
                                    'name' => $propName,
                                    'desc' => $propDesc,
                                    'type' => $propType,
                                    'options' => $propOptions,
                                    'value' => $propValue,
                                );
                            }
// @codeCoverageIgnoreEnd
                        }
                    }

                    return $properties;
                }

                /**
                 * Converts legacy property string types to xtypes.
                 *
                 * @param string $type A property type string
                 *
                 * @return string A valid xtype
                 * @codeCoverageIgnore
                 */
                protected function _XType($type)
                {
                    $xtype = $type;
                    switch ($type) {
                        case 'string':
                            $xtype = 'textfield';
                            break;
                        case 'int':
                        case 'integer':
                        case 'float':
                            $xtype = 'numberfield';
                            break;
                        case 'bool':
                        case 'boolean':
                            $xtype = 'checkbox';
                            break;
                        case 'list':
                            break;
                        default:
                            if (!in_array($xtype, array('checkbox', 'combo', 'datefield', 'numberfield', 'radio', 'textarea', 'textfield', 'timefield'))) {
                                $xtype = 'textfield';
                            }
                            break;
                    }

                    return $xtype;
                }

                /**
                 * Processes a modElement tag and returns the result.
                 *
                 * @param string $tag                A full tag string parsed from content
                 * @param bool   $processUncacheable
                 *
                 * @return mixed The output of the processed element represented by the
                 *               specified tag
                 * @require MODXPlaceholderTag MODXChunkTag (new tpl part)
                 */
                public function processTag($tag, $processUncacheable = true)
                {
                    $this->_processingTag = true;
                    $element = null;
                    $elementOutput = null;

                    $outerTag = $tag[0];
                    $innerTag = $tag[1];

                    /* Avoid all processing for comment tags, e.g. [[- comments here]] */

                    if (substr($innerTag, 0, 1) === '-') {
                        return '';
                    }

                    /* collect any nested element tags in the innerTag and process them */
                    $this->processElementTags($outerTag, $innerTag, $processUncacheable);
                    $this->_processingTag = true;
                    $outerTag = '[['.$innerTag.']]';

                    $tagParts = $this->escSplit('?', $innerTag, '`', 2);
                    $tagName = trim($tagParts[0]);
                    $tagPropString = null;
                    if (isset($tagParts[1])) {
                        $tagPropString = trim($tagParts[1]);
                    }

                    $token = substr($tagName, 0, 1);
                    $tokenOffset = 0;
                    $cacheable = true;
                    if ($token === '!') {
                        if (!$processUncacheable) {
                            $this->_processingTag = false;

                            return $outerTag;
                        }

                        $cacheable = false;
                        ++$tokenOffset;
                        $token = substr($tagName, $tokenOffset, 1);
                    }

                    $_restoreProcessingUncacheable = $this->_processingUncacheable;
                    /* stop processing uncacheable tags so they are not cached in the cacheable content */
                    if ($this->_processingUncacheable && $cacheable) {
                        $this->_processingUncacheable = false;
                    }

                    if ($elementOutput === null) {
                        $tagName = substr($tagName, 1 + $tokenOffset);
// this only fails due to bug in coverage (closing brace)
// @codeCoverageIgnoreStart
                        switch ($token) {
                            case '$':
                                $element = new MODXChunkTag($this);
                                break;
                            case '+':
                            default:
                                $element = new MODXPlaceholderTag($this);
                                break;
                        }
// @codeCoverageIgnoreEnd
                        $element->set('name', $tagName);
                        $element->setTag($outerTag);
                        $elementOutput = $element->process($tagPropString);
                    }
// @TODO figure out how to make this path happen?
// @codeCoverageIgnoreStart
                    if (($elementOutput === null || $elementOutput === false) && $outerTag !== $tag[0]) {
                        $elementOutput = $outerTag;
                    }
// @codeCoverageIgnoreEnd
                    /* Debug uses modx methods:
                    if ($this->modx->getDebug() === true) {
                        $this->modx->log(xPDO::LOG_LEVEL_DEBUG, "Processing {$outerTag} as {$innerTag} using tagname {$tagName}:\n" . print_r($elementOutput, 1) . "\n\n");
                        $this->modx->cacheManager->writeFile(MODX_BASE_PATH . 'parser.log', "Processing {$outerTag} as {$innerTag}:\n" . print_r($elementOutput, 1) . "\n\n", 'a');
                    }*/
                    $this->_processingTag = false;
                    $this->_processingUncacheable = $_restoreProcessingUncacheable;

                    return $elementOutput;
                }

        /**
         * Gets the real name of an element containing filter modifiers.
         *
         * @param string $unfiltered The unfiltered name of a {@link modElement}
         *
         * @return string The name minus any filter modifiers
         * @codeCoverageIgnore
         * @TODO: output filters
         */
        public function realname($unfiltered)
        {
            $filtered = $unfiltered;
            $split = $this->escSplit(':', $filtered);
            if ($split && isset($split[0])) {
                $filtered = $split[0];
                $propsetSplit = $this->escSplit('@', $filtered);
                if ($propsetSplit && isset($propsetSplit[0])) {
                    $filtered = $propsetSplit[0];
                }
            }

            return $filtered;
        }

        /**
         * Sets a placeholder value.
         *
         * @param string $key   The unique string key which identifies the
         *                      placeholder
         * @param mixed  $value The value to set the placeholder to
         */
        public function setPlaceholder($key, $value)
        {
            if (is_string($key)) {
                $this->data[$key] = $value;
            }
        }

        /**
         * Sets a collection of placeholders stored in an array or as object vars.
         *
         * An optional namespace parameter can be prepended to each placeholder key in the collection,
         * to isolate the collection of placeholders.
         *
         * Note that unlike toPlaceholders(), this function does not add separators between the
         * namespace and the placeholder key. Use toPlaceholders() when working with multi-dimensional
         * arrays or objects with variables other than scalars so each level gets delimited by a
         * separator.
         *
         * @param array|object $placeholders An array of values or object to set as placeholders
         * @param string       $namespace    A namespace prefix to prepend to each placeholder key
         */
        public function setPlaceholders($placeholders, $namespace = '')
        {
            $this->toPlaceholders($placeholders, $namespace, '');
        }

        /**
         * Sets placeholders from values stored in arrays and objects.
         *
         * Each recursive level adds to the prefix, building an access path using an optional separator.
         *
         * @param array|object $subject   An array or object to process
         * @param string       $prefix    An optional prefix to be prepended to the placeholder keys. Recursive
         *                                calls prepend the parent keys
         * @param string       $separator A separator to place in between the prefixes and keys. Default is a
         *                                dot or period: '.'
         * @param bool         $restore   Set to true if you want overwritten placeholder values returned
         *
         * @return array A multi-dimensional array containing up to two elements: 'keys' which always
         *               contains an array of placeholder keys that were set, and optionally, if the restore parameter
         *               is true, 'restore' containing an array of placeholder values that were overwritten by the method
         */
        public function toPlaceholders($subject, $prefix = '', $separator = '.', $restore = false)
        {
            $keys = array();
            $restored = array();
            if (is_object($subject)) {
                $subject = get_object_vars($subject);
            }
            if (is_array($subject)) {
                foreach ($subject as $key => $value) {
                    $rv = $this->toPlaceholder($key, $value, $prefix, $separator, $restore);
                    if (isset($rv['keys'])) {
                        foreach ($rv['keys'] as $rvKey) {
                            $keys[] = $rvKey;
                        }
                    }
                    if ($restore === true && isset($rv['restore'])) {
                        $restored = array_merge($restored, $rv['restore']);
                    }
                }
            }
            $return = array('keys' => $keys);
            if ($restore === true) {
                $return['restore'] = $restored;
            }

            return $return;
        }

        /**
         * Recursively validates and sets placeholders appropriate to the value type passed.
         *
         * @param string $key       The key identifying the value
         * @param mixed  $value     The value to set
         * @param string $prefix    A string prefix to prepend to the key. Recursive calls prepend the
         *                          parent keys as well
         * @param string $separator A separator placed in between the prefix and the key. Default is a
         *                          dot or period: '.'
         * @param bool   $restore   Set to true if you want overwritten placeholder values returned
         *
         * @return array A multi-dimensional array containing up to two elements: 'keys' which always
         *               contains an array of placeholder keys that were set, and optionally, if the restore parameter
         *               is true, 'restore' containing an array of placeholder values that were overwritten by the method
         */
        public function toPlaceholder($key, $value, $prefix = '', $separator = '.', $restore = false)
        {
            $return = array('keys' => array());
            if ($restore === true) {
                $return['restore'] = array();
            }
            if (!empty($prefix) && !empty($separator)) {
                $prefix .= $separator;
            }
            if (is_array($value) || is_object($value)) {
                $return = $this->toPlaceholders($value, "{$prefix}{$key}", $separator, $restore);
            } elseif (is_scalar($value)) {
                $return['keys'][] = "{$prefix}{$key}";
                if ($restore === true && array_key_exists("{$prefix}{$key}", $this->data)) {
                    $return['restore']["{$prefix}{$key}"] = $this->getPlaceholder("{$prefix}{$key}");
                }
                $this->setPlaceholder("{$prefix}{$key}", $value);
            }

            return $return;
        }

        /**
         * Get a placeholder value by key.
         *
         * @param string $key The key of the placeholder to a return a value from
         *
         * @return mixed The value of the requested placeholder, or an empty string if not located
         */
        public function getPlaceholder($key)
        {
            $placeholder = null;
            if (is_string($key) && array_key_exists($key, $this->data)) {
                $placeholder = &$this->data[$key];
            }

            return $placeholder;
        }

        /**
         * Unset a placeholder value by key.
         *
         * @param string $key The key of the placeholder to unset
         */
        public function unsetPlaceholder($key)
        {
            if (is_string($key) && array_key_exists($key, $this->data)) {
                unset($this->data[$key]);
            }
        }

        /**
         * Unset multiple placeholders, either by prefix or an array of keys.
         *
         * @param string|array $keys A string prefix or an array of keys indicating
         *                           the placeholders to unset
         */
        public function unsetPlaceholders($keys)
        {
            if (is_array($keys)) {
                foreach ($keys as $key) {
                    if (is_string($key)) {
                        $this->unsetPlaceholder($key);
                    }
                    if (is_array($key)) {
                        $this->unsetPlaceholders($key);
                    }
                }
            } elseif (is_string($keys)) {
                $placeholderKeys = array_keys($this->data);
                foreach ($placeholderKeys as $key) {
                    if (strpos($key, $keys) === 0) {
                        $this->unsetPlaceholder($key);
                    }
                }
            }
        }

            /**
             * Process and return the output from a Chunk by name.
             *
             * @param string $chunkName  The name of the chunk
             * @param array  $properties An associative array of properties to process
             *                           the Chunk with, treated as placeholders within the scope of the Element
             *
             * @return string The processed output of the Chunk
             */
            public function getChunk($chunkName, array $properties = array())
            {
                $output = '';

                $chunk = new MODXChunkTag($this);
                $chunk->set('name', $chunkName);
                $chunk->set('properties', $properties);
                $chunk->setCacheable(false);
                $output = $chunk->process($properties);

                return $output;
            }

            /**
             * Splits a string on a specified character, ignoring escaped content.
             *
             *
             * @param string $char     A character to split the tag content on
             * @param string $str      The string to operate on
             * @param string $escToken A character used to surround escaped content; all
             *                         content within a pair of these tokens will be ignored by the split
             *                         operation
             * @param int    $limit    Limit the number of results. Default is 0 which is
             *                         no limit. Note that setting the limit to 1 will only return the content
             *                         up to the first instance of the split character and will discard the
             *                         remainder of the string
             *
             * @return array An array of results from the split operation, or an empty
             *               array
             */
            public function escSplit($char, $str, $escToken = '`', $limit = 0)
            {
                $split = array();
                $charPos = strpos($str, $char);
                if ($charPos !== false) {
                    if ($charPos === 0) {
                        $searchPos = 1;
                        $startPos = 1;
                    } else {
                        $searchPos = 0;
                        $startPos = 0;
                    }
                    $escOpen = false;
                    $strlen = strlen($str);
                    for ($i = $startPos; $i <= $strlen; ++$i) {
                        if ($i == $strlen) {
                            $tmp = trim(substr($str, $searchPos));
                            if (!empty($tmp)) {
                                $split[] = $tmp;
                            }
                            break;
                        }
                        if ($str[$i] == $escToken) {
                            $escOpen = $escOpen == true ? false : true;
                            continue;
                        }
                        if (!$escOpen && $str[$i] == $char) {
                            $tmp = trim(substr($str, $searchPos, $i - $searchPos));
                            if (!empty($tmp)) {
                                $split[] = $tmp;
// @codeCoverageIgnoreStart
                                if ($limit > 0 && count($split) >= $limit) {
                                    break;
                                }
// @codeCoverageIgnoreEnd
                            }
                            $searchPos = $i + 1;
                        }
                    }
                } else {
                    $split[] = trim($str);
                }

                return $split;
            }
}
