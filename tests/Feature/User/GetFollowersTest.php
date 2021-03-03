<?php

namespace Tests\Feature;

use Illuminate\Support\Arr;
use Tests\TestCase;

/**
 *
 * Class FollowUserTest
 * @package Tests\Feature
 */
class GetFollowersTest extends TestCase
{
    public function testGetFollowersEndpoint(): void
    {
        $this->login();
        $user = $this->createUser();
        $follower = $this->createUser();
        $follower->follow($user);

        $responseData = $this->jsonApiGet("users/{$user->id}/followers");

        $expectedData = $this->userToResource($follower);

       self::assertCount(1, $responseData['data']);
       self::assertArrayHasArrayWithSubset($responseData['data'], $expectedData);
    }
}
