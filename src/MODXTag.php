<?php
/**
 * Abstract class representing a pseudo-element that can be parsed.
 *
 * @abstract You must implement the process() method on derivatives to implement
 * a parseable element tag.  All element tags are identified by a unique single
 * character token at the beginning of the tag string.
 */

namespace SepiaRiver;

abstract class MODXTag
{
    /**
     * A reference to the MODXParser.
     *
     * @var MODXParser
     */
    public $parser = null;
    /**
     * The name of the tag.
     *
     * @var string
     */
    public $name;
    /**
     * The properties on the tag.
     *
     * @var array
     */
    public $properties;
    /**
     * The content of the tag.
     *
     * @var string
     */
    public $_content = null;
    /**
     * The processed output of the tag.
     *
     * @var string
     */
    public $_output = '';
    /**
     * The result of processing the tag.
     *
     * @var bool
     */
    public $_result = true;
    /**
     * Just the isolated properties part of the tag string.
     *
     * @var string
     */
    public $_propertyString = '';
    /**
     * The arranged properties array for this tag.
     *
     * @var array
     */
    public $_properties = array();
    /**
     * Whether or not the tag has been processed.
     *
     * @var bool
     */
    public $_processed = false;
    /**
     * The tag string.
     *
     * @var string
     */
    public $_tag = '';
    /**
     * The tag initial token ($,%,*,etc).
     *
     * @var string
     */
    public $_token = '';
    /**
     * Fields on the tag.
     *
     * @var array
     */
    public $_fields = array(
        'name' => '',
        'properties' => '',
    );
    /**
     * Whether or not this tag is marked as cacheable.
     *
     * @var bool
     */
    public $_cacheable = true;
    /**
     * Any output/input filter on this tag.
     *
     * @var object
     */
    public $_filter = null;
    /**
     * Filter methods on this tag.
     *
     * @var array
     */
    public $_filterMethods = [];
    /**
     * Filter arguments on this tag.
     *
     * @var array
     */
    public $_filterArgs = [];

    /**
     * Set a reference to the modX object, load the name and properties, and instantiate the tag class instance.
     *
     * @param MODXParser $parser A reference to the SepiaRiver\MODXParser object
     */
    public function __construct(MODXParser $parser)
    {
        $this->parser = $parser;
        $this->name = &$this->_fields['name'];
        $this->properties = &$this->_fields['properties'];
    }

    /**
     * Generic getter method for MODXTag attributes.
     *
     * @see xPDOObject::get()
     *
     * @param string $k The field key
     *
     * @return mixed The value of the field or null if it is not set
     */
    public function get($k)
    {
        $value = null;
        if (array_key_exists($k, $this->_fields)) {
            if ($k == 'properties') {
                $value = is_string($this->_fields[$k]) && !empty($this->_fields[$k])
                    ? unserialize($this->_fields[$k])
                    : array();
            } else {
                $value = $this->_fields[$k];
            }
        }

        return $value;
    }
    /**
     * Generic setter method for MODXTag attributes.
     *
     * @see xPDOObject::set()
     *
     * @param string $k The field key
     * @param mixed  $v The value to assign to the field
     */
    public function set($k, $v)
    {
        if ($k === 'properties') {
            $v = is_array($v) ? serialize($v) : $v;
        }
        $this->_fields[$k] = $v;

        return $this->_fields[$k] === $v;
    }

    /**
     * Returns the current token for the tag.
     *
     * @return string The token for the tag
     */
    public function getToken()
    {
        return $this->_token;
    }

    /**
     * Setter method for the token class var.
     *
     * @param string $token The token to use for this element tag
     */
    public function setToken($token)
    {
        $this->_token = $token;
    }

    /**
     * Setter method for the tag class var.
     *
     * @param string $tag The tag to use for this element
     */
    public function setTag($tag)
    {
        $this->_tag = $tag;
    }

    /**
     * Gets a tag representation of the MODXTag instance.
     *
     * @return string
     */
    public function getTag()
    {
        if (empty($this->_tag) && ($name = $this->get('name'))) {
            $propTemp = array();
            if (empty($this->_propertyString) && !empty($this->_properties)) {
                while (list($key, $value) = each($this->_properties)) {
                    $propTemp[] = trim($key).'=`'.$value.'`';
                }
                if (!empty($propTemp)) {
                    $this->_propertyString = '?'.implode('&', $propTemp);
                }
            }
            $tag = '[[';
            $tag .= $this->getToken();
            $tag .= $name;
            if (!empty($this->_propertyString)) {
                $tag .= $this->_propertyString;
            }
            $tag .= ']]';
            $this->_tag = $tag;
        }
// @codeCoverageIgnoreStart
        if (empty($this->_tag)) {
            //$this->modx->log(xPDO::LOG_LEVEL_ERROR, 'Instance of ' . get_class($this) . ' produced an empty tag!');
        }
// @codeCoverageIgnoreEnd
        return $this->_tag;
    }

    /**
     * Process the tag and return the result.
     *
     * @see modElement::process()
     *
     * @param array|string $properties An array of properties or a formatted
     *                                 property string
     * @param string       $content    Optional content to use for the element
     *                                 processing
     *
     * @return mixed The result of processing the tag
     */
    public function process($properties = null, $content = null)
    {
        $this->parser->setProcessingElement(true);
        $this->getProperties($properties);

        $this->getTag();
        $this->getContent(is_string($content) ? array('content' => $content) : array());
        $this->setFilters();

        return $this->_result;
    }

    /**
     * Get an output filter instance configured for this Element.
     *
     * @return MODXFilter|null An output filter instance (or null if one cannot be loaded)
     */
    public function getFilter() {
        if (!empty($this->_filterMethods) && (!$this->_filter || !($this->_filter instanceof MODXFilter))) {
            //@TODO support customizing filter class?
            $this->_filter = new MODXFilter();
        }
        return $this->_filter;
    }
    /**
     * Set filters for this tag.
     *
     */
    protected function setFilters() {
        $output = $this->get('name');
        $name = $output;
        $splitPos = strpos($output, ':');
        if ($splitPos !== false && $splitPos > 0) {
            $matches = array ();
            $name = substr($output, 0, $splitPos);
            $modifiers = substr($output, $splitPos);
            if (preg_match_all('~:([^:=]+)(?:=`(.*?)`(?=:[^:=]+|$))?~s', $modifiers, $matches)) {
                $this->_filterMethods = $matches[1]; /* filter methods */
                $this->_filterArgs = $matches[2]; /* filter arguments */
            }
        }
        $this->set('name', $name);
    }

    /**
     * Apply an output filter to a tag.
     *
     * Call this method in your {MODXTag::process()} implementation when it is
     * appropriate, typically once all processing has been completed, but before
     * any caching takes place.
     *
     * @see modElement::filterOutput()
     */
    public function filterOutput() {
        $filter = $this->getFilter();
        if ($filter) {
            foreach ($this->_filterMethods as $i => $method) {
                $filter->$method($this->_output, $this->_filterArgs[$i]);
            }
        }
    }

    /**
     * Get the raw source content of the tag element.
     *
     * @param array $options An array of options implementations can use to
     *                       accept language, revision identifiers, or other information to alter the
     *                       behavior of the method
     *
     * @return string The raw source content for the element
     * @codeCoverageIgnore subclasses override this
     */
    public function getContent(array $options = array())
    {
        if (!$this->isCacheable() || !is_string($this->_content) || $this->_content === '') {
            if (isset($options['content'])) {
                $this->_content = $options['content'];
            } else {
                $this->_content = $this->get('name');
            }
        }

        return $this->_content;
    }

    /**
     * Set the raw source content for the tag element.
     *
     * @param string $content The content to set
     * @param array  $options Ignored
     *
     * @return bool
     */
    public function setContent($content, array $options = array())
    {
        return $this->set('name', $content);
    }

    /**
     * Get the properties for this element instance for processing.
     *
     * @param array|string $properties An array or string of properties to apply
     *
     * @return array A simple array of properties ready to use for processing
     */
    public function getProperties($properties = null)
    {
        $this->_properties = $this->parser->parseProperties($this->get('properties'));
        /*TODO: $set= $this->getPropertySet();
        if (!empty($set)) {
            $this->_properties= array_merge($this->_properties, $set);
        }*/

        if (!empty($properties)) {
            $this->_properties = array_merge($this->_properties, $this->parser->parseProperties($properties));
        }

        if (!empty($this->parser->data)) {
            $this->_properties = array_merge($this->_properties, $this->parser->parseProperties($this->parser->data));
        }

        return $this->_properties;
    }

    /**
     * Set default properties for this element instance.
     *
     * @param array|string $properties A property array or property string
     * @param bool         $merge      Indicates if properties should be merged with
     *                                 existing ones
     *
     * @return bool true if the properties are set
     */
    public function setProperties($properties, $merge = false)
    {
        $set = false;
        $propertyArray = array();
        if (is_string($properties)) {
            $properties = $this->parser->parsePropertyString($properties, true);
        }
        if (is_array($properties)) {
            foreach ($properties as $propKey => $property) {
                if (is_array($property) && isset($property[5])) {
                    $propertyArray[$property[0]] = array(
                        'name' => $property[0],
                        'desc' => $property[1],
                        'type' => $property[2],
                        'options' => $property[3],
                        'value' => $property[4],
                    );
                } // @codeCoverageIgnoreStart
                elseif (is_array($property) && isset($property['value'])) {
                    $propertyArray[$property['name']] = array(
                        'name' => $property['name'],
                        'desc' => isset($property['description']) ? $property['description'] : (isset($property['desc']) ? $property['desc'] : ''),
                        'type' => isset($property['xtype']) ? $property['xtype'] : (isset($property['type']) ? $property['type'] : 'textfield'),
                        'options' => isset($property['options']) ? $property['options'] : array(),
                        'value' => $property['value'],
                    );
                } // @codeCoverageIgnoreEnd
                else {
                    $propertyArray[$propKey] = array(
                        'name' => $propKey,
                        'desc' => '',
                        'type' => 'textfield',
                        'options' => array(),
                        'value' => $property,
                    );
                }
            }
            if ($merge && !empty($propertyArray)) {
                $existing = $this->get('properties');
                if (is_array($existing) && !empty($existing)) {
                    $propertyArray = array_merge($existing, $propertyArray);
                }
            }
            $set = $this->set('properties', $propertyArray);
        }

        return $set;
    }

    /**
     * Indicates if the element is cacheable.
     *
     * @return bool True if the element can be stored to or retrieved from
     *              the element cache
     * @codeCoverageIgnore
     */
    public function isCacheable()
    {
        return $this->_cacheable;
    }

    /**
     * Sets the runtime cacheability of the element.
     *
     * @param bool $cacheable Indicates the value to set for cacheability of
     *                        this element
     * @codeCoverageIgnore
     */
    public function setCacheable($cacheable = true)
    {
        $this->_cacheable = (bool) $cacheable;
    }

    /*
     * Gets a named property set to use with this MODXTag instance.
     *
     * This function will attempt to extract a setName from the tag name using the
     * @ symbol to delimit the name of the property set. If a setName parameter is provided,
     * the function will override any property set specified in the name by merging both
     * property sets.
     *
     * Here is an example of an tag using the @ modifier to specify a property set name:
     *  [[~TagName@PropertySetName:FilterCommand=`FilterModifier`?
     *      &PropertyKey1=`PropertyValue1`
     *      &PropertyKey2=`PropertyValue2`
     *  ]]
     *
     * @param string|null $setName An explicit property set name to search for.
     * @return array|null An array of properties or null if no set is found.
     */
     /* TODO: property set support
    public function getPropertySet($setName = null) {
        $propertySet= null;
        $name = $this->get('name');
        if (strpos($name, '@') !== false) {
            $psName= '';
            $split= xPDO\xPDO :: escSplit('@', $name);
            if ($split && isset($split[1])) {
                $name= $split[0];
                $psName= $split[1];
                $filters= xPDO\xPDO :: escSplit(':', $setName);
                if ($filters && isset($filters[1]) && !empty($filters[1])) {
                    $psName= $filters[0];
                    $name.= ':' . $filters[1];
                }
                $this->set('name', $name);
            }
            if (!empty($psName)) {
                $psObj= $this->modx->getObject('modPropertySet', array('name' => $psName));
                if ($psObj) {
                    $propertySet= $this->parser->parseProperties($psObj->get('properties'));
                }
            }
        }
        if (!empty($setName)) {
            $propertySetObj= $this->modx->getObject('modPropertySet', array('name' => $setName));
            if ($propertySetObj) {
                if (is_array($propertySet)) {
                    $propertySet= array_merge($propertySet, $this->parser->parseProperties($propertySetObj->get('properties')));
                } else {
                    $propertySet= $this->parser->parseProperties($propertySetObj->get('properties'));
                }
            }
        }
        return $propertySet;
    }*/
}
