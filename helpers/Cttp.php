<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

/**
 * Simple curl wrapper
 *
 * @author Warda Bangkila
 */
class Cttp
{

    /**
     * Url container
     *
     * @var string
     */
    protected $url;

    /**
     * Headers container
     *
     * @var array
     */
    protected $headers = [];

    /**
     * Method type flag
     *
     * @var boolean
     */
    protected $as_post = false;

    /**
     * Data container
     *
     * @var array
     */
    protected $data = [];

    /**
     * Constructor
     *
     * @return void
     */
    public function __construct()
    {
        $this->curl = curl_init();
    }

    /**
     * Set headers
     *
     * @param  array  $headers the array headers to include
     * @return Cttp
     */
    public function withHeaders($headers = [])
    {
        $this->headers = $headers;
        return $this;
    }

    /**
     * Get
     *
     * @param  string $url  the api url to get
     * @param  array  $data the payload data
     * @return response
     */
    public function get($url, $data = [])
    {
        $this->url = $url;
        $this->data = $data;
        $this->as_post = false;
        return $this->run();
    }

    /**
     * Post
     *
     * @param  string $url  the api url where to post
     * @param  array  $data the payload data
     * @return response
     */
    public function post($url, $data = [])
    {
        $this->url = $url;
        $this->data = $data;
        $this->as_post = true;
        return $this->run();
    }

    /**
     * Run curl
     *
     * @return response
     */
    private function run()
    {
        curl_setopt($this->curl, CURLOPT_URL, $this->url);
        curl_setopt($this->curl, CURLOPT_HTTPHEADER, $this->headers);

        if($this->as_post) {
            curl_setopt($this->curl, CURLOPT_POST, 1);
            curl_setopt($this->curl, CURLOPT_POSTFIELDS, $data);
        }

        curl_setopt($this->curl, CURLOPT_RETURNTRANSFER, true);

        $result = curl_exec($this->curl);

        curl_close($this->curl);

        return $result;
    }

}
