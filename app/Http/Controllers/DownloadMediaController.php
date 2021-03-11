<?php
declare(strict_types=1);

namespace App\Http\Controllers;

use Spatie\MediaLibrary\Models\Media;

class DownloadMediaController extends Controller
{
    public function download(string $model, string $modelId, string $mediaName, string $mediaId)
    {
        return Media::query()->findOrFail($mediaId);
    }
}
