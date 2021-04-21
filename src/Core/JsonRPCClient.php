<?php declare(strict_types=1);
/**
 * jsonRPCClient.php
 *
 * Written using the JSON RPC specification -
 * http://json-rpc.org/wiki/specification
 *
 * @author Kacper Rowinski <krowinski@implix.com>
 * http://implix.com
 *
 * @author Cypherbits <info@avanix.es>
 * PHP8 porting and refactor
 */
namespace MoneroIntegrations\MoneroPhp\Core;

use InvalidArgumentException;
use JsonException;
use RuntimeException;

class JsonRPCClient
{
    private string $url;
    private bool $debug;
    private null|string $username;
    private null|string $password;
    private array $curl_options = array(
        CURLOPT_CONNECTTIMEOUT => 8,
        CURLOPT_TIMEOUT => 8
    );
    private bool $curl_checkSSL;
    private string $debugOutput;
    private string $debugStartTime;

    private static array $httpErrors = array(
        400 => '400 Bad Request',
        401 => '401 Unauthorized',
        403 => '403 Forbidden',
        404 => '404 Not Found',
        405 => '405 Method Not Allowed',
        406 => '406 Not Acceptable',
        408 => '408 Request Timeout',
        500 => '500 Internal Server Error',
        502 => '502 Bad Gateway',
        503 => '503 Service Unavailable'
    );

    /**
     * JsonRPCClient constructor.
     * @param string $pUrl
     * @param string|null $pUser
     * @param string|null $pPass
     * @param bool $checkSSL
     * @param bool $debug
     */
    public function __construct(string $pUrl, null|string $pUser, null|string $pPass, bool $checkSSL, bool $debug = false)
    {
        $this->validate(false === extension_loaded('curl'), 'The curl extension must be loaded to use this class!');
        $this->validate(false === extension_loaded('json'), 'The json extension must be loaded to use this class!');

        $this->url = $pUrl;
        $this->username = $pUser;
        $this->password = $pPass;
        $this->curl_checkSSL = $checkSSL;
        $this->debug = $debug;
    }

    public function setDebug(bool $debug) : void
    {
        $this->debug = $debug;
    }

    public function setCurlOptions(array $pOptionsArray): void
    {
        if (is_array($pOptionsArray))
        {
            $this->curl_options = array_merge($this->curl_options, $pOptionsArray);
        }
        else
        {
            throw new InvalidArgumentException('Invalid options type, must be an array.');
        }
    }

    public function _run(string $pMethod, null|array $pParams, string $path) : array
    {
        // check if given params are correct
        $this->validate(false === is_scalar($pMethod), 'Method name has no scalar value');
        // send params as an object or an array
        // Request (method invocation)
        try {
            $request = json_encode(array('jsonrpc' => '2.0', 'method' => $pMethod, 'params' => $pParams), JSON_THROW_ON_ERROR);
        } catch (JsonException $e) {
            $this->validate(true, $e->getTraceAsString());
        }
        // if is_debug mode is true then add url and request to is_debug
        $this->debug('Url: ' . $this->url . "\r\n");
        $this->debug('Request: ' . $request . "\r\n");
        $responseMessage = $this->getResponse($request, $path);
        // if is_debug mode is true then add response to is_debug and display it
        $this->debug('Response: ' . $responseMessage . "\r\n", true);
        // decode and create array ( can be object, just set to false )
        try {
            $responseDecoded = json_decode($responseMessage, true, 512, JSON_THROW_ON_ERROR);
        } catch (JsonException $e) {
            $this->validate(true, $e->getTraceAsString());
        }

        if (isset($responseDecoded['error']))
        {
            $errorMessage = 'Request have return error: ' . $responseDecoded['error']['message'] . '; ' . "\n" .
                'Request: ' . $request . '; ';
            if (isset($responseDecoded['error']['data']))
            {
                $errorMessage .= "\n" . 'Error data: ' . $responseDecoded['error']['data'];
            }
            $this->validate( !is_null($responseDecoded['error']), $errorMessage);
        }
        return $responseDecoded['result'] ?? throw new RuntimeException('Result response not found');
    }

    public function getResponse(string $pRequest, string $path): bool|string
    {
        // do the actual connection
        $ch = curl_init();
        if (!$ch)
        {
            throw new RuntimeException('Could\'t initialize a cURL session');
        }
        curl_setopt($ch, CURLOPT_URL, $this->url.$path);
        curl_setopt($ch, CURLOPT_HTTPAUTH, CURLAUTH_DIGEST);
        curl_setopt($ch, CURLOPT_USERPWD, $this->username . ":" . $this->password);
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $pRequest);
        curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-type: application/json'));
        curl_setopt($ch, CURLOPT_ENCODING, 'gzip,deflate');
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);

        if ($this->curl_checkSSL)
        {
            curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, '2');
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, true);
        }else{
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        }
        if (!curl_setopt_array($ch, $this->curl_options))
        {
            throw new RuntimeException('Error while setting curl options');
        }
        // send the request
        $response = curl_exec($ch);
        // check http status code
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        if (isset(self::$httpErrors[$httpCode]))
        {
            throw new RuntimeException('Response Http Error - ' . self::$httpErrors[$httpCode]);
        }
        // check for curl error
        if (0 < curl_errno($ch))
        {
            throw new RuntimeException('Unable to connect to '.$this->url . ' Error: ' . curl_error($ch));
        }
        // close the connection
        curl_close($ch);
        return $response;
    }

    private function validate(bool $pFailed, string $pErrMsg): void
    {
        if ($pFailed)
        {
            throw new RuntimeException($pErrMsg);
        }
    }

    private function debug(string $pAdd, bool $pShow = false): void
    {
        // is_debug off return
        if (false === $this->debug)
        {
            return;
        }
        // add
        $this->debugOutput .= $pAdd;
        // get starttime
        $this->debugStartTime = empty($this->debugStartTime) ? array_sum(explode(' ', microtime())) : $this->debugStartTime;
        if (true === $pShow && !empty($this->debugOutput))
        {
            // get endtime
            $endTime = array_sum(explode(' ', microtime()));
            // performance summary
            $this->debugOutput .= 'Request time: ' . round($endTime - $this->debugStartTime, 3) . ' s Memory usage: ' . round(memory_get_usage() / 1024) . " kb\r\n";
            echo nl2br($this->debugOutput);
            // send output immediately
            flush();
            // clean static
            $this->debugStartTime = $this->debugOutput = '';
        }
    }
}
