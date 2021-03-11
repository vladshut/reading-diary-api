<?php

namespace App\Http\Controllers;

use App\Models\BookSection;
use App\Http\Resources\BookSectionResource;
use App\Models\UserBook;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Validation\ValidationException;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;

class BookSectionController extends Controller
{
    public function index(UserBook $userBook): AnonymousResourceCollection
    {
        return BookSectionResource::collection($userBook->sections()->get());
    }

    public function store(Request $request, UserBook $userBook)
    {
        $this->validate($request, [
            'name' => 'required',
            'parent_id' => 'numeric|gt:0|exists:book_sections,id',
        ]);

        $name = (string)$request->input('name');
        $parentId = (int)$request->input('parent_id', null);

        $order = (int)$userBook->sections()->where(['parent_id' => $parentId])->count() + 1;

        $section = $userBook->addSection($name, $order, $parentId);

        return new BookSectionResource($section);

    }

    /**
     * @param Request $request
     * @param BookSection $section
     * @return BookSectionResource|void
     * @throws ValidationException
     */
    public function update(Request $request, BookSection $section): BookSectionResource
    {
        if ($section->userBook()->first()->user()->first()->id === $this->getUser()->id) {
            $requestData = [
                'order' => 'required|numeric|gt:0',
                'name' => 'required',
                'parent_id' => 'numeric|gt:0',
            ];

            $this->validate($request, $requestData);

            $data = $request->only(array_keys($requestData));

            $section->update($data);
        }

        return new BookSectionResource($section);
    }

    /**
     * @param Request $request
     * @param BookSection $section
     * @return JsonResponse
     * @throws Exception
     */
    public function delete(Request $request, BookSection $section): JsonResponse
    {
        if ($section->userBook()->first()->user()->first()->id === $this->getUser()->id) {
            $section->delete();
        }

        return new JsonResponse();
    }
}
