<?php

namespace App\Services\OFm;

use App\Services\OFm\OFmRecord;
use App\Services\OFm\OFmError;
use GuzzleHttp\Client;
use Illuminate\Support\Facades\Redis;
use Illuminate\Support\Facades\Log;

/**
 * Provides an object with which to interact with the FileMaker Data API
 */
class DataApi {

  use OFmTrait;

  protected $server;

  protected $user;

  protected $password;

  protected $dbName;

  protected $client;

  protected $fmToken = '';

  protected $config;

  public function __construct($server, $user, $password, $dbName) {
    $this->server = $server;
    $this->user = $user;
    $this->password = $password;
    $this->dbName = $dbName;
  }

  protected function initializeConnection() {
    $this->client = new Client(['base_uri' => 'https://' . env('FM_SERVER', '') . '/fmi/data/v1/databases/' . $this->dbName . '/']);
    if (empty($this->fmToken)) {
      //We need an auth token
      $auth = [$this->user, $this->password];
      try {
        $response = $this->client->post('sessions', [
          'headers' => [
            'Content-Type' => 'application/json',
          ],
          'auth' => $auth,
          'connect_timeout' => 5,
        ]);
      } catch (\Exception $e) {
        throw new OFmException('Unable to connect');
      }
      $body = json_decode((string)$response->getBody());
      if (!empty($body->response->token)) {
        $this->fmToken = $body->response->token;
      }

    }

    return TRUE;
  }

  public function find($layout, $params, $countOnly = false) {
    $options = [];
    try {
      $options = [
        'body' => json_encode($params),
        'headers' => [
          'Content-Type' => 'application/json',
        ],
      ];
      $response = $this->post('layouts/' . $layout . '/_find', $options);
      $contents = json_decode((string)$response->getBody());
      $results = [];
      if (!empty($contents->response->data)) {
        //$results = $this->parseResults($layout, $contents->response->data);
      }
      if (!empty($results)) {
        return $results;
      }
    } catch (OFmException $ex) {
      if ($ex->getCode() != 401) {
        $this->logError($ex, $this->dbName, $layout, $options);
      }
    }
    return FALSE;
  }

  protected function post($url, $options = []) {
    try {
      if (!is_object($this->client)) {
        $this->initializeConnection();
      }
      if (!empty($this->fmToken)) {
        $options['headers']['Authorization'] = 'Bearer ' . $this->fmToken;
      }

      return $this->client->post($url, $options);
    } catch (\Exception $e) {
      //$response = json_decode($e->getResponse()->getBody()->getContents());
      Log::debug($e->getMessage());
      throw new OFmException($e->getMessage());
    }
  }

  public function getRecord($layout, $recordId) {
    if (!is_object($this->client)) {
      $this->initializeConnection();
    }
    $options = [
      'headers' => [
        'FM-Data-token' => $this->fmToken,
      ],
    ];
    try {
      $response = $this->get('record/' . $this->dbName . '/' . $layout . '/' . $recordId, $options);
      $contents = json_decode($response->getBody()->getContents());
      if ($contents->result == 'OK' && !empty($contents->data)) {
        $results = $this->parseResults($layout, $contents);
        return reset($results);
      }
    } catch (OFmException $e) {
      if ($e->getCode() != 401) {
        $this->logError($e, $this->dbName, $layout, $options);
      }
    }
    return FALSE;
  }

  protected function get($url, $options = []) {
    try {
      return $this->client->get($url, $options);
    } catch (\Exception $e) {
      $response = json_decode($e->getResponse()->getBody()->getContents());
      throw new OFmException($e->getMessage(), $response->errorCode);
    }
  }
}
