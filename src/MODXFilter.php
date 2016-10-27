<?php
/**
 * Modifies the output of a MODXTag object.
 * Can be extended to add more filtering functions.
 *
 */

namespace SepiaRiver;

class MODXFilter
{
    /**
     * The input content to filter. Modified in place.
     *
     * @var string
     */
    protected $input = '';
    /**
     * The current condition state
     *
     * @var string
     */
    protected $condition = null;
    /**
     * @constructor
     */
    public function __construct()
    {

    }
    /**
     * Set $condition. Can only be performed internally.
     */
    protected function setCondition($state) {
        if ($state === null){
            $this->condition = null;
        } else {
            $this->condition = boolval($state);
        }
    }
    /**
     * Get $condition
     */
    public function getCondition() {
        return $this->condition;
    }
    /**
     * Prepare filter arguments
     *
     * @return array $result
     */
    protected function getArgs($args) {
        $result = [];
        if (is_string($args)) {
            $decoded = json_decode(trim($args), true);
            if (is_array($decoded)) {
                $result = $decoded;
            } else {
                $result['value'] = $args;
            }
        }
        return $result;
    }
    /**
     * "is" filter conditional
     * conditional filters don't modify the input, only evaluate it and cache results
     */
    public function is($input, $args) {
        $args = $this->getArgs($args);
        if (isset($args['value'])) {
            $this->setCondition(($input == $args['value']));
        }
    }

    /**
     * "isnot" filter conditional
     * conditional filters don't modify the input, only evaluate it and cache results
     */
    public function isnot($input, $args) {
        $args = $this->getArgs($args);
        if (isset($args['value'])) {
            $this->setCondition(($input != $args['value']));
        }
    }

    /**
     * "then" filter
     * result depends on $condition. filters modify input in place.
     */
    public function then(&$input, $args) {
        $args = $this->getArgs($args);
        if (isset($args['value']) && ($this->getCondition() === true)) {
            $input = $args['value'];
            $this->setCondition(null);
        }
        // otherwise nothing happens
    }
    
    /**
     * "else" filter
     * result depends on $condition. filters modify input in place.
     */
    public function else(&$input, $args) {
        $args = $this->getArgs($args);
        if (isset($args['value']) && ($this->getCondition() === false)) {
            $input = $args['value'];
            $this->setCondition(null);
        }
        // otherwise nothing happens
    }
    
    /**
     * "default" filter
     * if input is empty output value from $args
     */
    public function default(&$input, $args) 
    {
        $args = $this->getArgs($args);
        if ((strlen($input) === 0) && isset($args['value'])) {
            $input = $args['value'];
        }
    }
    
    /**
     * "notempty" filter
     * if input is not empty output value from args
     */
    public function notempty(&$input, $args) 
    {
        $args = $this->getArgs($args);
        if ((strlen($input) > 0) && isset($args['value'])) {
            $input = $args['value'];
        }
    }
    
    /**
     * "trim" filter
     * calls php trim()
     */
    public function trim(&$input, $args)
    {
        $args = $this->getArgs($args);
        if (isset($args['value'])) {
            $input = trim($input, $args['value']);
        } else {
            $input = trim($input);
        }
    }
    
    /**
     * "replace" filter
     * calls php str_replace()
     */
    public function replace(&$input, $args)
    {
        $args = $this->getArgs($args);
        
    }

}
