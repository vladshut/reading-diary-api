<?php

namespace Tests;

use App\User;
use Faker\Factory;
use Faker\Generator;
use Illuminate\Contracts\Console\Kernel;
use Illuminate\Contracts\Debug\ExceptionHandler;
use Illuminate\Foundation\Exceptions\Handler;
use Illuminate\Foundation\Testing\Assert as PHPUnit;
use Illuminate\Foundation\Testing\Constraints\HasInDatabase;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Illuminate\Foundation\Testing\RefreshDatabaseState;
use Illuminate\Foundation\Testing\TestCase as BaseTestCase;
use Illuminate\Support\Facades\Artisan;
use Tymon\JWTAuth\Facades\JWTAuth;

abstract class TestCase extends BaseTestCase
{
    use CreatesApplication, DatabaseMigrations;

    protected $faker;

    /**
     * Define hooks to migrate the database before and after each test.
     *
     * @return void
     */
    public function runDatabaseMigrations(): void
    {
        $this->artisan('migrate:fresh');

        $this->app[Kernel::class]->setArtisan(null);

        $this->beforeApplicationDestroyed(static function () {
            RefreshDatabaseState::$migrated = true;
        });
    }

    protected function setUp(): void
    {
        /**
         * This disables the exception handling to display the stacktrace on the console
         * the same way as it shown on the browser
         */
        parent::setUp();
        $this->disableExceptionHandling();
        Artisan::call('db:seed');
        $this->faker = Factory::create();

    }

    protected function disableExceptionHandling(): void
    {
        $this->app->instance(ExceptionHandler::class, new class extends Handler {
            public function __construct() {}

            public function report(\Exception $e)
            {
                // no-op
            }

            public function render($request, \Exception $e) {
                throw $e;
            }
        });
    }

    protected function login(User $user = null): User
    {
        $user = $user ?: factory(User::class)->create();
        $this->actingAs($user, 'api');
        $this->actingAs($user, 'web');

        $token = JWTAuth::fromUser($user);

        $this->withHeader('Authorization', "Bearer $token");

        return $user;
    }

    protected function jsonApi($method, $uri, array $data = [], array $headers = []): array
    {
        $response = $this->json($method, "api/${uri}", $data, $headers);
        $responseContent = $response->getContent();
        $responseStatus = $response->getStatusCode();

        if ($responseStatus !== 204) {
            $this->assertJson($responseContent, 'API response is not valid json. ' . $responseContent);
        }
        $this->assertContains($responseStatus, [200, 201, 204], 'API json status is ' . $responseStatus . '. Content is ' . $responseContent);

        return json_decode($responseContent, true);
    }

    /**
     * Assert that the response has a given JSON structure.
     *
     * @param array $array
     * @param array|null $structure
     * @return void
     */
    public function assertStructure(array $array, array $structure): void
    {
        foreach ($structure as $key => $value) {
            if (is_array($value) && $key === '*') {
                PHPUnit::assertIsArray($array);

                foreach ($array as $item) {
                    $this->assertStructure($item, $structure['*']);
                }
            } elseif (is_array($value)) {
                PHPUnit::assertArrayHasKey($key, $array, 'Asserting that array ' . json_encode($array) . ' has structure ' . json_encode($structure) . '. Array has not a key ' . $key);
                $this->assertStructure($array[$key], $structure[$key]);
            } else {
                PHPUnit::assertArrayHasKey($value, $array, 'Asserting that array ' . json_encode($array) . ' has structure ' . json_encode($structure) . '. Array has not a key ' . $value);
            }
        }
    }

    protected function assertDatabaseHas($table, array $data, $connection = null)
    {
        if (($connection === 'mongodb') && isset($data['id'])) {
            $data['_id'] = $data['id'];
            unset($data['id']);
        }

        parent::assertDatabaseHas($table, $data, $connection);
    }

    protected function assertDatabaseMissing($table, array $data, $connection = null)
    {
        if (($connection === 'mongodb') && isset($data['id'])) {
            $data['_id'] = $data['id'];
            unset($data['id']);
        }

        parent::assertDatabaseMissing($table, $data, $connection);
    }
}
