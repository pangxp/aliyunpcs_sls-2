<?php
/**
 * Copyright (C) Alibaba Cloud Computing
 * All rights reserved
 */

require __DIR__ . '/../vendor/autoload.php';

use SLS\Client;
use SLS\Models\LogItem;
use SLS\Models\Request\PutLogsRequest;
use SLS\Models\Request\ListShardsRequest;
use SLS\Models\Request\ListTopicsRequest;
use SLS\Models\Request\MergeShardsRequest;
use SLS\Models\Request\DeleteShardRequest;
use SLS\Models\Request\SplitShardRequest;
use SLS\Models\Request\GetCursorRequest;
use SLS\Models\Request\BatchGetLogsRequest;
use SLS\Models\Request\ListLogstoresRequest;
use SLS\Models\Request\GetHistogramsRequest;
use SLS\Models\Request\GetLogsRequest;
use SLS\Models\Request\LogStoreSqlRequest;
use SLS\Models\Request\GetProjectLogsRequest;
use SLS\Models\Request\ProjectSqlRequest;

function putLogs(Client $client, $project, $logstore) {
    $topic = 'TestTopic';
    
    $contents = array( // key-value pair
        'TestKey'=>'TestContent'
    );
    $logItem = new LogItem();
    $logItem->setTime(time());
    $logItem->setContents($contents);
    $logitems = array($logItem);
    $request = new PutLogsRequest($project, $logstore, 
            $topic, null, $logitems);
    
    try {
        $response = $client->putLogs($request);
        logVarDump($response);
    } catch (SlsException $ex) {
        logVarDump($ex);
    } catch (Exception $ex) {
        logVarDump($ex);
    }
}

function listLogstores(Client $client, $project) {
    try{
        $request = new ListLogstoresRequest($project);
        $response = $client->listLogstores($request);
        logVarDump($response);
    } catch (SlsException $ex) {
        logVarDump($ex);
    } catch (Exception $ex) {
        logVarDump($ex);
    }
}


function listTopics(Client $client, $project, $logstore) {
    $request = new ListTopicsRequest($project, $logstore);
    
    try {
        $response = $client->listTopics($request);
        logVarDump($response);
    } catch (SlsException $ex) {
        logVarDump($ex);
    } catch (Exception $ex) {
        logVarDump($ex);
    }
}

function getLogs(Client $client, $project, $logstore) {
    $topic = 'TestTopic';
    $from = time()-3600;
    $to = time();
    $request = new GetLogsRequest($project, $logstore, $from, $to, $topic, '', 100, 0, False);
    
    try {
        $response = $client->getLogs($request);
        foreach($response -> getLogs() as $log)
        {
            print $log -> getTime()."\t";
            foreach($log -> getContents() as $key => $value){
                print $key.":".$value."\t";
            }
            print "\n";
        }

    } catch (SlsException $ex) {
        logVarDump($ex);
    } catch (Exception $ex) {
        logVarDump($ex);
    }
}

function getLogsWithPowerSql(Client $client, $project, $logstore) {
    $topic = '';
    $from = time()-3600;
    $to = time();
    $query = "* | select count(method)";
    $request = new LogStoreSqlRequest($project, $logstore, $from, $to, $query, True);
   
    try {
        $response = $client->executeLogStoreSql($request);
        foreach($response -> getLogs() as $log)
        {
            print $log -> getTime()."\t";
            foreach($log -> getContents() as $key => $value){
                print $key.":".$value."\t";
            }
            print "\n";
        }
        print "proccesedRows:".$response -> getProcessedRows()."\n";
        print "elapsedMilli:".$response -> getElapsedMilli()."\n";
        print "cpuSec:".$response -> getCpuSec()."\n";
        print "cpuCores:".$response -> getCpuCores()."\n";

    } catch (SlsException $ex) {
        logVarDump($ex);
    } catch (Exception $ex) {
        logVarDump($ex);
    }
}
function getProjectLogsWithPowerSql(Client $client, $project) {
    $query = " select count(method) from sls_operation_log where __time__ > to_unixtime(now()) - 300 and __time__ < to_unixtime(now())";
    $request = new GetProjectLogsRequest($project,  $query, True);
   
    try {
        $response = $client->getProjectLogs($request);
        #$response = $client->getProjectLogs($request);
        foreach($response -> getLogs() as $log)
        {
            print $log -> getTime()."\t";
            foreach($log -> getContents() as $key => $value){
                print $key.":".$value."\t";
            }
            print "\n";
        }
        print "proccesedRows:".$response -> getProcessedRows()."\n";
        print "elapsedMilli:".$response -> getElapsedMilli()."\n";
        print "cpuSec:".$response -> getCpuSec()."\n";
        print "cpuCores:".$response -> getCpuCores()."\n";
        print "requestId:".$response ->getRequestId()."\n";

    } catch (SlsException $ex) {
        logVarDump($ex);
    } catch (Exception $ex) {
        logVarDump($ex);
    }
}
function executeProjectSqlWithPowerSql(Client $client, $project) {
    $query = " select count(method) from sls_operation_log where __time__ > to_unixtime(now()) - 300 and __time__ < to_unixtime(now())";
    $request = new ProjectSqlRequest($project,  $query, True);
   
    try {
        $response = $client->executeProjectSql($request);
        #$response = $client->getProjectLogs($request);
        foreach($response -> getLogs() as $log)
        {
            print $log -> getTime()."\t";
            foreach($log -> getContents() as $key => $value){
                print $key.":".$value."\t";
            }
            print "\n";
        }
        print "proccesedRows:".$response -> getProcessedRows()."\n";
        print "elapsedMilli:".$response -> getElapsedMilli()."\n";
        print "cpuSec:".$response -> getCpuSec()."\n";
        print "cpuCores:".$response -> getCpuCores()."\n";
        print "requestId:".$response ->getRequestId()."\n";

    } catch (SlsException $ex) {
        logVarDump($ex);
    } catch (Exception $ex) {
        logVarDump($ex);
    }
}
function crudSqlInstance(Client $client,$project){
    $res = $client -> createSqlInstance($project,1000);
    logVarDump($res);
    $res = $client -> updateSqlInstance($project,999);
    logVarDump($res);
    $res = $client -> listSqlInstance($project);
    logVarDump($res);
}
function getHistograms(Client $client, $project, $logstore) {
    $topic = 'TestTopic';
    $from = time()-3600;
    $to = time();
    $request = new GetHistogramsRequest($project, $logstore, $from, $to, $topic, '');
    
    try {
        $response = $client->getHistograms($request);
        logVarDump($response);
    } catch (SlsException $ex) {
        logVarDump($ex);
    } catch (Exception $ex) {
        logVarDump($ex);
    }
}
function listShard(Client $client,$project,$logstore){
    $request = new ListShardsRequest($project,$logstore);
    try
    {
        $response = $client -> listShards($request);
        logVarDump($response);
    } catch (SlsException $ex) {
        logVarDump($ex);
    } catch (Exception $ex) {
        logVarDump($ex);
    }
}

function batchGetLogs(Client $client,$project,$logstore)
{
    $listShardRequest = new ListShardsRequest($project,$logstore);
    $listShardResponse = $client -> listShards($listShardRequest);
    foreach($listShardResponse-> getShardIds()  as $shardId)
    {
        $getCursorRequest = new GetCursorRequest($project,$logstore,$shardId,null, time() - 60);
        $response = $client -> getCursor($getCursorRequest);
        $cursor = $response-> getCursor();
        $count = 100;
        while(true)
        {
            $batchGetDataRequest = new BatchGetLogsRequest($project,$logstore,$shardId,$count,$cursor);
            logVarDump($batchGetDataRequest);
            $response = $client -> batchGetLogs($batchGetDataRequest);
            if($cursor == $response -> getNextCursor())
            {
                break;
            }
            $logGroupList = $response -> getLogGroupList();
            foreach($logGroupList as $logGroup)
            {
                print ($logGroup->getCategory());

                foreach($logGroup -> getLogsArray() as $log)
                {
                    foreach($log -> getContentsArray() as $content)
                    {
                        print($content-> getKey().":".$content->getValue()."\t");
                    }
                    print("\n");
                }
            }
            $cursor = $response -> getNextCursor();
        }
    }
}

function batchGetLogsWithRange(Client $client,$project,$logstore)
{
    $listShardRequest = new ListShardsRequest($project,$logstore);
    $listShardResponse = $client -> listShards($listShardRequest);
    foreach($listShardResponse-> getShardIds()  as $shardId)
    {
        //pull data which reached server at time range [now - 60s, now) for every shard
        $curTime = time();
        $beginCursorResponse = $client->getCursor(new GetCursorRequest($project,$logstore,$shardId,null,$curTime - 60));
        $beginCursor = $beginCursorResponse-> getCursor();
        $endCursorResponse = $client -> getCursor(new GetCursorRequest($project,$logstore,$shardId,null,$curTime));
        $endCursor = $endCursorResponse-> getCursor();
        $cursor = $beginCursor;
        print("-----------------------------------------\nbatchGetLogs for shard: ".$shardId.", cursor range: [".$beginCursor.", ".$endCursor.")\n");
        $count = 100;
        while(true)
        {
            $batchGetDataRequest = new BatchGetLogsRequest($project,$logstore,$shardId,$count,$cursor,$endCursor);
            $response = $client -> batchGetLogs($batchGetDataRequest);
            $logGroupList = $response -> getLogGroupList();
            $logGroupCount = 0;
            $logCount = 0;
            foreach($logGroupList as $logGroup)
            {
                $logGroupCount += 1;
                foreach($logGroup -> getLogsArray() as $log)
                {
                    $logCount += 1;
                    foreach($log -> getContentsArray() as $content)
                    {
                        print($content-> getKey().":".$content->getValue()."\t");
                    }
                    print("\n");
                }
            }
            $nextCursor = $response -> getNextCursor();
            print("batchGetLogs once, cursor: ".$cursor.", nextCursor: ".nextCursor.", logGroups: ".$logGroupCount.", logs: ".$logCount."\n");
            if($cursor == $nextCursor)
            {
                //read data finished
                break;
            }
            $cursor = $nextCursor;
        }
    }
}

function mergeShard(Client $client,$project,$logstore,$shardId)
{
    $request = new MergeShardsRequest($project,$logstore,$shardId);
    try
    {
        $response = $client -> mergeShards($request);
        logVarDump($response);
    }catch (SlsException $ex) {
        logVarDump($ex);
    } catch (Exception $ex) {
        logVarDump($ex);
    }
}
function splitShard(Client $client,$project,$logstore,$shardId,$midHash)
{
    $request = new SplitShardRequest($project,$logstore,$shardId,$midHash);
    try
    {
        $response = $client -> splitShard($request);
        logVarDump($response);
    }catch (SlsException $ex) {
        logVarDump($ex);
    } catch (Exception $ex) {
        logVarDump($ex);
    }
}

function logVarDump($expression){
    print "<br>loginfo begin = ".get_class($expression)."<br>";
    var_dump($expression);
    print "<br>loginfo end<br>";
}

/*
 * please refer to aliyun sdk document for detail:
 * http://help.aliyun-inc.com/internaldoc/detail/29074.html?spm=0.0.0.0.tqUNn5
 */
$endpoint = 'http://cn-hangzhou-yunlei-intranet.log.aliyuncs.com';
$accessKeyId = '';
$accessKey = '';
$project = 'ali-cn-yunlei-sls-admin';
$logstore = 'sls_operation_log';
$token = "";

$client = new Client($endpoint, $accessKeyId, $accessKey,$token);
listShard($client,$project,$logstore);
#mergeShard($client,$project,$logstore,2);
#deleteShard($client,$project,$logstore,2);
#splitShard($client,$project,$logstore,2,"80000000000000000000000000000001");
#putLogs($client, $project, $logstore);
#listShard($client,$project,$logstore);
#batchGetLogs($client,$project,$logstore);
#batchGetLogsWithRange($client,$project,$logstore);
#listLogstores($client, $project);
#listTopics($client, $project, $logstore);
#getHistograms($client, $project, $logstore);
#getLogs($client, $project, $logstore);
// executeProjectSqlWithPowerSql($client,$project);
// getProjectLogsWithPowerSql($client,$project);
// getLogsWithPowerSql($client, $project, $logstore);
// crudSqlInstance($client,$project);
