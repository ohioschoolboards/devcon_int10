<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace App\Services\OFm;

/**
 *
 * @author cgrewe
 */
interface OFmRecordInterface {
  
  /**
   * Retrieves the value of a field
   * 
   * @param string $fieldName
   * @return string
   */
  public function getField($fieldName);
  
  /**
   * Intended to be used as a wrapper for getField while maintaining FileMaker_Record code
   * that used two different functions to get encoded vs. unencoded strings
   * 
   * @param string $fieldName
   * @return string
   */
  public function getFieldUnencoded($fieldName);
  
  /**
   * Returns an array of field names available to this record or false if no fields exist
   * 
   * @return array|boolean
   */
  public function getFields();
  
  /**
   * Returns the record's ID number
   * 
   * @return integer
   */
  public function getRecordId();
  
  /**
   * Sets the value of the field
   * 
   * @param string $fieldName
   * @param string $value
   */
  public function setField($fieldName, $value);
  
  /**
   * Commits the record to FileMaker
   */
  public function commit();
  
  /**
   * Returns the name of the layout from which the record originates
   */
  public function getLayout();
}
