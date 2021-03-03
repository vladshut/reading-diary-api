<?php

namespace Tests\Feature;

use Tests\TestCase;

/**
 * Follow test. User is not followed yet. Positive result, DB record exists.
 * Follow test. User is already followed. Positive result, DB record exists.
 * Unfollow test. User is already followed. Positive result, DB record does not exists.
 * Unfollow test. User is not followed yet. Positive result, DB record does not exists.
 *
 * Class FollowUserTest
 * @package Tests\Feature
 */
class FollowUserTest extends TestCase
{
    public function testFollowEndpoint(): void
    {
        $followerId = $this->login()->id;
        $followeeId = $this->createUser()->id;

        $this->jsonApiPost("users/{$followeeId}/follow");
        $this->assertThatUserIsFollowedByUser($followerId, $followeeId);

        $this->jsonApiPost("users/{$followeeId}/follow");
        $this->assertThatUserIsFollowedByUser($followerId, $followeeId);
    }

    private function assertThatUserIsFollowedByUser(string $followerId, string $followeeId): void
    {
        $criteria = ['follower_id' => $followerId, 'followee_id' => $followeeId];
        $this->assertDatabaseHas('follows', $criteria);
        $this->assertDatabaseCount('follows', 1);
    }

    public function testUnfollowEndpoint(): void
    {
        $follower = $this->login();
        $followerId = $follower->id;
        $followee = $this->createUser();
        $followeeId = $followee->id;

        $follower->follow($followee);

        $this->assertThatUserIsFollowedByUser($followerId, $followeeId);

        $this->jsonApiPost("users/{$followeeId}/unfollow");
        $this->assertThatUserIsNotFollowedByUser($followerId, $followeeId);

        $this->jsonApiPost("users/{$followeeId}/unfollow");
        $this->assertThatUserIsNotFollowedByUser($followerId, $followeeId);
    }

    private function assertThatUserIsNotFollowedByUser(string $followerId, string $followeeId): void
    {
        $criteria = ['follower_id' => $followerId, 'followee_id' => $followeeId];
        $this->assertDatabaseMissing('follows', $criteria);
        $this->assertDatabaseCount('follows', 0);
    }
}
