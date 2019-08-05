<?php

namespace App\Http\Controllers;

use App\Services\OFm;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class TestController extends Controller {

  const loopCount = 100;

  const iterationIncrement = 1;

  protected $apiToken;

  /**
   * TestController constructor.
   *
   * @param \App\Services\OFm $fmService
   */
  public function __construct(OFm $fmService) {
    $this->fmService = $fmService;
  }

  protected function getAllTests() {
    return [
      'warmup' => [
        'iterationCount' => 1,
        'description' => 'Warmup, caches data',
      ],
      'contactDetailsWebPortalFiltered' => [
        'description' => 'Load from web layout with filtered email address portal'
      ],
      'contactDetailsWebPortalFilteredTwo' => [
        'description' => 'Load from web layout with filtered email address and phone number portal'
      ],
      'contactDetails' => [
        'description' => 'Load from Contact Details layout'
      ],
      'contactDetailsWithUnstoredCalcs' => [
        'description' => 'Load from Contact Details layout with one unstored calc'
      ],
      'contactDetailsWithTwoUnstoredCalcs' => [
        'description' => 'Load from Contact Details layout with two unstored calcs'
      ],
      'contactDetailsWeb' => [
        'description' => 'Load from web layout'
      ],
      'contactDetailsWebNoPortals' => [
        'description' => 'Load from web layout with no portals'
      ],
      'contactDetailsUnstoredTwoHop' => [
        'description' => 'Load contacts details, with one unstored calculation two hops away in the relationship graph',
      ],
    ];
  }

  public function runAllTests() {
    $return = [];

    foreach($this->getAllTests() AS $testName => $details) {
      $return[] = $this->testRunner($testName, $details);
    }

    return view('results')->with('results', $return);
  }

  public function runAllTestsShuffle() {
    $return = [];

    $tests = $this->getAllTests();

    //Run the warmup separately
    $warmupDetails = $tests['warmup'];
    $return[] = $this->testRunner('warmup', $warmupDetails);
    unset($tests['warmup']);

    //Now we shuffle before running each test iteration increment per loop
    //for($runCount = 0; $runCount < self::loopCount ; $runCount + self::iterationIncrement) {
    $runCount = 0;
    $loopCount = self::loopCount;
    while($runCount < $loopCount) {
      //Shuffle the tests
      $iterationTests = $this->shuffleTests($tests);
      foreach($iterationTests AS $testName => $testDetails) {
        $testDetails['iterationCount'] = self::iterationIncrement;
        $results = $this->testRunner($testName, $testDetails);
        if(!empty($return[$testName])) {
          //Only update the time and iterations if we're past the first iteration
          $return[$testName]['time'] += $results['time'];
          $return[$testName]['iterations'] += $results['iterations'];
        }
        else {
          $return[$testName] = $results;
        }
      }
      $runCount += self::iterationIncrement;
    }

    //Now, let's sort the data a bit
    uasort($return, function($a, $b) {
      return $a['time'] < $b['time'] ? -1 : 1;
    });

    return view('results')->with('results', $return);
  }

  protected function shuffleTests(array $tests) {
    $new = [];

    $keys = array_keys($tests);
    shuffle($keys);
    foreach($keys AS $key) {
      $new[$key] = $tests[$key];
    }

    return $new;
  }

  public function testRunner($testName, array $details = []) {
    $return = [
      'testName' => $this->nameToReadable(ucfirst($testName)),
    ];
    if(!empty($details['description'])) {
      $return['description'] = $details['description'];
    }

    if(method_exists($this, $testName)) {
      $iterationCount = !empty($details['iterationCount']) ? $details['iterationCount'] : self::loopCount;
      $return['iterations'] = $iterationCount;
      $start = microtime(true);
      for($i = 0 ; $i < $iterationCount ; $i++) {
        call_user_func([$this, $testName]);
      }
      $end = microtime(true);

      $return['time'] = ($end - $start);
    }
    else {
      $return['error'] = 'Method not found';
    }

    return $return;
  }

  protected function nameToReadable($name) {
    $array = preg_split('/(?=[A-Z])/', $name);

    return implode(' ', $array);
  }

  public function warmup() {
    $this->contactDetails();
  }

  public function contactDetails() {
    $test = $this->fmService->search('demo', 'Contact Details', ['First Name' => '*']);
  }

  public function contactDetailsWithUnstoredCalcs() {
    $test = $this->fmService->search('demo', 'Contact Details Unstored', ['First Name' => '*']);
  }

  public function contactDetailsUnstoredTwoHop() {
    $test = $this->fmService->search('demo', 'Contact Details Unstored Two Hop', ['First Name' => '*']);
  }

  public function contactDetailsWithTwoUnstoredCalcs() {
    $test = $this->fmService->search('demo', 'Contact Details Unstored Two', ['First Name' => '*']);
  }

  public function contactDetailsWeb() {
    $test = $this->fmService->search('demo', 'webContactDetails', ['First Name' => '*']);
  }

  public function contactDetailsWebNoPortals() {
    $test = $this->fmService->search('demo', 'webContactDetailsNoPortals', ['First Name' => '*']);
  }

  public function contactDetailsWebPortalFiltered() {
    $test = $this->fmService->search('demo', 'webContactDetailsPortalFiltered', ['First Name' => '*']);
  }

  public function contactDetailsWebPortalFilteredTwo() {
    $test = $this->fmService->search('demo', 'webContactDetailsPortalFilteredTwo', ['First Name' => '*']);
  }
}
