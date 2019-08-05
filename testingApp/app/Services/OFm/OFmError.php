<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace App\Services\OFm;

class OFmError extends \FileMaker_Error {
  
  /**
   * {@inheritdoc}
   */
  public function __construct($message = null, $code = null) {
    parent::PEAR_Error($message, $code);
  }
  
  /**
   * {@inheritdoc}
   */
  public function getErrorString() {
    
    static $strings = [];
    if(empty($strings)) {
      $reflection = new ReflectionClass('FileMaker_Error');
      $location = $reflection->getFileName();
      $path = str_replace('Error.php', 'Error/en.php', $location);
      include_once($path);
      $strings = $__FM_ERRORS;
    }
    
    if(!empty($strings[$this->getCode()])) {
      return $strings[$this->getCode()];
    }
    return $strings[-1];
  }
}
