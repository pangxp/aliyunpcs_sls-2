<?php
/**
 * Copyright (C) Alibaba Cloud Computing
 * All rights reserved
 */
namespace SLS;

use SLS\RequestCore;
use SLS\LogContent;
use SLS\Log;
use SLS\LogGroup;
use SLS\LogGroupList;
use SLS\ProtobufEnum;
use SLS\ProtobufMessage;
use SLS\Protobuf;
use SLS\SlsUtil;
use SLS\SlsException;

use SLS\Models\PutLogsRequest;
use SLS\Models\StaticCredentialsProvider;
use SLS\Models\CredentialsProvider;

use SLS\Models\Request\ApplyConfigToMachineGroupRequest;
use SLS\Models\Request\BatchGetLogsRequest;
use SLS\Models\Request\CreateACLRequest;
use SLS\Models\Request\CreateConfigRequest;
use SLS\Models\Request\CreateLogstoreRequest;
use SLS\Models\Request\CreateMachineGroupRequest;
use SLS\Models\Request\CreateShipperRequest;
use SLS\Models\Request\DeleteACLRequest;
use SLS\Models\Request\DeleteConfigRequest;
use SLS\Models\Request\DeleteLogstoreRequest;
use SLS\Models\Request\DeleteMachineGroupRequest;
use SLS\Models\Request\DeleteShardRequest;
use SLS\Models\Request\DeleteShipperRequest;
use SLS\Models\Request\GetACLRequest;
use SLS\Models\Request\GetConfigRequest;
use SLS\Models\Request\GetCursorRequest;
use SLS\Models\Request\GetHistogramsRequest;
use SLS\Models\Request\GetLogsRequest;
use SLS\Models\Request\GetMachineGroupRequest;
use SLS\Models\Request\GetMachineRequest;
use SLS\Models\Request\GetProjectLogsRequest;
use SLS\Models\Request\GetShipperConfigRequest;
use SLS\Models\Request\GetShipperTasksRequest;
use SLS\Models\Request\ListACLsRequest;
use SLS\Models\Request\ListConfigsRequest;
use SLS\Models\Request\ListLogstoresRequest;
use SLS\Models\Request\ListMachineGroupsRequest;
use SLS\Models\Request\ListShardsRequest;
use SLS\Models\Request\ListShipperRequest;
use SLS\Models\Request\ListTopicsRequest;
use SLS\Models\Request\LogStoreSqlRequest;
use SLS\Models\Request\MergeShardsRequest;
use SLS\Models\Request\ProjectSqlRequest;
use SLS\Models\Request\RemoveConfigFromMachineGroupRequest;
use SLS\Models\Request\RetryShipperTasksRequest;
use SLS\Models\Request\SplitShardRequest;
use SLS\Models\Request\UpdateACLRequest;
use SLS\Models\Request\UpdateConfigRequest;
use SLS\Models\Request\UpdateLogstoreRequest;
use SLS\Models\Request\UpdateMachineGroupRequest;
use SLS\Models\Request\UpdateShipperRequest;

use SLS\Models\Response\LogStoreSqlResponse;
use SLS\Models\Response\GetLogsResponse;
use SLS\Models\Response\ListTopicsResponse;
use SLS\Models\Response\ListSqlInstanceResponse;
use SLS\Models\Response\ListShipperResponse;
use SLS\Models\Response\ListShardsResponse;
use SLS\Models\Response\ListMachineGroupsResponse;
use SLS\Models\Response\ListLogstoresResponse;
use SLS\Models\Response\ListConfigsResponse;
use SLS\Models\Response\ListACLsResponse;
use SLS\Models\Response\GetShipperTasksResponse;
use SLS\Models\Response\GetShipperConfigResponse;
use SLS\Models\Response\GetMachineResponse;
use SLS\Models\Response\GetMachineGroupResponse;
use SLS\Models\Response\GetHistogramsResponse;
use SLS\Models\Response\GetCursorResponse;
use SLS\Models\Response\GetConfigResponse;
use SLS\Models\Response\GetACLResponse;
use SLS\Models\Response\DeleteShipperResponse;
use SLS\Models\Response\DeleteShardResponse;
use SLS\Models\Response\DeleteMachineGroupResponse;
use SLS\Models\Response\DeleteLogstoreResponse;
use SLS\Models\Response\DeleteConfigResponse;
use SLS\Models\Response\DeleteACLResponse;
use SLS\Models\Response\CreateSqlInstanceResponse;
use SLS\Models\Response\CreateShipperResponse;
use SLS\Models\Response\CreateMachineGroupResponse;
use SLS\Models\Response\CreateLogstoreResponse;
use SLS\Models\Response\CreateConfigResponse;
use SLS\Models\Response\CreateACLResponse;
use SLS\Models\Response\BatchGetLogsResponse;
use SLS\Models\Response\ApplyConfigToMachineGroupResponse;
use SLS\Models\Response\PutLogsResponse;

if(!defined('SLS_API_VERSION'))
    define('SLS_API_VERSION', '0.6.0');
if(!defined('SLS_USER_AGENT'))
    define('SLS_USER_AGENT', 'log-php-sdk-v-0.6.0');

/**
 * Client class is the main class in the SDK. It can be used to
 * communicate with LOG server to put/get data.
 *
 * @author log_dev
 */
class Client {

    /**
     *@var CredentialsProvider credentialsProvider
     */
    protected $credentialsProvider;

    /**
     * @var string LOG endpoint
     */
    protected $endpoint;

    /**
     * @var string Check if the host if row ip.
     */
    protected $isRowIp;

    /**
     * @var integer Http send port. The dafault value is 80.
     */
    protected $port;

    /**
     * @var string log sever host.
     */
    protected $logHost;

    /**
     * @var string the local machine ip address.
     */
    protected $source;

    /**
     * @var bool use https or use http.
     */
    protected $useHttps;
    
    /**
     * Client constructor.
     *
     * Either $accessKeyId/$accessKeySecret or $credentialsProvider must be provided.
     *
     * @param string $endpoint
     *            LOG host name, for example, http://cn-hangzhou.sls.aliyuncs.com
     * @param string $accessKeyId
     *            aliyun accessKeyId
     * @param string $accessKey
     *            aliyun accessKey
     * @param string $token
     *            aliyun token
     * @param CredentialsProvider $credentialsProvider
     */
    public function __construct(
        string $endpoint,
        string $accessKeyId = "",
        string $accessKey = "",
        string $token = "",
        CredentialsProvider $credentialsProvider = null
    ) {
        $this->setEndpoint($endpoint); // set $this->logHost
        if (!is_null($credentialsProvider)) {
            $this->credentialsProvider = $credentialsProvider;
        } else {
            if (empty($accessKeyId) || empty($accessKey)) {
                throw new SlsException("InvalidAccessKey", "accessKeyId or accessKeySecret is empty", "");
            }
            $this->credentialsProvider = new StaticCredentialsProvider(
                $accessKeyId,
                $accessKey,
                $token
            );
        }
        $this->source = SlsUtil::getLocalIp();
    }
    private function setEndpoint($endpoint) {
        if (strpos($endpoint, '://') === false) {
            $endpoint = 'http://' . $endpoint; // default use http
        }
        $urlComponents = parse_url($endpoint);
        if ($urlComponents === false || !isset($urlComponents['host'])) {
            throw new InvalidArgumentException("Invalid endpoint: $endpoint");
        }

        $this->useHttps = isset($urlComponents['scheme']) && $urlComponents['scheme'] === 'https';
        $this->logHost = $urlComponents['host'];

        if (isset($urlComponents['port'])) {
            $this->port = $urlComponents['port'];
            $this->endpoint = $this->logHost . ':' . $this->port;
        } else {
            $this->port = $this->useHttps ? 443 : 80;
            $this->endpoint = $this->logHost;
        }
        $this->isRowIp = SlsUtil::isIp($this->logHost);
    }
     
    /**
     * GMT format time string.
     * 
     * @return string
     */
    protected function getGMT() {
        return gmdate ( 'D, d M Y H:i:s' ) . ' GMT';
    }
    

    /**
     * Decodes a JSON string to a JSON Object. 
     * Unsuccessful decode will cause an SlsException.
     * 
     * @return string
     * @throws SlsException
     */
    protected function parseToJson($resBody, $requestId) {
        if (! $resBody)
          return NULL;
        
        $result = json_decode ( $resBody, true );
        if ($result === NULL){
          throw new SlsException ( 'BadResponse', "Bad format,not json: $resBody", $requestId );
        }
        return $result;
    }
    
    /**
     * @return array
     */
    protected function getHttpResponse($method, $url, $body, $headers) {
        $request = new RequestCore ( $url );
        foreach ( $headers as $key => $value )
            $request->add_header ( $key, $value );
        $request->set_method ( $method );
        $request->set_useragent(SLS_USER_AGENT);
        if ($method == "POST" || $method == "PUT")
            $request->set_body ( $body );
        $request->send_request ();
        $response = array ();
        $response [] = ( int ) $request->get_response_code ();
        $response [] = $request->get_response_header ();
        $response [] = $request->get_response_body ();
        return $response;
    }
    
    /**
     * @return array
     * @throws SlsException
     */
    private function sendRequest($method, $url, $body, $headers) {
        try {
            list ( $responseCode, $header, $resBody ) = 
                    $this->getHttpResponse ( $method, $url, $body, $headers );
        } catch ( Exception $ex ) {
            throw new SlsException ( $ex->getMessage (), $ex->__toString () );
        }
        
        $requestId = isset ( $header ['x-log-requestid'] ) ? $header ['x-log-requestid'] : '';

        if ($responseCode == 200) {
          return array ($resBody,$header);
        } 
        else {
            $exJson = $this->parseToJson ( $resBody, $requestId );
            if (isset($exJson ['error_code']) && isset($exJson ['error_message'])) {
                throw new SlsException ( $exJson ['error_code'], 
                        $exJson ['error_message'], $requestId );
            } else {
                if ($exJson) {
                    $exJson = ' The return json is ' . json_encode($exJson);
                } else {
                    $exJson = '';
                }
                throw new SlsException ( 'RequestError',
                        "Request is failed. Http code is $responseCode.$exJson", $requestId );
            }
        }
    }
    
    /**
     * @return array
     * @throws SlsException
     */
    private function send($method, $project, $body, $resource, $params, $headers) {
        $credentials = null;
        try {
            $credentials = $this->credentialsProvider->getCredentials();
        } catch (Exception $ex) {
            throw new SlsException(
                'InvalidCredentials',
                'Fail to get credentials:' . $ex->getMessage(),
                ''
            );
        }

        if ($body) {
            $headers ['Content-Length'] = strlen ( $body );
            if(isset($headers ["x-log-bodyrawsize"])==false)
                $headers ["x-log-bodyrawsize"] = 0;
            $headers ['Content-MD5'] = SlsUtil::calMD5 ( $body );
        } else {
            $headers ['Content-Length'] = 0;
            $headers ["x-log-bodyrawsize"] = 0;
            $headers ['Content-Type'] = ''; // If not set, http request will add automatically.
        }
        
        $headers ['x-log-apiversion'] = SLS_API_VERSION;
        $headers ['x-log-signaturemethod'] = 'hmac-sha1';
        if(strlen($credentials->getSecurityToken()) >0)
            $headers ['x-acs-security-token'] = $credentials->getSecurityToken();
        if(is_null($project))$headers ['Host'] = $this->logHost;
        else $headers ['Host'] = "$project.$this->logHost";
        $headers ['Date'] = $this->GetGMT ();
        $signature = SlsUtil::getRequestAuthorization ( $method, $resource, $credentials->getAccessKeySecret(), $credentials->getSecurityToken(), $params, $headers );
        $headers ['Authorization'] = "LOG ".$credentials->getAccessKeyId().":$signature";
        
        $url = $this->buildUrl($project, $resource, $params);
        return $this->sendRequest ( $method, $url, $body, $headers );
    }

    private function buildUrl($project, $resource, $params) {
        $url = $resource;
        $schema = $this->useHttps ? "https://" : "http://";
        if ($params) {
            $url .= '?' . SlsUtil::urlEncode($params);
        }
        if ($this->isRowIp) {
            return "$schema$this->endpoint$url";
        }
        if (is_null($project)) {
            return "$schema$this->endpoint$url";
        }
        return "$schema$project.$this->endpoint$url";
    }
    
    /**
     * Put logs to Log Service.
     * Unsuccessful opertaion will cause an SlsException.
     *
     * @param PutLogsRequest $request the PutLogs request parameters class
     * @throws SlsException
     * @return PutLogsResponse
     */
    public function putLogs(PutLogsRequest $request) {
        if (count ( $request->getLogitems () ) > 4096)
            throw new SlsException ( 'InvalidLogSize', "logItems' length exceeds maximum limitation: 4096 lines." );
        
        $logGroup = new LogGroup ();
        $topic = $request->getTopic () !== null ? $request->getTopic () : '';
        $logGroup->setTopic ( $request->getTopic () );
        $source = $request->getSource ();
        
        if ( ! $source )
            $source = $this->source;
        $logGroup->setSource ( $source );
        $logitems = $request->getLogitems ();
        foreach ( $logitems as $logItem ) {
            $log = new Log ();
            $log->setTime ( $logItem->getTime () );
            $content = $logItem->getContents ();
            foreach ( $content as $key => $value ) {
                $content = new LogContent ();
                $content->setKey ( $key );
                $content->setValue ( $value );
                $log->addContents ( $content );
            }

            $logGroup->addLogs ( $log );
        }

        $body = SlsUtil::toBytes( $logGroup );
        unset ( $logGroup );
        
        $bodySize = strlen ( $body );
        if ($bodySize > 3 * 1024 * 1024) // 3 MB
            throw new SlsException ( 'InvalidLogSize', "logItems' size exceeds maximum limitation: 3 MB." );
        $params = array ();
        $headers = array ();
        $headers ["x-log-bodyrawsize"] = $bodySize;
        $headers ['x-log-compresstype'] = 'deflate';
        $headers ['Content-Type'] = 'application/x-protobuf';
        $body = gzcompress ( $body, 6 );
        
        $logstore = $request->getLogstore () !== null ? $request->getLogstore () : '';
        $project = $request->getProject () !== null ? $request->getProject () : '';
        $shardKey = $request -> getShardKey();
        $resource = "/logstores/" . $logstore.($shardKey== null?"/shards/lb":"/shards/route");
        if($shardKey)
            $params["key"]=$shardKey;
        list ( $resp, $header ) = $this->send ( "POST", $project, $body, $resource, $params, $headers );
        $requestId = isset ( $header ['x-log-requestid'] ) ? $header ['x-log-requestid'] : '';
        $resp = $this->parseToJson ( $resp, $requestId );
        return new PutLogsResponse ( $header );
    }

    /**
     * create shipper service
     * @param CreateShipperRequest $request
     * return CreateShipperResponse
     */
    public function createShipper(CreateShipperRequest $request){
        $headers = array();
        $params = array();
        $resource = "/logstores/".$request->getLogStore()."/shipper";
        $project = $request->getProject () !== null ? $request->getProject () : '';
        $headers["Content-Type"] = "application/json";

        $body = array(
            "shipperName" => $request->getShipperName(),
            "targetType" => $request->getTargetType(),
            "targetConfiguration" => $request->getTargetConfigration()
        );
        $body_str = json_encode($body);
        $headers["x-log-bodyrawsize"] = strlen($body_str);
        list($resp, $header) = $this->send("POST", $project,$body_str,$resource,$params,$headers);
        $requestId = isset($header['x-log-requestid']) ? $header ['x-log-requestid'] : '';
        $resp = $this->parseToJson($resp, $requestId);
        return new CreateShipperResponse($resp, $header);
    }

    /**
     * create shipper service
     * @param UpdateShipperRequest $request
     * return UpdateShipperResponse
     */
    public function updateShipper(UpdateShipperRequest $request){
        $headers = array();
        $params = array();
        $resource = "/logstores/".$request->getLogStore()."/shipper/".$request->getShipperName();
        $project = $request->getProject () !== null ? $request->getProject () : '';
        $headers["Content-Type"] = "application/json";

        $body = array(
            "shipperName" => $request->getShipperName(),
            "targetType" => $request->getTargetType(),
            "targetConfiguration" => $request->getTargetConfigration()
        );
        $body_str = json_encode($body);
        $headers["x-log-bodyrawsize"] = strlen($body_str);
        list($resp, $header) = $this->send("PUT", $project,$body_str,$resource,$params,$headers);
        $requestId = isset($header['x-log-requestid']) ? $header ['x-log-requestid'] : '';
        $resp = $this->parseToJson($resp, $requestId);
        return new UpdateShipperResponse($resp, $header);
    }

    /**
     * get shipper tasks list, max 48 hours duration supported
     * @param GetShipperTasksRequest $request
     * return GetShipperTasksResponse
     */
    public function getShipperTasks(GetShipperTasksRequest $request){
        $headers = array();
        $params = array(
            'from' => $request->getStartTime(),
            'to' => $request->getEndTime(),
            'status' => $request->getStatusType(),
            'offset' => $request->getOffset(),
            'size' => $request->getSize()
        );
        $resource = "/logstores/".$request->getLogStore()."/shipper/".$request->getShipperName()."/tasks";
        $project = $request->getProject () !== null ? $request->getProject () : '';
        $headers["x-log-bodyrawsize"] = 0;
        $headers["Content-Type"] = "application/json";

        list($resp, $header) = $this->send("GET", $project,null,$resource,$params,$headers);
        $requestId = isset($header['x-log-requestid']) ? $header ['x-log-requestid'] : '';
        $resp = $this->parseToJson($resp, $requestId);
        return new GetShipperTasksResponse($resp, $header);
    }

    /**
     * retry shipper tasks list by task ids
     * @param RetryShipperTasksRequest $request
     * return RetryShipperTasksResponse
     */
    public function retryShipperTasks(RetryShipperTasksRequest $request){
        $headers = array();
        $params = array();
        $resource = "/logstores/".$request->getLogStore()."/shipper/".$request->getShipperName()."/tasks";
        $project = $request->getProject () !== null ? $request->getProject () : '';

        $headers["Content-Type"] = "application/json";
        $body = $request->getTaskLists();
        $body_str = json_encode($body);
        $headers["x-log-bodyrawsize"] = strlen($body_str);
        list($resp, $header) = $this->send("PUT", $project,$body_str,$resource,$params,$headers);
        $requestId = isset($header['x-log-requestid']) ? $header ['x-log-requestid'] : '';
        $resp = $this->parseToJson($resp, $requestId);
        return new RetryShipperTasksResponse($resp, $header);
    }

    /**
     * delete shipper service
     * @param DeleteShipperRequest $request
     * return DeleteShipperResponse
     */
    public function deleteShipper(DeleteShipperRequest $request){
        $headers = array();
        $params = array();
        $resource = "/logstores/".$request->getLogStore()."/shipper/".$request->getShipperName();
        $project = $request->getProject () !== null ? $request->getProject () : '';
        $headers["x-log-bodyrawsize"] = 0;
        $headers["Content-Type"] = "application/json";

        list($resp, $header) = $this->send("DELETE", $project,null,$resource,$params,$headers);
        $requestId = isset($header['x-log-requestid']) ? $header ['x-log-requestid'] : '';
        $resp = $this->parseToJson($resp, $requestId);
        return new DeleteShipperResponse($resp, $header);
    }

    /**
     * get shipper config service
     * @param GetShipperConfigRequest $request
     * return GetShipperConfigResponse
     */
    public function getShipperConfig(GetShipperConfigRequest $request){
        $headers = array();
        $params = array();
        $resource = "/logstores/".$request->getLogStore()."/shipper/".$request->getShipperName();
        $project = $request->getProject () !== null ? $request->getProject () : '';
        $headers["x-log-bodyrawsize"] = 0;
        $headers["Content-Type"] = "application/json";

        list($resp, $header) = $this->send("GET", $project,null,$resource,$params,$headers);
        $requestId = isset($header['x-log-requestid']) ? $header ['x-log-requestid'] : '';
        $resp = $this->parseToJson($resp, $requestId);
        return new GetShipperConfigResponse($resp, $header);
    }

    /**
     * list shipper service
     * @param ListShipperRequest $request
     * return ListShipperResponse
     */
    public function listShipper(ListShipperRequest $request){
        $headers = array();
        $params = array();
        $resource = "/logstores/".$request->getLogStore()."/shipper";
        $project = $request->getProject () !== null ? $request->getProject () : '';
        $headers["x-log-bodyrawsize"] = 0;
        $headers["Content-Type"] = "application/json";

        list($resp, $header) = $this->send("GET", $project,null,$resource,$params,$headers);
        $requestId = isset($header['x-log-requestid']) ? $header ['x-log-requestid'] : '';
        $resp = $this->parseToJson($resp, $requestId);
        return new ListShipperResponse($resp, $header);
    }

    /**
     * create logstore 
     * Unsuccessful opertaion will cause an SlsException.
     *
     * @param CreateLogstoreRequest $request the CreateLogStore request parameters class.
     * @throws SlsException
     * return CreateLogstoreResponse
     */
    public function createLogstore(CreateLogstoreRequest $request){
        $headers = array ();
        $params = array ();
        $resource = '/logstores';
        $project = $request->getProject () !== null ? $request->getProject () : '';
        $headers["x-log-bodyrawsize"] = 0;
        $headers["Content-Type"] = "application/json";
        $body = array(
            "logstoreName" => $request -> getLogstore(),
            "ttl" => (int)($request -> getTtl()),
            "shardCount" => (int)($request -> getShardCount())
        );
        $body_str =  json_encode($body);
        $headers["x-log-bodyrawsize"] = strlen($body_str);
        list($resp,$header)  = $this -> send("POST",$project,$body_str,$resource,$params,$headers);
        $requestId = isset ( $header ['x-log-requestid'] ) ? $header ['x-log-requestid'] : '';
        $resp = $this->parseToJson ( $resp, $requestId );
        return new CreateLogstoreResponse($resp,$header);
    }
    /**
     * update logstore 
     * Unsuccessful opertaion will cause an SlsException.
     *
     * @param UpdateLogstoreRequest $request the UpdateLogStore request parameters class.
     * @throws SlsException
     * return UpdateLogstoreResponse
     */
    public function updateLogstore(UpdateLogstoreRequest $request){
        $headers = array ();
        $params = array ();
        $project = $request->getProject () !== null ? $request->getProject () : '';
        $headers["Content-Type"] = "application/json";
        $body = array(
            "logstoreName" => $request -> getLogstore(),
            "ttl" => (int)($request -> getTtl()),
            "shardCount" => (int)($request -> getShardCount())
        );
        $resource = '/logstores/'.$request -> getLogstore();
        $body_str =  json_encode($body);
        $headers["x-log-bodyrawsize"] = strlen($body_str);
        list($resp,$header)  = $this -> send("PUT",$project,$body_str,$resource,$params,$headers);
        $requestId = isset ( $header ['x-log-requestid'] ) ? $header ['x-log-requestid'] : '';
        $resp = $this->parseToJson ( $resp, $requestId );
        return new UpdateLogstoreResponse($resp,$header);
    }
    /**
     * List all logstores of requested project.
     * Unsuccessful opertaion will cause an SlsException.
     *
     * @param ListLogstoresRequest $request the ListLogstores request parameters class.
     * @throws SlsException
     * @return ListLogstoresResponse
     */
    public function listLogstores(ListLogstoresRequest $request) {
        $headers = array ();
        $params = array ();
        $resource = '/logstores';
        $project = $request->getProject () !== null ? $request->getProject () : '';
        list ( $resp, $header ) = $this->send ( "GET", $project, NULL, $resource, $params, $headers );
        $requestId = isset ( $header ['x-log-requestid'] ) ? $header ['x-log-requestid'] : '';
        $resp = $this->parseToJson ( $resp, $requestId );
        return new ListLogstoresResponse ( $resp, $header );
    }

    /**
     * Delete logstore
     * Unsuccessful opertaion will cause an SlsException.
     *
     * @param DeleteLogstoreRequest $request the DeleteLogstores request parameters class.
     * @throws SlsException
     * @return Aliyun_Log_Models_DeleteLogstoresResponse
     */
    public function deleteLogstore(DeleteLogstoreRequest $request) {
        $headers = array ();
        $params = array ();
        $project = $request->getProject () !== null ? $request->getProject () : '';
        $logstore = $request -> getLogstore() != null ? $request -> getLogstore() :"";
        $resource = "/logstores/$logstore";
        list ( $resp, $header ) = $this->send ( "DELETE", $project, NULL, $resource, $params, $headers );
        $requestId = isset ( $header ['x-log-requestid'] ) ? $header ['x-log-requestid'] : '';
        $resp = $this->parseToJson ( $resp, $requestId );
        return new DeleteLogstoreResponse ( $resp, $header );
    }

    /**
     * List all topics in a logstore.
     * Unsuccessful opertaion will cause an SlsException.
     *
     * @param ListTopicsRequest $request the ListTopics request parameters class.
     * @throws SlsException
     * @return ListTopicsResponse
     */
    public function listTopics(ListTopicsRequest $request) {
        $headers = array ();
        $params = array ();
        if ($request->getToken () !== null)
            $params ['token'] = $request->getToken ();
        if ($request->getLine () !== null)
            $params ['line'] = $request->getLine ();
        $params ['type'] = 'topic';
        $logstore = $request->getLogstore () !== null ? $request->getLogstore () : '';
        $project = $request->getProject () !== null ? $request->getProject () : '';
        $resource = "/logstores/$logstore";
        list ( $resp, $header ) = $this->send ( "GET", $project, NULL, $resource, $params, $headers );
        $requestId = isset ( $header ['x-log-requestid'] ) ? $header ['x-log-requestid'] : '';
        $resp = $this->parseToJson ( $resp, $requestId );
        return new ListTopicsResponse ( $resp, $header );
    }

    /**
     * Get histograms of requested query from log service.
     * Unsuccessful opertaion will cause an SlsException.
     *
     * @param GetHistogramsRequest $request the GetHistograms request parameters class.
     * @throws SlsException
     * @return array(json body, http header)
     */
    public function getHistogramsJson(GetHistogramsRequest $request) {
        $headers = array ();
        $params = array ();
        if ($request->getTopic () !== null)
            $params ['topic'] = $request->getTopic ();
        if ($request->getFrom () !== null)
            $params ['from'] = $request->getFrom ();
        if ($request->getTo () !== null)
            $params ['to'] = $request->getTo ();
        if ($request->getQuery () !== null)
            $params ['query'] = $request->getQuery ();
        $params ['type'] = 'histogram';
        $logstore = $request->getLogstore () !== null ? $request->getLogstore () : '';
        $project = $request->getProject () !== null ? $request->getProject () : '';
        $resource = "/logstores/$logstore";
        list ( $resp, $header ) = $this->send ( "GET", $project, NULL, $resource, $params, $headers );
        $requestId = isset ( $header ['x-log-requestid'] ) ? $header ['x-log-requestid'] : '';
        $resp = $this->parseToJson ( $resp, $requestId );
        return array($resp, $header);
    }
    
    /**
     * Get histograms of requested query from log service.
     * Unsuccessful opertaion will cause an SlsException.
     *
     * @param GetHistogramsRequest $request the GetHistograms request parameters class.
     * @throws SlsException
     * @return GetHistogramsResponse
     */
    public function getHistograms(GetHistogramsRequest $request) {
        $ret = $this->getHistogramsJson($request);
        $resp = $ret[0];
        $header = $ret[1];
        return new GetHistogramsResponse ( $resp, $header );
    }

    /**
     * Get logs from Log service.
     * Unsuccessful opertaion will cause an SlsException.
     *
     * @param GetLogsRequest $request the GetLogs request parameters class.
     * @throws SlsException
     * @return array(json body, http header)
     */
    public function getLogsJson(GetLogsRequest $request) {
        $headers = array ();
        $params = array ();
        if ($request->getTopic () !== null)
            $params ['topic'] = $request->getTopic ();
        if ($request->getFrom () !== null)
            $params ['from'] = $request->getFrom ();
        if ($request->getTo () !== null)
            $params ['to'] = $request->getTo ();
        if ($request->getQuery () !== null)
            $params ['query'] = $request->getQuery ();
        $params ['type'] = 'log';
        if ($request->getLine () !== null)
            $params ['line'] = $request->getLine ();
        if ($request->getOffset () !== null)
            $params ['offset'] = $request->getOffset ();
        if ($request->getOffset () !== null)
            $params ['reverse'] = $request->getReverse () ? 'true' : 'false';
        if ($request -> getPowerSql() != null)
            $params ["powerSql"] = $request -> getPowerSql()? 'true' : 'false';
        $logstore = $request->getLogstore () !== null ? $request->getLogstore () : '';
        $project = $request->getProject () !== null ? $request->getProject () : '';
        $resource = "/logstores/$logstore";
        list ( $resp, $header ) = $this->send ( "GET", $project, NULL, $resource, $params, $headers );
        $requestId = isset ( $header ['x-log-requestid'] ) ? $header ['x-log-requestid'] : '';
        $resp = $this->parseToJson ( $resp, $requestId );
        return array($resp, $header);
        //return new GetLogsResponse ( $resp, $header );
    }
    
    /**
     * Get logs from Log service.
     * Unsuccessful opertaion will cause an SlsException.
     *
     * @param GetLogsRequest $request the GetLogs request parameters class.
     * @throws SlsException
     * @return GetLogsResponse
     */
    public function getLogs(GetLogsRequest $request) {
        $ret = $this->getLogsJson($request);
        $resp = $ret[0];
        $header = $ret[1];
        return new GetLogsResponse ( $resp, $header );
    }

    /**
     * Get logs from Log service.
     * Unsuccessful opertaion will cause an SlsException.
     *
     * @param GetProjectLogsRequest $request the GetLogs request parameters class.
     * @throws SlsException
     * @return array(json body, http header)
     */
    public function getProjectLogsJson(GetProjectLogsRequest $request) {
        $headers = array ();
        $params = array ();
        if ($request->getQuery () !== null)
            $params ['query'] = $request->getQuery ();
        if ($request -> getPowerSql() != null)
            $params ["powerSql"] = $request -> getPowerSql()? 'true' : 'false';
        $project = $request->getProject () !== null ? $request->getProject () : '';
        $resource = "/logs";
        list ( $resp, $header ) = $this->send ( "GET", $project, NULL, $resource, $params, $headers );
        $requestId = isset ( $header ['x-log-requestid'] ) ? $header ['x-log-requestid'] : '';
        $resp = $this->parseToJson ( $resp, $requestId );
        return array($resp, $header);
        //return new GetLogsResponse ( $resp, $header );
    }
     /**
     * Get logs from Log service.
     * Unsuccessful opertaion will cause an SlsException.
     *
     * @param GetProjectLogsRequest $request the GetLogs request parameters class.
     * @throws SlsException
     * @return GetLogsResponse
     */
    public function getProjectLogs(GetProjectLogsRequest $request) {
        $ret = $this->getProjectLogsJson($request);
        $resp = $ret[0];
        $header = $ret[1];
        return new GetLogsResponse ( $resp, $header );
    }
    /**
     * execute sql on logstore
     * Unsuccessful opertaion will cause an SlsException.
     * @param LogStoreSqlRequest $request the executeLogStoreSql request parameters class
     * @throws SlsException
     * @return LogStoreSqlResponse
     */
    public function executeLogStoreSql(LogStoreSqlRequest $request) {
        $headers = array ();
        $params = array ();
        if ($request->getFrom () !== null)
            $params ['from'] = $request->getFrom ();
        if ($request->getTo () !== null)
            $params ['to'] = $request->getTo ();
        if ($request->getQuery () !== null)
            $params ['query'] = $request->getQuery ();
        $params ['type'] = 'log';
        if ($request -> getPowerSql() != null)
            $params ["powerSql"] = $request -> getPowerSql()? 'true' : 'false';
        $logstore = $request->getLogstore () !== null ? $request->getLogstore () : '';
        $project = $request->getProject () !== null ? $request->getProject () : '';
        $resource = "/logstores/$logstore";
        list ( $resp, $header ) = $this->send ( "GET", $project, NULL, $resource, $params, $headers );
        $requestId = isset ( $header ['x-log-requestid'] ) ? $header ['x-log-requestid'] : '';
        $resp = $this->parseToJson ( $resp, $requestId );
        return new LogStoreSqlResponse($resp, $header);
    }
    /**
     * exeucte project sql.
     * Unsuccessful opertaion will cause an SlsException.
     *
     * @param ProjectSqlRequest $request the GetLogs request parameters class.
     * @throws SlsException
     * @return array(json body, http header)
     */
    public function executeProjectSqlJson(ProjectSqlRequest $request) {
        $headers = array ();
        $params = array ();
        if ($request->getQuery () !== null)
            $params ['query'] = $request->getQuery ();
        if ($request -> getPowerSql() != null)
            $params ["powerSql"] = $request -> getPowerSql()? 'true' : 'false';
        $project = $request->getProject () !== null ? $request->getProject () : '';
        $resource = "/logs";
        list ( $resp, $header ) = $this->send ( "GET", $project, NULL, $resource, $params, $headers );
        $requestId = isset ( $header ['x-log-requestid'] ) ? $header ['x-log-requestid'] : '';
        $resp = $this->parseToJson ( $resp, $requestId );
        return array($resp, $header);
        //return new GetLogsResponse ( $resp, $header );
    }
     /**
     * Get logs from Log service.
     * Unsuccessful opertaion will cause an SlsException.
     *
     * @param ProjectSqlRequest $request the GetLogs request parameters class.
     * @throws SlsException
     * @return GetLogsResponse
     */
    public function executeProjectSql(ProjectSqlRequest $request) {
        $ret = $this->executeProjectSqlJson($request);
        $resp = $ret[0];
        $header = $ret[1];
        return new Response ( $resp, $header );
    }
    /**
     * create sql instance api
     * Unsuccessful opertaion will cause an SlsException.
     * @param  $project is project name 
     * @param  $cu is max cores used concurrently in a project
     * @throws SlsException
     * @return CreateSqlInstanceResponse
     */
    public function createSqlInstance($project, $cu)
    {
        $headers = array();
        $params = array();
        $resource = '/sqlinstance';
        $headers['x-log-bodyrawsize'] = 0;
        $headers ['Content-Type'] = 'application/json';
        $body = array(
            "cu"=>$cu
        );
        $body_str = json_encode($body);
        list($resp,$header)  = $this -> send("POST",$project,$body_str,$resource,$params,$headers);    
        $requestId = isset ( $header ['x-log-requestid'] ) ?
            $header ['x-log-requestid'] : '';
        $resp = $this->parseToJson ( $resp, $requestId );
        return new CreateSqlInstanceResponse($resp,$header);   
    }
    
    /**
     * update sql instance api
     * Unsuccessful opertaion will cause an SlsException.
     * @param  $project is project name 
     * @param  $cu is max cores used concurrently in a project
     * @throws SlsException
     * @return UpdateSqlInstanceResponse
     */
    public function updateSqlInstance($project, $cu)
    {
        $headers = array();
        $params = array();
        $resource = '/sqlinstance';
        $headers['x-log-bodyrawsize'] = 0;
        $headers ['Content-Type'] = 'application/json';
        $body = array(
            "cu"=>$cu
        );
        $body_str = json_encode($body);
        list($resp,$header)  = $this -> send("PUT",$project,$body_str,$resource,$params,$headers);    
        $requestId = isset ( $header ['x-log-requestid'] ) ?
            $header ['x-log-requestid'] : '';
        $resp = $this->parseToJson ( $resp, $requestId );
        return new UpdateSqlInstanceResponse($resp,$header);   
    }
    /**
     * get sql instance api
     * Unsuccessful opertaion will cause an SlsException.
     * @param  $project is project name 
     * @throws SlsException
     * @return UpdateSqlInstanceResponse
     */
    public function listSqlInstance($project)
    {
        $headers = array();
        $headers['Content-Type'] = 'application/x-protobuf';
        $hangzhou['Content-Length'] = '0';
        $params = array();
        $resource = '/sqlinstance';
        $body_str = "";
        list($resp,$header)  = $this -> send("GET",$project,$body_str,$resource,$params,$headers);    
        $requestId = isset ( $header ['x-log-requestid'] ) ?
            $header ['x-log-requestid'] : '';
        $resp = $this->parseToJson ( $resp, $requestId );
        return new ListSqlInstanceResponse($resp,$header);   
    }

    /**
     * Get logs from Log service with shardid conditions.
     * Unsuccessful opertaion will cause an SlsException.
     *
     * @param BatchGetLogsRequest $request the BatchGetLogs request parameters class.
     * @throws SlsException
     * @return BatchGetLogsResponse
     */
    public function batchGetLogs(BatchGetLogsRequest $request) {
      $params = array();
      $headers = array();
      $project = $request->getProject()!==null?$request->getProject():'';
      $logstore = $request->getLogstore()!==null?$request->getLogstore():'';
      $shardId = $request->getShardId()!==null?$request->getShardId():'';
      if($request->getCount()!==null)
          $params['count']=$request->getCount();
      if($request->getCursor()!==null)
          $params['cursor']=$request->getCursor();
	  if($request->getEndCursor()!==null)
          $params['end_cursor']=$request->getEndCursor();
      $params['type']='log';
      $headers['Accept-Encoding']='gzip';
      $headers['accept']='application/x-protobuf';

      $resource = "/logstores/$logstore/shards/$shardId";
      list($resp,$header) = $this->send("GET",$project,NULL,$resource,$params,$headers);
      //$resp is a byteArray
      $resp =  gzuncompress($resp);
      if($resp===false)$resp = new LogGroupList();
      
      else {
          $resp = new LogGroupList($resp);
      }
      return new BatchGetLogsResponse ( $resp, $header );
    }

    /**
     * List Shards from Log service with Project and logstore conditions.
     * Unsuccessful opertaion will cause an SlsException.
     *
     * @param ListShardsRequest $request the ListShards request parameters class.
     * @throws SlsException
     * @return ListShardsResponse
     */
    public function listShards(ListShardsRequest $request) {
        $params = array();
        $headers = array();
        $project = $request->getProject()!==null?$request->getProject():'';
        $logstore = $request->getLogstore()!==null?$request->getLogstore():'';

        $resource='/logstores/'.$logstore.'/shards';
        list($resp,$header) = $this->send("GET",$project,NULL,$resource,$params,$headers); 
        $requestId = isset ( $header ['x-log-requestid'] ) ? $header ['x-log-requestid'] : '';
        $resp = $this->parseToJson ( $resp, $requestId );
        return new ListShardsResponse ( $resp, $header );
    }

    /**
     * split a shard into two shards  with Project and logstore and shardId and midHash conditions.
     * Unsuccessful opertaion will cause an SlsException.
     *
     * @param SplitShardRequest $request the SplitShard request parameters class.
     * @throws SlsException
     * @return ListShardsResponse
     */
    public function splitShard(SplitShardRequest $request) {
        $params = array();
        $headers = array();
        $project = $request->getProject()!==null?$request->getProject():'';
        $logstore = $request->getLogstore()!==null?$request->getLogstore():'';
        $shardId = $request -> getShardId()!== null ? $request -> getShardId():-1;
        $midHash = $request -> getMidHash()!= null?$request -> getMidHash():"";

        $resource='/logstores/'.$logstore.'/shards/'.$shardId;
        $params["action"] = "split";
        $params["key"] = $midHash;
        list($resp,$header) = $this->send("POST",$project,NULL,$resource,$params,$headers); 
        $requestId = isset ( $header ['x-log-requestid'] ) ? $header ['x-log-requestid'] : '';
        $resp = $this->parseToJson ( $resp, $requestId );
        return new ListShardsResponse ( $resp, $header );
    }
    /**
     * merge two shards into one shard with Project and logstore and shardId and conditions.
     * Unsuccessful opertaion will cause an SlsException.
     *
     * @param MergeShardsRequest $request the MergeShards request parameters class.
     * @throws SlsException
     * @return ListShardsResponse
     */
    public function MergeShards(MergeShardsRequest $request) {
        $params = array();
        $headers = array();
        $project = $request->getProject()!==null?$request->getProject():'';
        $logstore = $request->getLogstore()!==null?$request->getLogstore():'';
        $shardId = $request -> getShardId()!= null ? $request -> getShardId():-1;

        $resource='/logstores/'.$logstore.'/shards/'.$shardId;
        $params["action"] = "merge";
        list($resp,$header) = $this->send("POST",$project,NULL,$resource,$params,$headers); 
        $requestId = isset ( $header ['x-log-requestid'] ) ? $header ['x-log-requestid'] : '';
        $resp = $this->parseToJson ( $resp, $requestId );
        return new ListShardsResponse ( $resp, $header );
    }
    /**
     * delete a read only shard with Project and logstore and shardId conditions.
     * Unsuccessful opertaion will cause an SlsException.
     *
     * @param DeleteShardRequest $request the DeleteShard request parameters class.
     * @throws SlsException
     * @return ListShardsResponse
     */
    public function DeleteShard(DeleteShardRequest $request) {
        $params = array();
        $headers = array();
        $project = $request->getProject()!==null?$request->getProject():'';
        $logstore = $request->getLogstore()!==null?$request->getLogstore():'';
        $shardId = $request -> getShardId()!= null ? $request -> getShardId():-1;

        $resource='/logstores/'.$logstore.'/shards/'.$shardId;
        list($resp,$header) = $this->send("DELETE",$project,NULL,$resource,$params,$headers); 
        $requestId = isset ( $header ['x-log-requestid'] ) ? $header ['x-log-requestid'] : '';
        return new DeleteShardResponse ( $header );
    }

    /**
     * Get cursor from Log service.
     * Unsuccessful opertaion will cause an SlsException.
     *
     * @param GetCursorRequest $request the GetCursor request parameters class.
     * @throws SlsException
     * @return GetCursorResponse
     */
    public function getCursor(GetCursorRequest $request){
      $params = array();
      $headers = array();
      $project = $request->getProject()!==null?$request->getProject():'';
      $logstore = $request->getLogstore()!==null?$request->getLogstore():'';
      $shardId = $request->getShardId()!==null?$request->getShardId():'';
      $mode = $request->getMode()!==null?$request->getMode():'';
      $fromTime = $request->getFromTime()!==null?$request->getFromTime():-1;

      if((empty($mode) xor $fromTime==-1)==false){
        if(!empty($mode))
          throw new SlsException ( 'RequestError',"Request is failed. Mode and fromTime can not be not empty simultaneously");
        else
          throw new SlsException ( 'RequestError',"Request is failed. Mode and fromTime can not be empty simultaneously");
      }
      if(!empty($mode) && strcmp($mode,'begin')!==0 && strcmp($mode,'end')!==0)
        throw new SlsException ( 'RequestError',"Request is failed. Mode value invalid:$mode");
      if($fromTime!==-1 && (is_integer($fromTime)==false || $fromTime<0))
        throw new SlsException ( 'RequestError',"Request is failed. FromTime value invalid:$fromTime");
      $params['type']='cursor';
      if($fromTime!==-1)$params['from']=$fromTime;
      else $params['mode'] = $mode;
      $resource='/logstores/'.$logstore.'/shards/'.$shardId;
      list($resp,$header) = $this->send("GET",$project,NULL,$resource,$params,$headers); 
      $requestId = isset ( $header ['x-log-requestid'] ) ? $header ['x-log-requestid'] : '';
      $resp = $this->parseToJson ( $resp, $requestId );
      return new GetCursorResponse($resp,$header);
    }

    public function createConfig(CreateConfigRequest $request){
        $params = array();
        $headers = array();
        $body=null;
        if($request->getConfig()!==null){
          $body = json_encode($request->getConfig()->toArray());
        }
        $headers ['Content-Type'] = 'application/json';
        $resource = '/configs';
        list($resp,$header) = $this->send("POST",NULL,$body,$resource,$params,$headers); 
        return new CreateConfigResponse($header);
    }

    public function updateConfig(UpdateConfigRequest $request){
        $params = array();
        $headers = array();
        $body=null;
        $configName='';
        if($request->getConfig()!==null){
          $body = json_encode($request->getConfig()->toArray());
          $configName=($request->getConfig()->getConfigName()!==null)?$request->getConfig()->getConfigName():'';
        }
        $headers ['Content-Type'] = 'application/json';
        $resource = '/configs/'.$configName;
        list($resp,$header) = $this->send("PUT",NULL,$body,$resource,$params,$headers);  
        return new UpdateConfigResponse($header);
    }

    public function getConfig(GetConfigRequest $request){
        $params = array();
        $headers = array();

        $configName = ($request->getConfigName()!==null)?$request->getConfigName():'';
        
        $resource = '/configs/'.$configName;
        list($resp,$header) = $this->send("GET",NULL,NULL,$resource,$params,$headers); 
        $requestId = isset ( $header ['x-log-requestid'] ) ? $header ['x-log-requestid'] : '';
        $resp = $this->parseToJson ( $resp, $requestId );
        return new GetConfigResponse($resp,$header);
    }

    public function deleteConfig(DeleteConfigRequest $request){
        $params = array();
        $headers = array();
        $configName = ($request->getConfigName()!==null)?$request->getConfigName():'';
        $resource = '/configs/'.$configName;
        list($resp,$header) = $this->send("DELETE",NULL,NULL,$resource,$params,$headers); 
        return new DeleteConfigResponse($header);
    }

    public function listConfigs(ListConfigsRequest $request){
        $params = array();
        $headers = array();

        if($request->getConfigName()!==null)$params['configName'] = $request->getConfigName();
        if($request->getOffset()!==null)$params['offset'] = $request->getOffset();
        if($request->getSize()!==null)$params['size'] = $request->getSize();

        $resource = '/configs';
        list($resp,$header) = $this->send("GET",NULL,NULL,$resource,$params,$headers); 
        $requestId = isset ( $header ['x-log-requestid'] ) ? $header ['x-log-requestid'] : '';
        $resp = $this->parseToJson ( $resp, $requestId );
        return new ListConfigsResponse($resp,$header);
    }
    
    public function createMachineGroup(CreateMachineGroupRequest $request){
        $params = array();
        $headers = array();
        $body=null;
        if($request->getMachineGroup()!==null){
          $body = json_encode($request->getMachineGroup()->toArray());
        }
        $headers ['Content-Type'] = 'application/json';
        $resource = '/machinegroups';
        list($resp,$header) = $this->send("POST",NULL,$body,$resource,$params,$headers); 

        return new CreateMachineGroupResponse($header);
    }

    public function updateMachineGroup(UpdateMachineGroupRequest $request){
        $params = array();
        $headers = array();
        $body=null;
        $groupName='';
        if($request->getMachineGroup()!==null){
          $body = json_encode($request->getMachineGroup()->toArray());
          $groupName=($request->getMachineGroup()->getGroupName()!==null)?$request->getMachineGroup()->getGroupName():'';
        }
        $headers ['Content-Type'] = 'application/json';
        $resource = '/machinegroups/'.$groupName;
        list($resp,$header) = $this->send("PUT",NULL,$body,$resource,$params,$headers);  
        return new UpdateMachineGroupResponse($header);
    }

    public function getMachineGroup(GetMachineGroupRequest $request){
        $params = array();
        $headers = array();

        $groupName = ($request->getGroupName()!==null)?$request->getGroupName():'';
        
        $resource = '/machinegroups/'.$groupName;
        list($resp,$header) = $this->send("GET",NULL,NULL,$resource,$params,$headers); 
        $requestId = isset ( $header ['x-log-requestid'] ) ? $header ['x-log-requestid'] : '';
        $resp = $this->parseToJson ( $resp, $requestId );
        return new GetMachineGroupResponse($resp,$header);
    }

    public function deleteMachineGroup(DeleteMachineGroupRequest $request){
        $params = array();
        $headers = array();

        $groupName = ($request->getGroupName()!==null)?$request->getGroupName():'';
        $resource = '/machinegroups/'.$groupName;
        list($resp,$header) = $this->send("DELETE",NULL,NULL,$resource,$params,$headers); 
        return new DeleteMachineGroupResponse($header);
    }

    public function listMachineGroups(ListMachineGroupsRequest $request){
        $params = array();
        $headers = array();

        if($request->getGroupName()!==null)$params['groupName'] = $request->getGroupName();
        if($request->getOffset()!==null)$params['offset'] = $request->getOffset();
        if($request->getSize()!==null)$params['size'] = $request->getSize();

        $resource = '/machinegroups';
        list($resp,$header) = $this->send("GET",NULL,NULL,$resource,$params,$headers); 
        $requestId = isset ( $header ['x-log-requestid'] ) ? $header ['x-log-requestid'] : '';
        $resp = $this->parseToJson ( $resp, $requestId );

        return new ListMachineGroupsResponse($resp,$header);
    }

    public function applyConfigToMachineGroup(ApplyConfigToMachineGroupRequest $request){
        $params = array();
        $headers = array();
        $configName=$request->getConfigName();
        $groupName=$request->getGroupName();
        $headers ['Content-Type'] = 'application/json';
        $resource = '/machinegroups/'.$groupName.'/configs/'.$configName;
        list($resp,$header) = $this->send("PUT",NULL,NULL,$resource,$params,$headers);  
        return new ApplyConfigToMachineGroupResponse($header);
    }

    public function removeConfigFromMachineGroup(RemoveConfigFromMachineGroupRequest $request){
        $params = array();
        $headers = array();
        $configName=$request->getConfigName();
        $groupName=$request->getGroupName();
        $headers ['Content-Type'] = 'application/json';
        $resource = '/machinegroups/'.$groupName.'/configs/'.$configName;
        list($resp,$header) = $this->send("DELETE",NULL,NULL,$resource,$params,$headers);  
        return new RemoveConfigFromMachineGroupResponse($header);
    }

    public function getMachine(GetMachineRequest $request){
        $params = array();
        $headers = array();

        $uuid = ($request->getUuid()!==null)?$request->getUuid():'';

        $resource = '/machines/'.$uuid;
        list($resp,$header) = $this->send("GET",NULL,NULL,$resource,$params,$headers);
        $requestId = isset ( $header ['x-log-requestid'] ) ? $header ['x-log-requestid'] : '';
        $resp = $this->parseToJson ( $resp, $requestId );
        return new GetMachineResponse($resp,$header);
    }

    public function createACL(CreateACLRequest $request){
        $params = array();
        $headers = array();
        $body=null;
        if($request->getAcl()!==null){
          $body = json_encode($request->getAcl()->toArray());
        }
        $headers ['Content-Type'] = 'application/json';
        $resource = '/acls';
        list($resp,$header) = $this->send("POST",NULL,$body,$resource,$params,$headers);
        $requestId = isset ( $header ['x-log-requestid'] ) ? $header ['x-log-requestid'] : '';
        $resp = $this->parseToJson ( $resp, $requestId );
        return new CreateACLResponse($resp,$header);
    }

    public function updateACL(UpdateACLRequest $request){
        $params = array();
        $headers = array();
        $body=null;
        $aclId='';
        if($request->getAcl()!==null){
          $body = json_encode($request->getAcl()->toArray());
          $aclId=($request->getAcl()->getAclId()!==null)?$request->getAcl()->getAclId():'';
        }
        $headers ['Content-Type'] = 'application/json';
        $resource = '/acls/'.$aclId;
        list($resp,$header) = $this->send("PUT",NULL,$body,$resource,$params,$headers);  
        return new UpdateACLResponse($header);
    }
    
    public function getACL(GetACLRequest $request){
        $params = array();
        $headers = array();

        $aclId = ($request->getAclId()!==null)?$request->getAclId():'';
        
        $resource = '/acls/'.$aclId;
        list($resp,$header) = $this->send("GET",NULL,NULL,$resource,$params,$headers); 
        $requestId = isset ( $header ['x-log-requestid'] ) ? $header ['x-log-requestid'] : '';
        $resp = $this->parseToJson ( $resp, $requestId );

        return new GetACLResponse($resp,$header);
    }
    
    public function deleteACL(DeleteACLRequest $request){
        $params = array();
        $headers = array();
        $aclId = ($request->getAclId()!==null)?$request->getAclId():'';
        $resource = '/acls/'.$aclId;
        list($resp,$header) = $this->send("DELETE",NULL,NULL,$resource,$params,$headers); 
        return new DeleteACLResponse($header);
    }
    
    public function listACLs(ListACLsRequest $request){
        $params = array();
        $headers = array();
        if($request->getPrincipleId()!==null)$params['principleId'] = $request->getPrincipleId();
        if($request->getOffset()!==null)$params['offset'] = $request->getOffset();
        if($request->getSize()!==null)$params['size'] = $request->getSize();

        $resource = '/acls';
        list($resp,$header) = $this->send("GET",NULL,NULL,$resource,$params,$headers); 
        $requestId = isset ( $header ['x-log-requestid'] ) ? $header ['x-log-requestid'] : '';
        $resp = $this->parseToJson ( $resp, $requestId );
        return new ListACLsResponse($resp,$header);
    }

}

