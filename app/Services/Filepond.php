<?php

namespace App\Services;

use App\Exceptions\InvalidPathException;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Str;
use Throwable;

class Filepond
{
    /**
     * Converts the given path into a filepond server id
     *
     * @param string $path
     * @return string
     */
    public function getServerIdFromPath(string $path): string
    {
        return Crypt::encryptString($path);
    }

    /**
     * Converts the given filepond server id into a path
     *
     * @param string $serverId
     * @return string
     * @throws InvalidPathException
     */
    public function getPathFromServerId(string $serverId): string
    {
        if (!trim($serverId)) {
            throw new InvalidPathException();
        }
        $filePath = Crypt::decryptString($serverId);
        if (!Str::startsWith($filePath, config('filepond.temporary_files_path'))) {
            throw new InvalidPathException();
        }

        return $filePath;
    }

    /**
     * @param $serverId
     * @return null|string
     */
    public function findPathFromServerId($serverId): ?string
    {
        try {
            $filePath = $this->getPathFromServerId($serverId);
        } catch (Throwable $exception) {
            return null;
        }

        return $filePath;
    }
}
