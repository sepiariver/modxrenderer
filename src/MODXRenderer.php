<?php
/**
 * MODXRenderer for Slim
 *
 * @link        https://github.com/sepiariver/MODXRenderer
 * @copyright   Copyright (c) 2016 YJ Tso @sepiariver
 * @license     https://github.com/slimphp/PHP-View/blob/master/LICENSE.md (MIT License)
 *              https://github.com/modxcms/revolution/blob/2.x/LICENSE.md (GPL)
 */
namespace MODXRenderer;

use \InvalidArgumentException;
use Psr\Http\Message\ResponseInterface;
use MODXRenderer\MODXParser;

/**
 * Class MODXRenderer
 * @package MODXRenderer
 *
 * Render MODX Templates and Chunks into a PSR-7 Response object
 */
 //extends MODXParser
class MODXRenderer extends MODXParser
{
    /**
     * Template path
     * @var string
     */
    public static $template_path;
    /**
     * Chunk path
     * @var string
     */
    public static $chunk_path;
    /**
     * Site Settings to prefix MODXParser data.
     * @var string
     */
    public static $site_prefix = '+';
    /**
     * Reference to renderer key in container
     * @var string
     */
    public static $service_name;
    /**
     * Container for site settings.
     * @var array
     */
    private $attributes = [];

    /**
     * MODXRenderer constructor.
     *
     * @param array $settings
     */
    public function __construct(array $rendererSettings, array $siteSettings = [])
    {
        self::setStaticData($rendererSettings);
        $this->setAttributes($siteSettings);
        parent::__construct($this->attributes);
    }
    /**
     * Set required CONSTANTS
     *
     * throws InvalidArgumentException if $config doesn't contain the required directory paths
     *
     * @param array             $config
     *
     * @throws \InvalidArgumentException
     */
    public static function setStaticData(array $config)
    {
        if (
            !isset($config['template_path']) ||
            !is_dir($config['template_path']) ||
            !isset($config['chunk_path']) ||
            !is_dir($config['chunk_path'])
            )
        {
            throw new \InvalidArgumentException("MODXRenderer requires template_path and chunk_path.");
        }
        self::$template_path = rtrim($config['template_path'], '/\\') . '/';
        self::$chunk_path = rtrim($config['chunk_path'], '/\\') . '/';
        if (!empty($config['site_prefix'])) self::$site_prefix = $config['site_prefix'];
        return true;
    }
    /**
     * Render a template
     *
     * $data cannot contain template as a key
     *
     * throws RuntimeException if $templatePath . $template does not exist
     *
     * @param ResponseInterface $response
     * @param string             $template
     * @param array              $data
     *
     * @return ResponseInterface
     *
     * @throws \InvalidArgumentException
     * @throws \RuntimeException
     */
    public function render(ResponseInterface $response, $template, array $data = [])
    {
        $output = $this->fetch($template, $data);

        $response->getBody()->write($output);

        return $response;
    }

    /**
     * Get the attributes for the renderer
     *
     * @return array
     */
    public function getAttributes()
    {
        return $this->attributes;
    }

    /**
     * Set the attributes for the renderer
     *
     * @param array $attributes
     */
    public function setAttributes(array $attributes)
    {
        $this->attributes = $attributes;
    }

    /**
     * Add an attribute
     *
     * @param $key
     * @param $value
     */
    public function addAttribute($key, $value) {
        $this->attributes[$key] = $value;
    }

    /**
     * Retrieve an attribute
     *
     * @param $key
     * @return mixed
     */
    public function getAttribute($key) {
        if (!isset($this->attributes[$key])) {
            return false;
        }

        return $this->attributes[$key];
    }

    /**
     * Renders a template and returns the result as a string
     *
     * cannot contain template as a key
     *
     * throws RuntimeException if $templatePath . $template does not exist
     *
     * @param $template
     * @param array $data
     *
     * @return mixed
     *
     * @throws \InvalidArgumentException
     * @throws \RuntimeException
     */
    public function fetch($template, array $data = []) {
        if (isset($data['template'])) {
            throw new \InvalidArgumentException("Duplicate template key found");
        }

        if (!is_file(self::$template_path . $template)) {
            throw new \RuntimeException("View cannot render `$template` because the template does not exist");
        }

        try {
            ob_start();
            $this->protectedIncludeScope(self::$template_path . $template, $data);
            $output = ob_get_clean();
        } // @codeCoverageIgnoreStart
        catch(\Throwable $e) { // PHP 7+
            ob_end_clean();
            throw $e;
        }
        catch(\Exception $e) { // PHP < 7
            ob_end_clean();
            throw $e;
        }
        // @codeCoverageIgnoreEnd
        return $output;
    }

    /**
     * @param string $template
     * @param array $data
     */
    protected function protectedIncludeScope ($template, array $data) {
        $content = file_get_contents($template);
        // Placeholders were set in constructor. Merge with live data.
        $this->data = array_merge($this->data, $data);
        $this->processElementTags('', $content, true, false, '[[', ']]', array(), 10);
        echo $content;
    }

}
