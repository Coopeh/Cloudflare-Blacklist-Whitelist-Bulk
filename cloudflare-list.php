<?php $time_start = microtime(true);

// Add multiple IPs to blacklist, whitelist or unlist them on Cloudflare using CloudFlare API by AzzA <azza@broadcasthe.net>
// Ed Cooper 2015 - https://blog.ed.gs
// Version 1.0

// Configure your API key and email address below

$cfemailaddress = "email@here.com"; // Cloudflare email address
$cfapikey = "yourcloudflareapikey"; // Cloudflare API key
$type = "Whitelist"; // Use either Whitelist, Blacklist or Unlist as options

// Use either $url, $ips or both, but make sure the URL address is a line break separated list of IPs, comment and uncommente the lines as needed

$url = "https://www.statuscake.com/API/Locations/txt"; // Web address for line break separated list of IPs
$ips = array("128.199.222.65","209.141.61.87","37.122.208.79","37.157.246.146","162.253.64.87","95.154.217.114","5.45.179.103","199.167.128.80","108.61.123.148","199.167.198.78","176.56.230.59","31.220.1.73","176.56.230.110","162.248.97.72","192.110.164.58","37.235.53.240","192.211.53.16","192.119.147.94","196.41.137.237","192.110.160.219","212.68.34.43","167.160.94.79","192.119.147.95","162.253.64.104","192.241.221.11","50.2.139.16","162.248.101.207","172.245.33.212","64.188.46.143","50.2.64.192","162.217.250.151","162.250.96.153","106.186.23.220","37.235.48.42","192.3.23.124","158.255.208.76","125.63.48.239","185.12.45.70","49.50.252.89","213.183.56.107","88.150.203.215","198.56.129.35","31.22.116.155","46.246.28.90","78.157.217.101","37.235.55.205","162.245.216.227","185.38.32.15","107.150.1.135","158.255.215.97","107.170.227.23","107.170.227.24","188.226.169.228","188.226.185.106","188.226.186.199","107.182.132.11","188.226.171.58","108.61.119.153","188.226.158.160","212.13.200.68","95.154.217.127","95.154.217.129","107.170.240.141","176.227.201.226","198.55.116.54","199.116.117.115","107.155.90.144","107.155.88.134","107.155.125.29","162.211.229.10","31.3.247.74","95.154.217.174","108.61.197.82","209.222.30.242","108.61.196.32","108.61.212.141","108.61.196.165","108.61.196.195","178.79.169.144","198.58.124.46","188.226.247.184","188.226.139.158","108.61.197.29","188.226.184.152","108.61.196.225","104.131.248.65","104.131.248.78","108.61.196.255","104.131.247.151","108.61.196.38","107.170.197.248","107.170.219.46","188.226.203.84","178.62.41.44","178.62.41.49","178.62.41.52","162.243.71.56","178.62.40.233","162.243.247.163","107.170.53.191","82.37.165.148","31.22.116.183","5.135.29.124","31.220.7.152","31.220.7.237","178.32.72.135","54.148.157.118","45.56.89.59","178.62.80.93","178.62.71.227","45.56.114.90","178.73.210.99","181.41.214.137","154.127.60.59","193.68.47.115","178.62.73.80"); // Statuscake IPs

// STOP EDITING NOW

function get_data($url) {
  $ch = curl_init();
  $timeout = 5;
  curl_setopt($ch, CURLOPT_URL, $url);
  curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
  curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, $timeout);
  $data = curl_exec($ch);
  curl_close($ch);
  return $data;
}

class cloudflare_api
{
    // The URL of the API
    private static $URL = array(
        'USER' => 'https://www.cloudflare.com/api_json.html',
        'HOST' => 'https://api.cloudflare.com/host-gw.html'
    );

    // Timeout for the API requests in seconds
    const TIMEOUT = 5;

    // Stores the api key
    private $token_key;
    private $host_key;

    // Stores the email login
    private $email;

    /**
     * Make a new instance of the API client
     */
    public function __construct()
    {
        $parameters = func_get_args();
        switch (func_num_args()) {
            case 1:
                // a host API
                $this->host_key  = $parameters[0];
                break;
            case 2:
                // a user request
                $this->email     = $parameters[0];
                $this->token_key = $parameters[1];
                break;
        }
    }

    public function setEmail($email)
    {
        $this->email = $email;
    }

    public function setToken($token_key)
    {
        $this->token_key = $token_key;
    }


    /**
     * CLIENT API
     * Section 3
     * Access
     */

    /**
     * 4.7a - Whitelist IPs
     * You can add an IP address to your whitelist.
     */
    public function wl($ip)
    {
        $data = array(
            'a'   => 'wl',
            'key' => $ip
        );
        return $this->http_post($data);
    }

    /**
     * 4.7b - Blacklist IPs
     * You can add an IP address to your blacklist.
     */
    public function ban($ip)
    {
        $data = array(
            'a'   => 'ban',
            'key' => $ip
        );
        return $this->http_post($data);
    }

    /**
     * 4.7c - Unlist IPs
     * You can remove an IP address from the whitelist and the blacklist.
     */
    public function nul($ip)
    {
        $data = array(
            'a'   => 'nul',
            'key' => $ip
        );
        return $this->http_post($data);
    }

    /**
     * GLOBAL API CALL
     * HTTP POST a specific task with the supplied data
     */
    private function http_post($data, $type = 'USER')
    {
        switch ($type) {
            case 'USER':
                $data['u']   = $this->email;
                $data['tkn'] = $this->token_key;
                break;
            case 'HOST':
                $data['host_key'] = $this->host_key;
                break;
        }
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_VERBOSE, 0);
        curl_setopt($ch, CURLOPT_FORBID_REUSE, true);
        curl_setopt($ch, CURLOPT_URL, self::$URL[$type]);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
        curl_setopt($ch, CURLOPT_TIMEOUT, self::TIMEOUT);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        $http_result = curl_exec($ch);
        $error       = curl_error($ch);
        $http_code   = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);
        if ($http_code != 200) {
            return array(
                'error' => $error
            );
        } else {
            $result = json_decode($http_result);
            echo $result->{'response'}->{'result'}->{'ip'}." - ".ucfirst($result->{'result'})."\n";
        }
    }
}

$valid = array();

// Check if the URL is set in the parameters at the top then retrieve and validate the contents
if (isset($url)) {
    $url = get_data($url);

    $comma = strpos($url, ",");
    $space = strpos($url, " ");
    $linebreak = strpos($url, "\n");

    if ($comma !== false) {
        $url = explode(",", $url);
        echo "Comma detected\n";
    } else if ($space !== false) {
        $url = explode(" ", $url);
        echo "Space detected\n";
    } else if ($linebreak !== false) {
        $url = explode("\n", $url);
        echo "Line break detected\n";
    } else {
        echo "Can't detect delimiter, is it a space, comma or new line?\n";
    }
    if (is_array($url)) {
        foreach ($url as $contents) {
            if (filter_var($contents, FILTER_VALIDATE_IP)) {
                array_push($valid, $contents);
            } else {
                echo $contents." is not valid.\n";
            }
        }
    }
}

// Check if any IPs are set in the parameters and validate the contents
if (isset($ips)) {
    if (is_array($ips)) {
        foreach ($ips as $contents) {
            if (filter_var($contents, FILTER_VALIDATE_IP)) {
                array_push($valid, $contents);
            } else {
                echo $contents." is not valid.\n";
            }
        }
    }
}

// What have we got?
echo count($valid)." IPs detected. ".$type."ing with Cloudflare now...\n";

// Set the listing types
$checkVars = array("Whitelist","Blacklist","Unlist");

// Check the listing type and get started
if(in_array($type, $checkVars)){

    // Run the $url IPs first
    if (isset($url)) {
        if (is_array($url)) {
            foreach ($url as $value) {
                $cf = new cloudflare_api($cfemailaddress, $cfapikey);
                if($type == "Whitelist") {
                  $response = $cf->wl($value);
                } elseif($type == "Blacklist") {
                  $response = $cf->ban($value);
                } elseif($type == "Unlist") {
                  $response = $cf->nul($value);
                }
                print_r($response);
            }
        }
    } else {
        echo "No URL specified, trying IPs\n";
    }

    // Run the $ips array second
    if (isset($ips)) {
        if (is_array($ips)) {
            foreach ($ips as $value) {
                $cf = new cloudflare_api($cfemailaddress, $cfapikey);
                if($type == "Whitelist") {
                  $response = $cf->wl($value);
                } elseif($type == "Blacklist") {
                  $response = $cf->ban($value);
                } elseif($type == "Unlist") {
                  $response = $cf->nul($value);
                }
                print_r($response);
            }
        }
    } else {
        echo "No manual IPs specified\n";
    }

} else {
    echo "Unknown type, please check configuration\n";
}

// Fin
echo "Finished\n";
$time_end = microtime(true);

$execution_time = ($time_end - $time_start);

echo "Script running time:".round($execution_time)." seconds\n";

?>
