<?php
declare(strict_types=1);


namespace App\Builders;


use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Query\JoinClause;

final class FeedBuilder extends BaseBuilder
{
    public function whereMatchQuery(string $query): Builder
    {
        return $this->whereRaw("UPPER(title) LIKE '%" . strtoupper($query) . "%'");
    }

    public function whereInFavoriteList(string $userId): Builder
    {
        return $this->join('feed_user', static function (JoinClause $join) use ($userId) {
            $join->on('feed_user.feed_id', '=', 'feeds.id');
            $join->where('feed_user.user_id', '=', $userId);
            $join->where('feed_user.is_favorite', '=', true);
        });
    }

    public function withFavoriteFlag($userId): Builder
    {
        return $this->leftJoin(
            'feed_user AS fu_ff',
            static function (JoinClause $join) use ($userId) {
                $join->on('fu_ff.feed_id', '=', 'feeds.id');
                $join->where('fu_ff.user_id', '=', (string)$userId);
            }
        )->select($this->query->columns ?? '*')
            ->addSelect('feeds.id AS id')
            ->addSelect('fu_ff.is_favorite AS is_favorite');
    }
}
