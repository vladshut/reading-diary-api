<?php

namespace App\Services;

use DateTimeInterface;
use Exception;
use Illuminate\Support\Facades\Storage;
use Spatie\MediaLibrary\UrlGenerator\BaseUrlGenerator;

class PrivateUrlGenerator extends BaseUrlGenerator
{

    public function getUrl(): string
    {
        $media = $this->media;

        $modelType = last(explode('\\', $media->model_type));
        $modelId = $media->model_id;
        $mediaName = $media->name;
        $mediaId = $media->id;
        $fileName = $media->file_name;

        return "/api/files/$modelType/$modelId/$mediaName/$mediaId/$fileName";
    }

    /**
     * @param DateTimeInterface $expiration
     * @param array $options
     * @return string
     * @throws Exception
     */
    public function getTemporaryUrl(DateTimeInterface $expiration, array $options = []): string
    {
        throw new Exception("Temp url is not supported.");
    }

    /**
     * @return string
     * @throws Exception
     */
    public function getResponsiveImagesDirectoryUrl(): string
    {
        throw new Exception("No responsive directory url for private files.");
    }

    /**
     * @return string
     */
    public function getPath(): string
    {
        return Storage::disk($this->media->disk)->path('') . $this->getPathRelativeToRoot();
    }

}
