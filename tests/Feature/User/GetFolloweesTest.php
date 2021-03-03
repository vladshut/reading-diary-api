<?php

namespace Tests\Feature;

use Illuminate\Support\Arr;
use Tests\TestCase;

/**
 *
 * Class FollowUserTest
 * @package Tests\Feature
 */
class GetFolloweesTest extends TestCase
{
    public function testGetFolloweesEndpoint(): void
    {
        $user = $this->login();
        $followee = $this->createUser();
        $user->follow($followee);


        $responseData = $this->jsonApiGet("users/{$user->id}/followees");

        $expectedData = $this->userToResource($followee);

       self::assertCount(1, $responseData['data']);
       self::assertArrayHasArrayWithSubset($responseData['data'], $expectedData);
    }
}
