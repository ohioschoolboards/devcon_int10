<?php
/**
 * Created by PhpStorm.
 * User: cgrewe
 * Date: 10/8/2018
 * Time: 2:53 PM
 */

namespace App\Services;

use App\Services\OFm\OFmTrait;
use Illuminate\Support\Facades\Config;
use App\Services\OFm\DataApi;


class OFm {

  use OFmTrait;

  protected $fmConfig;

  protected $debug = TRUE;

  protected static $databases = [];

  protected static $valueLists = [];

  /**
   * Constructor, loads fmConfig variable from controlling file
   */
  public function __construct() {
    $this->fmConfig = Config::get('ofm.databases');
  }

  /**
   * Connects to a FM database
   *
   * @param $dbName
   *
   * @return bool|mixed
   */
  private function connect($dbName) {
    if (!empty(static::$databases[$dbName])) {
      return static::$databases[$dbName];
    }
    else {
      if (!empty($this->fmConfig[$dbName])) {
        try {
          $dbConnection = new DataApi(
            $this->fmConfig[$dbName]['server'],
            $this->fmConfig[$dbName]['user'],
            $this->fmConfig[$dbName]['password'],
            $this->fmConfig[$dbName]['file']
          );
          if ($dbConnection) {
            static::$databases[$dbName] = $dbConnection;
            return static::$databases[$dbName];
          }
        } catch (Exception $ex) {
          return FALSE; //Replace with thrown error
        }
      }
      else {
        return FALSE; // Replace with thrown error
      }
    }
  }

  /**
   * Executes a single find
   *
   * @param $dbName
   * @param $layout
   * @param $params
   * @param bool $single
   * @param array $sort
   * @param array $range
   *
   * @return bool|mixed
   */
  public function search($dbName, $layout, $params, $single = FALSE, $sort = [], $range = []) {
    $start = microtime(TRUE);
    $obj = $this->connect($dbName);
    $midpoint = microtime(TRUE);
    if ($obj) {
      //Translate the params
      $firstParam = reset($params);
      $body = [];
      if (is_array($firstParam)) {
        //It's a compound scenario, loop to preserve request ordering
        foreach ($params AS $paramSet) {
          asort($paramSet);
          $this->paramsToStrings($paramSet);
          $body['query'][] = $paramSet;
        }
      }
      else {
        asort($params);
        $this->paramsToStrings($params);
        $body['query'][] = $params;
      }
      if (!empty($sort)) {
        sort($sort);
        foreach ($sort AS $sParams) {
          if (!empty($sParams['field']) && !empty($sParams['flag'])) {
            $body['sort'][] = [
              'fieldName' => $sParams['field'],
              'sortOrder' => $sParams['flag'],
            ];
          }
        }
      }
      if (!empty($range)) {
        if (empty($range[1])) {
          $range[1] = 50;
        }
        list($start, $number) = $range;
        if (!empty($start)) {
          $body['offset'] = "$start";
        }
        $body['range'] = "$number";
      }
      $result = $obj->find($layout, $body);
      $end = microtime(TRUE);
      //debug($layout . ' Total time: ' . ($end - $start) . ' seconds, connect time: ' . ($midpoint - $start) . ' seconds');
      if (!$result) {
        return FALSE;
      }
      else {
        return $single ? reset($result) : $result;
      }
    }
    else {
      return FALSE; //Replace with thrown error
    }
  }

  public function searchById($dbName, $layout, $id, $single = TRUE, $field = 'memNum', $sort = []) {
    return $this->search($dbName, $layout, [$field => $id], $single, $sort);
  }

  public function getRecordById($dbName, $layout, $recordId) {
    if ($recordId <= 0) {
      return FALSE;
    }
    $start = microtime(TRUE);
    $obj = $this->connect($dbName);
    $midpoint = microtime(TRUE);
    if ($obj) {
      $return = $obj->getRecord($layout, $recordId);
      $end = microtime(TRUE);
      //debug('Total time: ' . ($end - $start) . ' seconds, connect time: ' . ($midpoint - $start) . ' seconds');
      return $return;
    }
    return FALSE;
  }

  public function createRecord($dbName, $layout) {
    $obj = $this->connect($dbName);
    return $obj->createRecord($layout);
  }

  public function loadCompoundFindRecords($dbName, $layout) {
    $start = microtime(TRUE);
    $obj = $this->connect($dbName);
    $midpoint = microtime(TRUE);
    $args = func_get_args();
    $count = func_num_args();
    $sort = [];
    $settings = [];
    $params = [];
    $range = [];
    $single = FALSE;
    //$returnResultSet = false;
    for ($i = 2; $i < $count; $i++) {
      $arg = $args[$i];
      if (!empty($arg['sort'])) {
        //This is the sort parameters
        $sort = $arg['sortParams'];
      }
      else {
        if (!empty($arg['setting'])) {
          //This is the settings
          $settings = $arg;
        }
        else {
          $params[] = $arg;
        }
      }
    }
    if (!empty($settings)) {
      if (!empty($settings['range'])) {
        $range = $settings['range'];
      }
      if (!empty($settings['single'])) {
        $single = TRUE;
      }
    }
    if (!empty($params)) {
      return $this->search($dbName, $layout, $params, $single, $sort, $range);
    }
    return FALSE;
  }

  public function getReservedKeys() {
    return [
      'isCompound',
      'dbname',
      'dblayout',
      'sort',
      'settings',
    ];
  }

  public function paramsToStrings(&$paramSet) {
    foreach ($paramSet AS $key => $value) {
      if ($key == 'omit') {
        $paramSet[$key] = "true";
      }
      else {
        if (!is_string($value)) {
          $paramSet[$key] = (string) $value;
        }
      }
    }
  }

  public function getconnect($dbName) {
    return $this->connect($dbName);
  }
}
