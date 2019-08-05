<?php

namespace App\Services\OFm;

use App\Services\OFm\OFm;

/**
 * Record object to allow interaction with data retrieved from FileMaker's Data API
 */
class OFmRecord implements OFmRecordInterface {
  
  protected $fields = [];
  protected $original = [];
  protected $portals = [];
  protected $originalPortals = [];
  protected $dataApi;
  protected $layout;
  protected $recordId;
  protected $databaseName;
  
  /**
   * Creates an OFmRecord object
   * 
   * @param string $databaseName
   * @param string $layout
   * @param OFm $fmService
   * @param int $recordId
   * @param array $values
   */
  public function __construct($databaseName, $layout, DataApi $dataApi, $recordId = 0, array $values = [], $portalData = null) {
    $this->databaseName = $databaseName;
    $this->layout = $layout;
    $this->dataApi = $dataApi;
    $this->recordId = $recordId;
    if(!empty($values)) {
      $this->fields = $this->original = $values;
    }
    if(!empty($portalData)) {
      //Do something with it
    }
  }
  
  /**
   * {@inheritdoc}
   */
  public function getField($fieldName) {
    return !empty($this->fields[$fieldName]) ? $this->fields[$fieldName] : '';
  }

  /**
   * {@inheritdoc}
   */
  public function getFieldUnencoded($fieldName) {
    return $this->getField($fieldName);
  }

  /**
   * {@inheritdoc}
   */
  public function getFields() {
    return array_keys($this->fields);
  }

  /**
   * {@inheritdoc}
   */
  public function getRecordId() {
    return $this->recordId;
  }

  /**
   * {@inheritdoc}
   */
  public function commit() {
    return $this->dataApi->editRecord($this->layout, $this->fields, $this->recordId, $this->original);
  }

  /**
   * {@inheritdoc}
   */
  public function setField($fieldName, $value) {
    $this->fields[$fieldName] = $value;
  }
  
  /**
   * {@inheritdoc}
   */
  public function getLayout() {
    return $this->layout;
  }

}
