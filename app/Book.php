<?php

namespace App;

use DateTime;
use ePub\Definition\ManifestItem;
use ePub\Definition\Metadata;
use ePub\Definition\MetadataItem;
use ePub\Exception\OutOfBoundsException;
use ePub\Reader;
use Exception;
use Illuminate\Contracts\Filesystem\Filesystem;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Http\File;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

/**
 * @property mixed id
 * @property mixed author
 * @method static paginate()
 * @method static create(array $all)
 */
class Book extends Model
{
    protected $fillable = [
        'title',
        'year',
        'pages',
        'isbn10',
        'isbn13',
        'lang',
        'description',
        'author_id',
    ];

    public function author(): BelongsTo
    {
        return $this->belongsTo(Author::class);
    }

    public function genres(): BelongsToMany
    {
        return $this->belongsToMany(Genre::class);
    }

    public function storeCover(UploadedFile $cover): void {
        $this->storage()->putFileAs($this->getCoverDirectoryPath(), $cover, $this->id);
    }

    public function getCoverUrl(): string {
        return $this->storage()->exists($this->getCoverFilePath()) ? $this->storage()->url($this->getCoverFilePath()) : '';
    }

    private function getCoverDirectoryPath():string
    {
        return 'public/covers';
    }

    private function getCoverFilePath():string
    {
        return 'public/covers/' . $this->id;
    }

    private function storage(): Filesystem
    {
        return Storage::disk('public');
    }

    /**
     * Parses an EPUB file
     *
     * @param File|UploadedFile $file
     *
     * @return array
     * @throws OutOfBoundsException
     * @throws Exception
     */
    public static function parseEpub($file): array
    {
        $meta = [];
        $reader   = new Reader();
        $epub     = $reader->load($file);
        $metadata = $epub->getMetadata();

        $meta['title'] = self::fetchMetadataValue($metadata, 'title');
        $meta['author'] = self::fetchMetadataValue($metadata, 'creator');
        $meta['lang'] = self::fetchMetadataValue($metadata, 'language');

        $meta['genres'] = [];
        if ($metadata->has('subject')) {
            $subjects = $metadata->get('subject');
            foreach ($subjects as $subject) {
                $meta['genres'][] = $subject->value;
            }
        }

        $meta['description'] = self::fetchMetadataValue($metadata, 'description');

        $meta['year'] = null;
        if ($metadata->has('date')) {
            $meta['year'] = (new DateTime($metadata->getValue('date')))->format('Y');
        }

        $meta['isbn10'] = null;
        $meta['isbn13'] = null;


        if ($metadata->has('identifier')) {
            $identifiers = $metadata->get('identifier');
            foreach ($identifiers as $identifier) {
                /** @type MetadataItem $identifier */
                if (array_key_exists('scheme', $identifier->attributes)) {
                    // remove any eventual uri prefixes
                    $parts           = explode(':', $identifier->value);
                    $identifierValue = array_pop($parts);

                    if (!in_array(strlen($identifierValue), [10, 13])) {
                        continue;
                    }

                    $identifierKey = 'isbn' . strlen($identifierValue);

                    switch (strtolower((string)$identifier->attributes['scheme'][0])) {
                        case 'isbn':
                            $meta[$identifierKey] = $identifierValue;
                            break;
                        case 'mobi-asin':
                            $meta['asin'] = $identifierValue;
                            break;
                        case 'uuid':
                            $meta['id'] = $identifierValue;
                            break;
                    }
                }
            }
        }

        $meta['cover'] = null;

        if ($epub->getManifest()->has('cover')) {
            /** @type ManifestItem $cover */
            $cover         = $epub->getManifest()->get('cover');
            $meta['cover'] = $cover->getContent();
        }

        return $meta;
    }

    private static function fetchMetadataValue(Metadata $metadata, string $key)
    {
        if ($metadata->has($key)) {
            return $metadata->getValue($key);
        }

        return null;
    }
}
