<?php namespace WordpressFoundation;
/**
 * WordpressFoundation Utilities
 *
 * @package WordpressFoundation
 * @author Brian Greenacre <bgreenacre42@gmail.com>
 * @version $id$
 */

/**
 * Input class handles any global inputs from the request.
 * 
 * @package WordpressFoundation
 * @author Brian Greenacre <bgreenacre42@gmail.com>
 * @version $id$
 */
class Input {

    /**
     * $post array
     *
     * @access protected
     * @var array
     */
    protected $post = array();

    /**
     * $_GET array
     *
     * @access protected
     * @var array
     */
    protected $query = array();

    /**
     * $cookies array
     *
     * @access protected
     * @var array
     */
    protected $cookies = array();

    /**
     * $files array
     *
     * @access protected
     * @var array
     */
    protected $files = array();

    /**
     * Optionally set the data arrays on object instantiation.
     *
     * @access public
     * @param array $data Associative data array
     * @return void
     */
    public function __construct(array $data = array())
    {
        if ($post = array_get($data, 'post'))
        {
            // Set post array
            $this->setPost($post);
        }

        if ($query = array_get($data, 'query'))
        {
            // Set query array
            $this->setQuery($query);
        }

        if ($cookies = array_get($data, 'cookies'))
        {
            // Set cookie array
            $this->setCookies($cookies);
        }

        if ($files = array_get($data, 'files'))
        {
            // Set files array
            $this->setFiles($files);
        }
    }

    /**
     * Sanitize values.
     *
     * @access public
     * @param  mixed $value Value to sanitize.
     * @return mixed        Sanitized value.
     */
    public function sanitize($value)
    {
        if (is_array($value) OR is_object($value))
        {
            foreach ($value as $key => $val)
            {
                // Recursively clean each value
                $value[$key] = $this->sanitize($val);
            }
        }
        elseif (is_string($value))
        {
            if ( (version_compare(PHP_VERSION, '5.4') < 0 && get_magic_quotes_gpc()) === true)
            {
                // Remove slashes added by magic quotes
                $value = stripslashes($value);
            }

            if (strpos($value, "\r") !== FALSE)
            {
                // Standardize newlines
                $value = str_replace(array("\r\n", "\r"), "\n", $value);
            }
        }

        return $value;
    }

    /**
     * Get the request method.
     *
     * @access public
     * @return string Request method.
     */
    public function getRequestMethod()
    {
        return $_SERVER['REQUEST_METHOD'];
    }

    /**
     * Get post value.
     * 
     * @param  string $key     Get index or entire array if null.
     * @param  mixed  $default Return this value if key not found.
     * @return mixed           Entire array or key value.
     */
    public function getPost($key = null, $default = null)
    {
        if ($key === null)
        {
            return $this->post;
        }

        return array_get($this->post, $key, $default);
    }

    /**
     * Set the post array.
     *
     * @access public
     * @param  array $post Associative array of data.
     * @return $this
     */
    public function setPost(array $post)
    {
        $this->post = $this->sanitize($post);

        return $this;
    }

    /**
     * Get query value.
     * 
     * @param  string $key     Get index or entire array if null.
     * @param  mixed  $default Return this value if key not found.
     * @return mixed           Entire array or key value.
     */
    public function getQuery($key = null, $default = null)
    {
        if ($key === null)
        {
            return $this->query;
        }

        return array_get($this->query, $key, $default);
    }

    /**
     * Set the query array.
     *
     * @access public
     * @param  array $query Associative array of data.
     * @return $this
     */
    public function setQuery(array $query)
    {
        $this->query = $this->sanitize($query);

        return $this;
    }

    /**
     * Get cookie value.
     * 
     * @param  string $key     Get index or entire array if null.
     * @param  mixed  $default Return this value if key not found.
     * @return mixed           Entire array or key value.
     */
    public function getCookie($key = null, $default = null)
    {
        if ($key === null)
        {
            return $this->cookies;
        }

        return array_get($this->cookies, $key, $default);
    }

    /**
     * Set the cookie array.
     *
     * @access public
     * @param  array $cookies Associative array of data.
     * @return $this
     */
    public function setCookies(array $cookies)
    {
        $this->cookies = $this->sanitize($cookies);

        return $this;
    }

    /**
     * Get files.
     *
     * @access public
     * @return mixed  Entire array or key value.
     */
    public function getFiles()
    {
        return $this->files;
    }

    /**
     * Set the files array.
     *
     * @access public
     * @param  array $files Array of files uploaded to server.
     * @return $this
     */
    public function setFiles(array $files)
    {
        $this->files = $this->sanitize($files);

        return $this;
    }

}