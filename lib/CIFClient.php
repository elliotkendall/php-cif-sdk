<?php
class CIFClient {
  private $baseurl;
  private $apikey;
  private $http;
  private $validationChecks = array(
   'severity' => array('low', 'medium', 'high'),
   'confidence' => '/^(100|[0-9]{1,2})$/',
   'restriction' => array('public', 'need-to-know', 'private'),
   'guid' => '/^[a-z0-9.]+$/');
  
  # Possible invocations:
  #  function __construct($url, $apikey)
  #  function __construct($configfile)
  #  function __construct()
  function __construct() {
    # Set the config variables, depending on how we were invoked
    $numargs = func_num_args();
    if ($numargs == 2) {
      $this->baseurl = func_get_arg(0);
      $this->apikey = func_get_arg(1);
    } else {
      $files = array('.cif', getenv('HOME') . '/.cif');
      if ($numargs == 1) {
        # If we were given a config file path, try that first
        array_unshift($files, func_get_arg(0));
      }
      $ifr = new IniFileReader();
      foreach ($files as $file) {
        try {
          $config = $ifr->parse($file);
        } catch (Exception $e) {
          continue;
        }
      }
      if (! isset($config)) {
        throw new Exception('Could not open any config file');
      }
      if (! isset($config['client_http']['host'])) {
        throw new Exception('Host not defined in config file');
      }
      $this->baseurl = $config['client_http']['host'];

      if (! isset($config['client']['apikey'])) {
        throw new Exception('Host not defined in config file');
      }
      $this->apikey = $config['client']['apikey'];
    }

    $this->http = new HTTPClient('PHP CIFClient');
    # Necessary to get JSON output
    $this->http->setOptions(array(
     CURLOPT_HTTPHEADER => array('Accept: application/json')));
  }

  function ping() {
    $t0 = microtime(TRUE);
    $url = $this->baseurl . '/ping?token=' . urlencode($this->apikey);
    $response = $this->http->get($url);
    $status = $this->http->getStatus();
    if ($status > 299) {
      throw new Exception('Ping failed, status code ' . $code);
    }
    return microtime(TRUE) - $t0;
  }

  function query($query, $severity = Null, $confidence = Null,
   $restriction = Null, $guid = Null, $nomap = False, $nolog = False) {

    if (! function_exists('json_decode')) {
      throw new Exception('json_decode() not defined! You need PHP 5.2+ or '
       . 'the json PECL module');
    }

    if (! preg_match('/^[a-z0-9][0-9a-z\/.-]*$/', $query)) {
      throw new Exception('Invalid path');
    }      

    $params = array('apikey=' . $this->apikey, 'q=' . $query);

    foreach (array(
     'severity' => $severity,
     'confidence' => $confidence,
     'restriction' => $restriction,
     'guid' => $guid)
     as $name => $val) {
      if ($this->validate($name, $val)) {
        $params[] = "$name=$val";
      }
    }

    $nolog && $params[] = 'nolog=1';
    $nomap && $params[] = 'nomap=1';

    $url = $this->baseurl . '/api' . $this->arrayToGetParams($params);
    $response = $this->http->get($url);

    # In SESv2, this response is not actually a JSON object despite
    # setting Content-Type: text/json. WTF?
    $ret = array();
    foreach (explode("\n", $response) as $line) {
      if (trim($line) == '' || trim($line) == '1') {
        continue;
      }
      if (! ($ret[] = json_decode($line, True))) {
        throw new Exception("Could not JSON decode response: $line");
      }
    }
    return $ret;
  }

  private function arrayToGetParams($params) {
    if (count($params) > 0)
      return '?' . implode('&', $params);
    return '';
  }

  private function validate($param, $value) {
    if (@in_array($value, $this->validationChecks[$param])) {
      return TRUE;
    } else if (@preg_match($this->validationChecks[$param], $value)) {
      return TRUE;
    }
    return FALSE;
  }
}
?>
