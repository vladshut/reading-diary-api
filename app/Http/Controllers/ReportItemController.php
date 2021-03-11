<?php

namespace App\Http\Controllers;

use App\Models\BookSection;
use App\Http\Resources\ReportItemResource;
use App\Models\ReportItem;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Http\Response;
use MongoDB\BSON\ObjectID;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;

class ReportItemController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @param BookSection $section
     * @return array|AnonymousResourceCollection
     */
    public function index(BookSection $section)
    {
        if ($section->userBook()->first()->user()->first()->id !== $this->getUser()->id) {
            return [];
        }

        return ReportItemResource::collection($section->reportItems()->get());
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param Request $request
     * @param BookSection $section
     * @return ReportItemResource|array
     */
    public function store(Request $request, BookSection $section)
    {
        if ($section->userBook()->first()->user()->first()->id !== $this->getUser()->id) {
            return [];
        }

        $data = $request->all();
        $data['book_section_id'] = $section->id;
        $data['book_user_id'] = $section->userBook()->get(['id'])->first()->id;

        $reportItem = new ReportItem($data);
        $reportItem->save();

        return new ReportItemResource($reportItem);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param Request $request
     * @param ReportItem $reportItem
     * @return ReportItemResource|Response
     */
    public function update(Request $request, ReportItem $reportItem)
    {
        $section = BookSection::findOrFail($reportItem->book_section_id);

        if ($section->userBook()->first()->user()->first()->id === $this->getUser()->id) {
            $reportItem->update($request->all());
            $reportItem->save();
        }

        return new ReportItemResource($reportItem);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param ReportItem $reportItem
     * @return JsonResponse
     * @throws Exception
     */
    public function destroy(ReportItem $reportItem): JsonResponse
    {
        $section = BookSection::findOrFail($reportItem->book_section_id);

        if ($section->userBook()->first()->user()->first()->id === $this->getUser()->id) {
            $reportItem->delete();
        }

        return new JsonResponse([]);
    }

    /**
     * @param Request $request
     * @param BookSection $section
     * @return AnonymousResourceCollection
     */
    public function saveBookSectionReport(Request $request, BookSection $section)
    {
        if (!$section->userBook()->first()->user()->first()->id === $this->getUser()->id) {
            throw new BadRequestHttpException();
        }

        $itemsToUpdate = $request->get('updatedItems', []);
        $itemsToDelete = $request->get('deletedItems', []);

        if (!is_array($itemsToUpdate) || !is_array($itemsToDelete) || (empty($itemsToUpdate) && empty($itemsToDelete))) {
            throw new BadRequestHttpException();
        }

        $userBookId = $section->userBook()->get(['id'])->first()->id;

        foreach ($itemsToUpdate as $item) {
            /** @var ReportItem $reportItem */
            $reportItem = ReportItem::query()->find($item['id']);

            if ($reportItem) {
                $reportItem->update($item);
                $reportItem->save();
            } else {
                $item['book_section_id'] = $section->id;
                $item['book_user_id'] = $userBookId;

                $reportItem = new ReportItem($item);
                $reportItem->_id = new ObjectID($item['id']);
                $reportItem->save();
            }
        }

        if (!empty($itemsToDelete)) {
            ReportItem::destroy($itemsToDelete);
        }

        return ReportItemResource::collection($section->reportItems()->get());
    }
}
