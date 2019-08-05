<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use App\Services\OFm;

class OFmServiceProvider extends ServiceProvider {

    /**
     * Bootstrap services.
     *
     * @return void
     */
    public function boot() {

    }

    /**
     * Register services.
     *
     * @return void
     */
    public function register() {
        $this->app->bind(OFm::class, function () {
            return new OFm();
        });
    }

    public function provides() {
        return [OFm::class];
    }
}
