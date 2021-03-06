<?php

namespace App\Http\Controllers;

use App\Services\Filepond;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller as BaseController;
use Illuminate\Support\Facades\Response;
use Spatie\Image\Image;

class FilepondController extends BaseController
{

    /**
     * @var Filepond
     */
    private $filepond;

    public function __construct(Filepond $filepond)
    {
        $this->filepond = $filepond;
    }

    /**
     * Uploads the file to the temporary directory
     * and returns an encrypted path to the file
     *
     * @param Request $request
     * @return \Illuminate\Http\Response
     */
    public function upload(Request $request)
    {
        $input = $request->allFiles();
        $file = is_array($input) ? head($input) : $input;

        if (!$file) {
            abort(400);
        }

        $tempPath = config('filepond.temporary_files_path');

        $filePath = @tempnam($tempPath, 'fp_');
        $filePath .= '_' . $file->getClientOriginalName();

        $filePathParts = pathinfo($filePath);

        if (!$file->move($filePathParts['dirname'], $filePathParts['basename'])) {
            return Response::make('Could not save file', 500, [
                'Content-Type' => 'text/plain',
            ]);
        }

        if (is_image($filePath)) {
            Image::load($filePath)
                ->width(1000)
                ->height(1000)
                ->optimize()
                ->save();
        }

        return Response::make($this->filepond->getServerIdFromPath($filePath), 200, [
            'Content-Type' => 'text/plain',
        ]);
    }

    /**
     * Takes the given encrypted filepath and deletes
     * it if it hasn't been tampered with
     *
     * @param Request $request
     * @return mixed
     */
    public function delete(Request $request)
    {
        $filePath = $this->filepond->getPathFromServerId($request->getContent());
        if (unlink($filePath)) {
            return Response::make('', 200, [
                'Content-Type' => 'text/plain',
            ]);
        }

        return Response::make('', 500, [
            'Content-Type' => 'text/plain',
        ]);
    }

    public function load(Request $request)
    {

    }
}
