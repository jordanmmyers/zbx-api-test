<?php

class ZabbixAPI {
    private $username;
    private $password;
    public $url;
    private $key;
    public $errors = array();
    public $debug = array();

    function __construct() {
        // Parse config file
        $config = parse_ini_file('config.ini', true);
        // Store credentials for later use (maybe)
        $this->username = $config['zabbix-api']['username'];
        $this->password = $config['zabbix-api']['password'];
        $this->url = $config['zabbix-api']['zabbix-url'];

        // Authenticate and store key
        if ($result = $this->authenticate($this->username, $this->password)) {
            $this->key = $result;
        }
    }

    function __destruct() {
        $this->request("user.logout");
    }

    private function authenticate($u, $p) {
        $method = "user.login";
        $params = array(
            "user" => $u,
            "password" => $p
        );

        if ($result = $this->request($method, $params)) {
            return $result->result;
        } else {
            array_push($this->errors, "Could not authenticate");
            $this->debug['auth'] = $this->request($method, $params);
        }
    }

    private function request($method, $params) {
        // API Key NULL? We must be logging in
        $key = (isset($this->key)) ? $this->key : NULL;

        // We should assume that $params is an array
        $json = array(
            "jsonrpc" => "2.0",
            "method" => $method,
            "params" => $params,
            "id" => 1,
            "auth" => $key
        );

        if ($data = json_encode($json)) {
            $url = $this->url;
            $header = array("Content-Type: application/json-rpc");          

            $curl = curl_init();
            curl_setopt($curl, CURLOPT_URL, $url);
            curl_setopt($curl, CURLOPT_HTTPHEADER, $header);
            curl_setopt($curl, CURLPOT_POST, 1);
            curl_setopt($curl, CURLOPT_POSTFIELDS, $data);
            curl_setopt($curl, CURLOPT_HEADER, 0);
            curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);

            $result = curl_exec($curl);

            $this->debug['post-data'][$method] = $data;
            $this->debug['post-result'][$method] = $result;
            $this->debug['curl-error'][$method] = curl_error($curl);

            return json_decode($result);
            curl_close($curl);
        } else array_push($this->errors, "Could not parse JSON in request");
    }

    public function getData($method, $params) {
        // $params will be json, needs to be converted to an array
        // then back to json (yeah, I know...)
        $params = json_decode($params, TRUE);
        if (isset($params) && is_array($params)) {
            if (isset($method) && is_string($method)) {
                // Returns an object
                return $this->request($method, $params);
            } else array_push($this->errors, "Method not set or not a string");
        } else array_push($this->errors, "Params not set or not an array");
    }
}

?>
