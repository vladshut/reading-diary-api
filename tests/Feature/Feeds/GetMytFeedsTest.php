<?php
declare(strict_types=1);


namespace Tests\Feature\Feeds;


use App\Models\Feed;
use Tests\TestCase;

final class GetMytFeedsTest extends TestCase
{
    public function testMy(): void
    {
        // create user followee
        // create another user
        // create user follower
        // follower follow followee
        // creates 5 feeds for followee
        // creates 1 feed for another user
        // login as follower
        // make request to get feeds: 3 per page
        // check that there are 3 feeds in the response
        // check that there are 2 page in the response
        // check that total count equals to 5
        // check that 3 feeds in the response are the latest feeds from followee

        $followee = $this->createUser();
        $anotherUser = $this->createUser();
        $follower = $this->createUser();
        $follower->follow($followee);

        $followeeFeedsCount = 5;
        $followeeFeeds = factory(Feed::class)->times($followeeFeedsCount)->create(['author_id' => $followee->id])->all();

        $anotherUserFeedsCount = 1;
        factory(Feed::class)->times($anotherUserFeedsCount)->create(['author_id' => $anotherUser->id]);

        $this->login($follower);

        $perPage = 3;
        $totalPageCount = (int)ceil($followeeFeedsCount / $perPage);
        $responseData = $this->jsonApiGet("feeds/my?per_page={$perPage}");

        self::assertCount($perPage, $responseData['data']);
        self::assertEquals($followeeFeedsCount, $responseData['meta']['total']);
        self::assertEquals($totalPageCount, $responseData['meta']['last_page']);

        self::assertModelsResourcesInArray(array_slice($followeeFeeds, 0, $perPage), $responseData['data']);
    }
}
