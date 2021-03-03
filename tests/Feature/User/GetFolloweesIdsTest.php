<?php

namespace Tests\Feature;

use Tests\TestCase;

/**
 *
 * Class FollowUserTest
 * @package Tests\Feature
 */
class GetFolloweesIdsTest extends TestCase
{
    public function testGetFolloweesIdsEndpoint(): void
    {
        $user = $this->login();
        $followee = $this->createUser();
        $user->follow($followee);


        $responseData = $this->jsonApiGet("users/{$user->id}/followees-ids");

        self::assertEquals([$followee->id], $responseData);
    }
}
