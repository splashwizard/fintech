<?php

namespace Tests;
use Illuminate\Support\Facades\Schema;

use Illuminate\Foundation\Testing\TestCase as BaseTestCase;

abstract class TestCase extends BaseTestCase
{
    use CreatesApplication;

    protected $api_url = '/api/v1';

    public function setUp(): void
    {
          parent::setUp();
          Schema::defaultStringLength(191);
          $this->artisan('migrate:fresh');
         // $this->seed();

    }
}
