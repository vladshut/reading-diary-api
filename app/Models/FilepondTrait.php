<?php
declare(strict_types=1);


namespace App\Models;


use App\Exceptions\ValidationException;
use App\Services\Filepond;
use Spatie\MediaLibrary\HasMedia\HasMedia;
use Spatie\MediaLibrary\Models\Media;

trait FilepondTrait
{
    protected static $maxFileSize = [
        'image' => 5 * 1024 * 1024, // 5 Mb
        'pdf' => 10 * 1024 * 1024, // 10 Mb
        'video' => 300 * 1024 * 1024, // 300 Mb
    ];

    public static function bootFilepondTrait(): void
    {
        static::saving(static function (HasMedia $model) {

            // Get the temporary path using the serverId returned by the upload function in `FilepondController.php`
            foreach ($model->filepondFields() as $options) {
                $filepondField = $options['field'];

                $where = $options['where'] ?? null;

                if (is_callable($where) && !$where($model)) {
                    continue;
                }

                if (!$model->$filepondField) {
                    $model->deleteMediasByName($filepondField);

                    continue;
                }

                /** @var Filepond $filepond */
                $filepond = app(Filepond::class);
                $path = $filepond->findPathFromServerId($model->$filepondField);

                if (!$path) {
                    return;
                }

                $validationField = $options['validationField'] ?? $filepondField;

                $fileType = get_file_type($path);

                $allowedType = $options['type'] ?? null;

                if ($allowedType && $fileType !== $allowedType) {
                    throw new ValidationException([$validationField => trans('validation.invalid_file_type')]);
                }

                $fileSize = filesize($path);
                $maxFileSize = $options['maxFileSize'] ?? (self::$maxFileSize[$allowedType] ?? null);

                if ($maxFileSize && $fileSize > $maxFileSize) {
                    throw new ValidationException([$validationField => trans('validation.max_file_size')]);
                }

                $model->deleteMediasByName($filepondField);

                $media = $model
                    ->addMedia($path) //starting method
                    ->usingName($filepondField)
                    ->toMediaCollection(); //finishing method

                $model->$filepondField = '';
            }
        });

        static::saved(static function (HasMedia $model) {
            // Get the temporary path using the serverId returned by the upload function in `FilepondController.php`
            foreach ($model->filepondFields() as $options) {

                $filepondField = $options['field'];

                if ($model->$filepondField) {
                    continue;
                }

                $where = $options['where'] ?? null;

                if (is_callable($where) && !$where($model)) {
                    continue;
                }

                $model->load('media');

                /** @var Media $media */
                $media = $model->getMedia()->where('name', $filepondField)->first();

                if (!$media) {
                    continue;
                }

                $model->$filepondField = $media->getUrl();

                $model->save();
            }
        });
    }

    protected function deleteMediasByName(string $name): void
    {
        /** @var Media[] $medias */
        $medias = $this->getMedia()->where('name', $name)->all();

        foreach ($medias as $media) {
            $media->delete();
        }
    }

    abstract public function filepondFields(): array;
}
