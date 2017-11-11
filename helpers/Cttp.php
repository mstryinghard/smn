<?php

class Cttp
{
    protected $url;
    protected $headers = [];
    protected $as_post = false;
    protected $data = [];

    public function __construct()
    {
        $this->curl = curl_init();
    }

    public function withHeaders($headers = [])
    {
        $this->headers = $headers;
        return $this;
    }

    public function get($url, $data = [])
    {
        $this->url = $url;
        $this->data = $data;
        $this->as_post = false;
        return $this->run();
    }

    public function post($url, $data = [])
    {
        $this->url = $url;
        $this->data = $data;
        $this->as_post = true;
        return $this->run();
    }

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
