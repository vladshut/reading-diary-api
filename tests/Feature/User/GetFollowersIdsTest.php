<?php

namespace Tests\Feature;

use Tests\TestCase;

/**
 *
 * Class FollowUserTest
 * @package Tests\Feature
 */
class GetFollowersIdsTest extends TestCase
{
    public function testGetFollowersIdsEndpoint(): void
    {
        $user = $this->login();
        $follower = $this->createUser();
        $follower->follow($user);


        $responseData = $this->jsonApiGet("users/{$user->id}/followers-ids");

        self::assertEquals([$follower->id], $responseData);
    }
}
