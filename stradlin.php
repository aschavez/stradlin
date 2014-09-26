<?php

function serve_json($doc, $options = 0, $cbname = 'callback') {
  set_content_type('application/json');
  $doc = json_encode($doc, $options);
  $callback = null;
  if (array_key_exists('HTTP_JSONP_CALLBACK', $_SERVER) && strlen($_SERVER['HTTP_JSONP_CALLBACK'])>0) {
    $callback = $_SERVER['HTTP_JSONP_CALLBACK'];
  } elseif (array_key_exists($cbname, $_REQUEST) && strlen($_REQUEST[$cbname])>0) {
    $callback = $_REQUEST[$cbname];
  }
  if (strlen($callback)>0) {
    printf("%s(%s);\n", $callback, $doc);
  } else {
    echo $doc;
  }
}

function get_request_uri() {
  if (array_key_exists('PATH_INFO', $_SERVER)) {
    return  $_SERVER['PATH_INFO'];
  } elseif (array_key_exists('REDIRECT_URL', $_SERVER)) {
    return $_SERVER['REDIRECT_URL'];
  } else {
    return '/';
  }
}

function request_method_matches($methods) {
  return in_array($_SERVER['REQUEST_METHOD'], $methods);
}

function before($regexp, $methods, $callback) {

  $regexp = sanitize_regexp($regexp);
  $methods = array_methods($methods);

  /* Match */
  $uri = get_request_uri();
  if (request_method_matches($methods) && preg_match($regexp, $uri, $params)) {
    $callback($params);
  }

}

function route($regexp, $methods, $callback) {

  $regexp = sanitize_regexp($regexp);
  $methods = array_methods($methods);

  /* Match */
  $uri = get_request_uri();
  if (request_method_matches($methods) && preg_match($regexp, $uri, $params)) {
    $callback($params);
    exit();
  }

}

function sanitize_regexp($regexp) {

  /* Sanitize regexp */
  if (!preg_match('/^\^(.)+$/', $regexp)) {
    $regexp = sprintf("^%s", $regexp);
  }
  if (!preg_match('/^(.)+\$$/', $regexp)) {
    $regexp = sprintf("%s$", $regexp);
  }
  $regexp = str_replace("/", "\/", $regexp);
  $regexp = sprintf("/%s/", $regexp);

  return $regexp;

}

function array_methods($methods) {

  /* Create array of accepted HTTP methods */
  $methods = explode(",", $methods) ;
  foreach($methods as $k=>$v) {
    $methods[$k] = trim(strtoupper($v));
  }

  return $methods;

}

function status_code_map() {
  return array(
    200 => 'OK',
    201 => 'Created',
    202 => 'Accepted',
    203 => 'Non',
    204 => 'No Content',
    205 => 'Reset Content',
    206 => 'Partial Content',
    300 => 'Multiple Choices',
    301 => 'Moved Permanently',
    302 => 'Found',
    303 => 'See Other',
    304 => 'Not Modified',
    305 => 'Use Proxy',
    307 => 'Temporary Redirect',
    400 => 'Bad Request',
    401 => 'Unauthorized',
    402 => 'Payment Required',
    403 => 'Forbidden',
    404 => 'Not Found',
    405 => 'Method Not Allowed',
    406 => 'Not Acceptable',
    407 => 'Proxy Authentication Required',
    408 => 'Request Timeout',
    409 => 'Conflict',
    410 => 'Gone',
    411 => 'Length Required',
    412 => 'Precondition Failed',
    413 => 'Request Entity Too Large',
    414 => 'Request',
    415 => 'Unsupported Media Type',
    416 => 'Requested Range Not Satisfiable',
    417 => 'Expectation Failed',
    500 => 'Internal Server Error',
    501 => 'Not Implemented',
    502 => 'Bad Gateway',
    503 => 'Service Unavailable',
    504 => 'Gateway Timeout',
    505 => 'HTTP Version Not Supported',
  );
}

function set_content_type($content_type) {
  header("Content-Type: $content_type");
}

function set_status_code($status_code) {
  $map = status_code_map();
  header(sprintf("HTTP/1.1 %d %s", $status_code, strtoupper($map[$status_code])));
}

function all_verbs_array() {
  return array(
    "OPTIONS",
    "GET",
    "HEAD",
    "POST",
    "PUT",
    "DELETE",
    "TRACE",
    "CONNECT"
  );
}

function all_verbs() {
  return implode(", ", all_verbs_array());
}

function include_template($template, $template_dir='templates') {
  $path = str_replace("//", "/", $template_dir.'/'.$template);
  include($path);
}

function render_template($template, $context = null, $template_dir='templates') {
  if (!is_null($context)) {
    extract($context);
  }
  include_template($template, $template_dir);
}

function phpinfo_to_file($path) {
  ob_start();
  phpinfo();
  $contents = ob_get_contents();
  ob_end_clean();
  $fp = @fopen($path, 'w+');
  @fputs($fp, $contents);
  @fclose($fp);
}

?>
