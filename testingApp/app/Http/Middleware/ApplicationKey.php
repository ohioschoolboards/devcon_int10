<?php

namespace App\Http\Middleware;

use App\Services\OFm;
use Closure;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Log;

class ApplicationKey {

  protected $apiToken;

  public function __construct(OFm $fmService) {
    $this->fmService = $fmService;
    $settings = $this->fmService->search('O2', 'settings', ['id' => '*'], TRUE);
    if ($settings) {
      $this->apiToken = $settings->getField('currentApplicationKey');
    }
  }

  /**
   * Handle an incoming request.
   *
   * @param  \Illuminate\Http\Request $request
   * @param  \Closure $next
   *
   * @return mixed
   */
  public function handle($request, Closure $next) {
    if (!empty($this->apiToken) && $request->header('fmApplicationKey') == $this->apiToken) {
      return $next($request);
    }

    return abort(403, 'Access Denied');
  }
}
