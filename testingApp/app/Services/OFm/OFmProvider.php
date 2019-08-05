<?php

/**
 * @file
 * Contains App\Services\OFm\OFmProvider
 */

namespace App\Services\OFm;

use Drupal\Core\Plugin\DefaultPluginManager;
use Drupal\Core\Extension\ModuleHandlerInterface;
use Drupal\Core\Plugin\Discovery\ContainerDerivativeDiscoveryDecorator;
use Drupal\Core\Plugin\Discovery\YamlDiscovery;
use Drupal\Core\Cache\CacheBackendInterface;

class OFmProvider extends DefaultPluginManager {
  
  /**
   * @inheritdoc
   */
  public function __construct(ModuleHandlerInterface $module_handler, CacheBackendInterface $cache_backend) {
    $this->alterInfo('o_fm_provider');
    $this->discovery = new ContainerDerivativeDiscoveryDecorator(new YamlDiscovery('o_fm.provider', $module_handler->getModuleDirectories()));
    $this->moduleHandler = $module_handler;
    $this->setCacheBackend($cache_backend, 'o_fm_providers');
  }
  
}