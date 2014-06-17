<?php
class HTTPClient {
  private $curl;

  function __construct($agent = NULL) {
    $this->curl = curl_init();
    curl_setopt($this->curl, CURLOPT_RETURNTRANSFER, True);
    curl_setopt($this->curl, CURLOPT_FAILONERROR, True);
    curl_setopt($this->curl, CURLOPT_CONNECTTIMEOUT, 3);
    if ($agent !== NULL) {
      curl_setopt($this->curl, CURLOPT_USERAGENT, $agent);
    }
  }

  function setOptions($options) {
    if (! curl_setopt_array($this->curl, $options)) {
      throw new Exception('Could not set options: ' . curl_error($this->curl));
    }
  }

  function get($url) {
    curl_setopt($this->curl, CURLOPT_HTTPGET, TRUE);
    return $this->exec($url);
  }

  function post($url) {
    curl_setopt($this->curl, CURLOPT_POST, TRUE);
    return $this->exec($url);
  }

  private function exec($url) {
    curl_setopt($this->curl, CURLOPT_URL, $url);
    $ret = curl_exec($this->curl);
    if ($ret === False) {
      throw new Exception('Curl: ' . curl_error($this->curl));
    }
    return $ret;
  }

  function getStatus() {
    return curl_getinfo($this->curl, CURLINFO_HTTP_CODE);
  }
}
?>
