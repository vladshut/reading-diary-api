<?php

namespace Tests;

use App\User;
use Exception;
use Faker\Factory;
use Faker\Generator;
use Illuminate\Contracts\Console\Kernel;
use Illuminate\Contracts\Debug\ExceptionHandler;
use Illuminate\Foundation\Exceptions\Handler;
use Illuminate\Foundation\Testing\Assert as PHPUnit;
use Illuminate\Foundation\Testing\Concerns\InteractsWithExceptionHandling;
use Illuminate\Foundation\Testing\Constraints\HasInDatabase;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Illuminate\Foundation\Testing\RefreshDatabaseState;
use Illuminate\Foundation\Testing\TestCase as BaseTestCase;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Artisan;
use Tymon\JWTAuth\Facades\JWTAuth;

abstract class TestCase extends BaseTestCase
{
    use CreatesApplication, DatabaseMigrations;

    protected $faker;


    protected function setUp(): void
    {
        /**
         * This disables the exception handling to display the stacktrace on the console
         * the same way as it shown on the browser
         */
        parent::setUp();
        Artisan::call('db:seed');
        $this->faker = Factory::create();

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
            self::assertJson($responseContent, 'API response is not valid json. ' . $responseContent);
        }
        self::assertContains($responseStatus, [200, 201, 204], 'API json status is ' . $responseStatus . '. Content is ' . $responseContent);

        return json_decode($responseContent, true, 512, JSON_THROW_ON_ERROR);
    }

    protected function jsonApiPut($uri, array $data = [], array $headers = [], array $responseValidationRules = null): array
    {
        return $this->jsonApi('put', $uri, $data, $headers, $responseValidationRules);
    }

    protected function jsonApiPost($uri, array $data = [], array $headers = [], array $responseValidationRules = null): array
    {
        return $this->jsonApi('post', $uri, $data, $headers, $responseValidationRules);
    }

    protected function jsonApiGet($uri, array $data = [], array $headers = [], array $responseValidationRules = null): array
    {
        return $this->jsonApi('get', $uri, $data, $headers, $responseValidationRules);
    }

    protected function jsonApiDelete($uri, array $data = [], array $headers = [], array $responseValidationRules = null): array
    {
        return $this->jsonApi('delete', $uri, $data, $headers, $responseValidationRules);
    }


    protected function assertValidationErrors($method, $uri, array $data = [], array $headers = [], $expectedValidationErrors = null): array
    {
        $response = $this->json($method, "api/{$uri}", $data, $headers);
        $responseContent = $response->getContent();
        $responseStatus = $response->getStatusCode();

        self::assertJson($responseContent, 'API response is not valid json. ' . $responseContent);

        $expectedValidationErrorsStr = $expectedValidationErrors ? json_encode($expectedValidationErrors) : '';
        $assertMessage = "API json status: '{$responseStatus}'. Content: '{$responseContent}'. Expected errors: '{$expectedValidationErrorsStr}'.";
        self::assertEquals(422, $responseStatus, $assertMessage);

        $responseData =  json_decode($responseContent, true, 512);

        if ($expectedValidationErrors) {
            $expectedErrorKey = is_array($expectedValidationErrors) ? 'errors' : 'error';
            $expectedErrors = [$expectedErrorKey => $expectedValidationErrors];

            $info = PHP_EOL;
            $info .= 'EXPECTED: ' . json_encode($expectedErrors) . PHP_EOL;
            $info .= '  ACTUAL: ' . json_encode($responseData) . PHP_EOL;

            self::assertArrayHasKey($expectedErrorKey, $responseData);

            if ($expectedErrorKey === 'errors') {
                self::assertIsArray($responseData[$expectedErrorKey]);
                self::assertCount(
                    0,
                    array_diff_key($expectedValidationErrors, $responseData[$expectedErrorKey]),
                    $info
                );

                foreach ($expectedValidationErrors as $fieldName => $expectedFieldValidationErrors) {
                    self::assertCount(
                        0,
                        array_diff($expectedFieldValidationErrors, $responseData[$expectedErrorKey][$fieldName]),
                        $info
                    );
                }
            } else {
                self::assertEquals($expectedValidationErrors, $responseData[$expectedErrorKey]);
            }
        }

        return $responseData;
    }

    protected function assertValidationErrorsGet($uri, array $data = [], array $headers = [], $expectedValidationErrors = null): array
    {
        return static::assertValidationErrors('get', $uri, $data, $headers, $expectedValidationErrors);
    }

    protected function assertValidationErrorsPost($uri, array $data = [], array $headers = [], $expectedValidationErrors = null): array
    {
        return static::assertValidationErrors('post', $uri, $data, $headers, $expectedValidationErrors);
    }

    protected function assertValidationErrorsPut($uri, array $data = [], array $headers = [], $expectedValidationErrors = null): array
    {
        return static::assertValidationErrors('put', $uri, $data, $headers, $expectedValidationErrors);
    }

    protected function assertValidationErrorsDelete($uri, array $data = [], array $headers = [], $expectedValidationErrors = null): array
    {
        return static::assertValidationErrors('delete', $uri, $data, $headers, $expectedValidationErrors);
    }

    protected function assertDataIsValid(array $data, array $validationRules, string $message = null): void
    {
        /** @var \Illuminate\Validation\Factory $validatorFactory */
        $validatorFactory = $this->app->get('validator');
        $validator = $validatorFactory->make($data, $validationRules);

        $info = PHP_EOL;
        $info .= 'ERRORS: ' . $validator->errors()->toJson() . PHP_EOL;
        $info .= ' RULES: ' . json_encode($validationRules) . PHP_EOL;
        $info .= '  DATA: ' . json_encode($data) . PHP_EOL;

        static::assertTrue($validator->passes(), 'Data is not valid.' . $info);
    }

    protected function assertValuePublishedWithKey($expectedValue, string $key): void
    {
        static::assertTrue($this->pubSub->hasPublishedValue($key), "There are no values published with key '$key'.");
        static::assertEquals($expectedValue, $this->pubSub->getPublishedValue($key), "Value was not be published with key '$key'.");
    }

    protected function assertKeyHasNotPublishedValues(string $key): void
    {
        static::assertFalse($this->pubSub->hasPublishedValue($key), "Key '$key' has published values.");
    }

    protected function assertJsonApiNotFound($method, $uri, array $data = [], array $headers = []): void
    {
        $response = $this->json($method, $uri, $data, $headers);
        $responseContent = $response->getContent();
        $responseStatus = $response->getStatusCode();

        self::assertContains($responseStatus, [404], 'API json status is ' . $responseStatus . '. Content is ' . $responseContent);
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
                    static::assertStructure($item, $structure['*']);
                }
            } elseif (is_array($value)) {
                PHPUnit::assertArrayHasKey($key, $array, 'Asserting that array ' . json_encode($array) . ' has structure ' . json_encode($structure) . '. Array has not a key ' . $key);
                static::assertStructure($array[$key], $structure[$key]);
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

    protected function createImage(string $path): void
    {
        $filePath = __DIR__ . '/example.jpg';
        shell_exec("rm $path 2>&1");
        shell_exec("cp $filePath $path 2>&1");
    }

    private function getInvalidDataSet(array $validData, string $key, $value, $errorMessage): array
    {
        $data = $validData;
        Arr::set($data, $key, $value);

        if (is_array_assoc($errorMessage)) {
            $errors = $errorMessage;
        } else {
            $errors = [$key => is_string($errorMessage) ? [$errorMessage] : $errorMessage];
        }

        return [$data, $errors];
    }

    protected function transformValidationRulesToInvalidDataSets(array $validData, array $rules, string $keyPrefix = ''): array
    {
        $result = [];

        foreach ($rules as $key => $keyRules) {
            foreach ($keyRules as $val => $err) {
                $data = $validData;

                if (isset($err['extra_data'])) {
                    foreach ($err['extra_data'] as $extraKey => $extraValue) {
                        Arr::set($data, $extraKey, $extraValue);
                    }
                }

                if (is_array($err) && array_key_exists('val', $err)) {
                    $val = $err['val'];
                }

                $err = $err['err'] ?? $err;

                $result[] = $this->getInvalidDataSet($data, $keyPrefix . $key, $val, $err);
            }
        }

        return $result;
    }
}
