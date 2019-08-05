<?php
/**
 * Created by PhpStorm.
 * User: cgrewe
 * Date: 4/27/2019
 * Time: 2:02 PM
 */

namespace App\Services\OFm;

use Illuminate\Support\Facades\Log;

trait OFmTrait {

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


  public function logError($results, $dbName, $layout, $params) {
    Log::debug(print_r([
      //'errorCode' => $results->code,
      'msg' => $results->getMessage(),
      'name' => $dbName,
      'layout' => $layout,
      'params' => $params,
    ], TRUE));
  }

}