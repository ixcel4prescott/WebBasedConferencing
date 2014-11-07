<?
//------------------------------------------------------------------------------
//  Marc's Utils
//------------------------------------------------------------------------------

// dump a nicely formatted backtrace
function trace() 
{
  echo '<table class="backtrace" style="border: 1px solid #ccc">';
  echo '<tr><th>Class</th><th>Function</th><th>Line</th><th>File</th></tr>';

  $bt = debug_backtrace();

  for($i=1; $i<count($bt); $i++) {
    $cls = isset($bt[$i]['class']) ? $bt[$i]['class'] : '';
    printf('<tr class="%s"><td style="border: 1px solid #ccc">%s</td><td style="border: 1px solid #ccc">%s</td><td style="border: 1px solid #ccc">%s</td><td style="border: 1px solid #ccc">%s</td></tr>', $i&0x1 ? 'odd' : 'even', $cls, $bt[$i]['function'], $bt[$i]['line'], $bt[$i]['file']);
  }

  echo '</table>';
}

// recursive trim of data
function clean_data($data) 
{
  if(is_array($data))
    $rv = array_map('clean_data', $data);
  elseif(is_string($data))
    $rv = trim($data);
  else
    $rv = $data;

  return $rv;
}

// pull an key from a each item in a sequence
function pluck($seq, $key)
{
  $out = Array();
  foreach($seq as $i)
    $out[] = $i[$key];

  return $out;
}

// reindex a sequence by key in each item
function reindex($seq, $key) 
{
  $out = Array();

  foreach($seq as $i)
    $out[$i[$key]] = $i;

  return $out;
}

// build a k=>v map by pulling k,v from each item in a sequence
function map_pluck($seq, $key, $val) 
{
  $out = Array();

  foreach($seq as $i)
    $out[$i[$key]] = $i[$val];

  return $out;
}

function mssql_escape($s)
{
  return get_magic_quotes_gpc() ? str_replace("'", "''", stripslashes($s)) : str_replace("'", "''", $s);
}

function now($format='Ymd H:i:s') 
{
  return date($format);
}

// Only really need first 64bits
function generate_token($len=8)
{
  do {
    $token = substr(sha1(uniqid()), 0, $len);
  } while(is_numeric($token));

  return $token;
}

function generate_password($syllables)
{
  // English syllables are typically made up of an onset+coda+nuclei so in this overly simplistic view we can generate some
  $onsets = Array( '', 'p', 'b', 't', 'd', 'k', 'g', 'th', 'sh', 'ch', 'sf', 'sv',
		   'sc', 'pr', 'br', 'tr', 'dr', 'kr', 'gr', 'f', 'v', 's', 'z', 'm',
		   'n', 'sp', 'st', 'sk', 'sw', 'sm', 'sn', 'pl', 'bl', 'tl', 'dl',
		   'kl', 'gl', 'l', 'r', 'w', 'j', 'c', 'h', 'fr', 'vr', 'sr', 'fl',
		   'vl', 'sl', 'pw', 'bw', 'tw', 'dw', 'kw', 'gw' );
  
  $nuclei = Array('a', 'e', 'i', 'o', 'u' );
  
  $codas = Array( 'p', 'b', 't', 'd', 'k', 'g', 'ch', 'th', 'sh', 'ct', 'ft', 'fk',
		  'f', 'v', 's', 'z', 'm', 'n', 'zd', 'mp', 'nt', 'nd', 'nk', 'ng',
		  'l', 'r', 'j', 'q', 'x', 'c', 'sp', 'st', 'sk', 'lp', 'lt', 'lk' );
  
  $word = '';
  for($i=0; $i<$syllables; $i++)
    $word .= ($onsets[array_rand($onsets)] . $nuclei[array_rand($nuclei)] . $codas[array_rand($codas)]);
  
  return $word;
}

function render_template($_layout_, $_view_, $_args_)
{
  extract($_args_);

  ob_start();
  include($_view_);
    
  $content_for_layout = ob_get_clean();

  ob_start();
  include($_layout_);

  return ob_get_clean();
}

function flatten_shell_args($args)
{
  $out = Array();

  foreach($args as $k => $v) {

    if(!is_numeric($k)) {

      $k = (strlen($k)>1) ? '--' . $k : '-' . $k;

      if(!is_null($v)) {
	if(is_array($v)) {
	  
	  foreach($v as $i)
	    $out[] = sprintf('%s %s', $k, escapeshellarg($i));
	  
	} else {
	  $out[] = sprintf('%s %s', $k, escapeshellarg($v));
	}
      } else {
	$out[] = $k;
      }

    } else {
      $out[] = escapeshellarg($v);
    }
  }
  
  return implode(' ', $out);
}

function spawn($cmd, $args, &$out, $cwd=null, $env=null, $stdout=true, $stderr=true)
{  
  $rv = null;

  $exec = $cmd . ' ' . flatten_shell_args($args);
  $out  = Array();

  $descriptors = Array();

  if($stdout)
    $descriptors[1] = Array('pipe', 'w');

  if($stderr)
    $descriptors[2] = Array('pipe', 'w');
   
  $p = proc_open($exec, $descriptors, $pipes, $cwd, $env);
  if(is_resource($p)) {

    if($stdout) {
      $out[1] = stream_get_contents($pipes[1]);
      fclose($pipes[1]);
    }
    
    if($stderr) { 
      $out[2] = stream_get_contents($pipes[2]);
      fclose($pipes[2]);
    }
    
    $rv = proc_close($p);
  }

  return $rv;
}

function format_phone($p, $fmt='($1)$2-$3') 
{
  if(strlen($p) == 10 || (strlen($p) == 11 && $p[0] == '1'))
    return preg_replace('/1?(\d{3})(\d{3})(\d{4})/', $fmt, $p);

  return $p;
}

function validate_email_list($emails, $validator_re=VALID_EMAIL) 
{
  $emails = explode(',', $emails);

  if($emails) {
    foreach($emails as $e) {
      if(preg_match(VALID_EMAIL, $e) === 0)
	return false;
    }
  } else {
    return false;
  }
  
  return true;
}

function is_holiday($date) 
{
  $year = date('Y',$date);

  $holidays = Array( "Thanksgiving" => Array("3 weeks thursday",Array(11,1)),
		     "New Years Day" => Array("today",Array(1,1)),
		     "Independence Day" => Array("today",Array(7,4)),
		     "Labor Day" => Array("monday",Array(9,1)),
		     "Christmas" => Array("today",Array(12,25)),
		     "Memorial Day" => Array("last monday",Array(6,1)) );

  foreach($holidays as $name => $spec) {
    $spec_time = mktime(0,0,0,$spec[1][0],$spec[1][1],$year);
    if (strtotime($spec[0], $spec_time) == $date)
      return $name;
  }

  return null;
}

function shorten($text, $len=20)
{
  if(strlen($text)>=$len)
    $rv = substr($text, 0, $len) . '...';
  else
    $rv = $text;

  return $rv;
}

function ends_with($needle, $haystack)
{
  return substr($haystack, strlen($needle)*-1) == $needle;
}

function http_post($url, $data, $optional_headers = null)
{
  $c = curl_init();

  curl_setopt($c, CURLOPT_URL, $url);
  curl_setopt($c, CURLOPT_HEADER, 0);
  curl_setopt($c, CURLOPT_RETURNTRANSFER, 1);
  curl_setopt($c, CURLOPT_POST, 1);
  curl_setopt($c, CURLOPT_POSTFIELDS, $data);
  curl_setopt($c, CURLOPT_SSL_VERIFYHOST, false);
  curl_setopt($c, CURLOPT_SSL_VERIFYPEER, false);
  
  if($optional_headers)
    curl_setopt($c, CURLOPT_HTTPHEADER, $optional_headers);

  return curl_exec($c);
}

function format_passcode($code, $seperator=' ')
{  
  if(is_numeric($code))
    return substr(chunk_split($code, 3, $seperator), 0, -1);
  
  return $code;
}

// Keys - array of key order
// Cols - array of keys -> headers
// Data - array of key -> values
function export_csv($filename, $keys, $headers, $rows, $callback=null)
{
  header('Content-type: text/csv');
  header(sprintf('Content-Disposition: attachment; filename="%s.csv"', $filename));
  header("Cache-Control:  maxage=1");
  header("Pragma: public");

  $out = fopen('php://output', 'w');

  $data = Array();
  foreach($keys as $k)
    $data[] = $headers[$k];

  fputcsv($out, $data);

  foreach($rows as $row) {
    $data = Array();

    if($callback)
      $row = call_user_func($callback, $row);

    foreach($keys as $k)
      $data[] = $row[$k];

    fputcsv($out, $data);
  }

  fclose($out);
}

function month_boundaries($month = null, $year=null){
//Finds that absolute beginning and end of a month as well as the number of days in the month.
    if(!$year)
      $year = date('Y');

    if (!$month)
      $month = date('m');
      
    $days_in_month  = cal_days_in_month(CAL_GREGORIAN, $month, $year);
    $start_of_month_rough = mktime(0, 0, 0, $month, 1, $year);
    $end_of_month_rough   = mktime(23, 59, 59, $month, $days_in_month, $year);

    $start_of_month = date('Y-m-d H:i:s', $start_of_month_rough);
    $end_of_month = date('Y-m-d H:i:s', $end_of_month_rough);
    return array('start_of_month' => $start_of_month, 'end_of_month' => $end_of_month, 'days_in_month' => $days_in_month);
}
