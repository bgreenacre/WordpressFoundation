<?php namespace Kummerspeck\Wordpress;

class Response {

    /**
     * Plugin container object.
     *
     * @access protected
     * @var PluginContainer
     */
    protected $_container;

    /**
     * Construct object.
     *
     * @access public
     * @param PluginContainer $container Plugins container object.
     * @return void
     */
    public function __construct(PluginContainer $container)
    {
        $this->setContainer($container);
    }

    /**
     * Render a response string when object is casted to string or echoed.
     *
     * @access public
     * @return string Response string.
     */
    public function __toString()
    {
    	return (string) $this->render();
    }

    /**
     * Render a response and send out headers.
     *
     * @access public
     * @return string Response.
     */
    public function render()
    {
    	if ( ! headers_sent())
    	{
    		header('Content-Length: ' . strlen($this->_body));

    		foreach ($this->_headers as $header)
    		{
    			header($header, true);
    		}
    	}

    	return $this->_body;
    }

    /**
     * Add a header to be sent during render.
     *
     * @access public
     * @param string $header HTTP header value.
     * @return $this
     */
    public function addHeader($header)
    {
    	$this->_headers[] = $header;

    	return $this;
    }

    /**
     * Set container object.
     *
     * @access public
     * @param PluginContainer $container Plugin container object.
     * @return $this
     */
    public function setContainer(PluginContainer $container)
    {
        $this->_container = $container;

        return $this;
    }

    /**
     * Get container object.
     *
     * @access public
     * @return PluginContainer
     */
    public function getContainer()
    {
        return $this->_container;
    }

}