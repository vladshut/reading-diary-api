<?php

namespace App;

use Jenssegers\Mongodb\Eloquent\Model;
use Webmozart\Assert\Assert;

/**
 * @property mixed book_section_id
 * @property mixed type
 */
class ReportItem extends Model
{
    public const TYPE_TERM = 'term';
    public const TYPE_GOAL = 'goal';
    public const TYPE_QUOTE = 'quote';
    public const TYPE_QUESTION = 'question';
    public const TYPE_RESUME = 'resume';
    public const TYPE_REFERENCE = 'reference';
    public const TYPE_INFORMATION_EVALUATION = 'information_evaluation';
    public const TYPE_REVIEW = 'review';
    public const TYPE_RATING = 'rating';
    public const TYPE_FORWARD_RESEARCH = 'forward_research';

    public const TYPES = [
        self::TYPE_TERM,
        self::TYPE_GOAL,
        self::TYPE_QUOTE,
        self::TYPE_QUESTION,
        self::TYPE_RESUME,
        self::TYPE_REFERENCE,
        self::TYPE_INFORMATION_EVALUATION,
        self::TYPE_REVIEW,
        self::TYPE_RATING,
        self::TYPE_FORWARD_RESEARCH,
    ];
    private const SHARED_FIELDS = ['type', 'book_section_id', 'book_user_id'];

    protected $connection = 'mongodb';

    public function fill(array $attributes): self
    {
        if ($this->type) {
            foreach (self::SHARED_FIELDS as $field) {
                unset($attributes[$field]);
            }
            $this->fillable = self::fieldsTypeMap($this->type, false);
        } else if (isset($attributes['type'])) {
            Assert::oneOf($attributes['type'], self::TYPES);

            $this->fillable = self::fieldsTypeMap($attributes['type']);

            foreach ($this->fillable as $key) {
                Assert::keyExists($attributes, $key);
            }
        }

        /** @var self $self */
        $self = parent::fill($attributes);

        return $self;
    }

    public static function fieldsTypeMap(string $type, bool $withSharedFields = true): array
    {
        Assert::oneOf($type, self::TYPES);

        $map = [
            self::TYPE_TERM => ['definition'],
            self::TYPE_GOAL => ['result', 'is_reached'],
            self::TYPE_QUOTE => ['note'],
            self::TYPE_QUESTION => [],
            self::TYPE_RESUME => [],
            self::TYPE_REFERENCE => [],
            self::TYPE_INFORMATION_EVALUATION => [],
            self::TYPE_REVIEW => [],
            self::TYPE_RATING => [],
            self::TYPE_FORWARD_RESEARCH => [],
        ];

        $fields = [$type];

        foreach ($map[$type] as $field) {
            $fields[] = "{$type}_{$field}";
        }

        return $withSharedFields ? array_merge(self::SHARED_FIELDS, $fields) : $fields;
    }
}
