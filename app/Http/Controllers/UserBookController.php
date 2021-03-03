<?php

namespace App\Http\Controllers;

use App\Book;
use App\Http\Resources\UserBookResource;
use App\User;
use App\UserBook;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;

class UserBookController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     */
    public function myBooks(): AnonymousResourceCollection
    {
        return UserBookResource::collection($this->getUser()->books()->paginate());
    }

    /**
     * Display a listing of the resource.
     * @param UserBook $userBook
     * @return UserBookResource
     */
    public function show(UserBook $userBook): UserBookResource
    {
        if ($this->getUser()->id !== $userBook->user()->first('id')['id']) {
            throw new BadRequestHttpException();
        }

        return new UserBookResource($userBook);
    }

    /**
     * @param Request $request
     * @return UserBookResource
     * @throws ValidationException
     */
    public function addNew(Request $request): UserBookResource
    {
        $this->validate($request, [
            'title' => 'required',
            'year' => 'required',
            'pages' => 'required',
            'lang' => 'required',
            'description' => '',
            'author_id' => 'required',
        ]);

        /** @var Book $book */
        $book = Book::create($request->all());

        $cover = $request->file('cover');

        if ($cover) {
            $book->storeCover($cover);
        }

        $userBook = $this->getUser()->addBook($book);

        return new UserBookResource($userBook);
    }

    /**
     * @param Request $request
     * @return UserBookResource
     * @throws ValidationException
     */
    public function addExisting(Request $request): UserBookResource
    {
        $this->validate($request, ['book_id' => 'required']);

        $book = Book::find($request->get('book_id'));

        $userBook = $this->getUser()->addBook($book);

        return new UserBookResource($userBook);
    }

    public function startReading(UserBook $userBook): UserBookResource
    {
        if ($this->getUser()->id === $userBook->user()->first('id')['id']) {
            $userBook->startReading();
        }

        return new UserBookResource($userBook);
    }


    public function finishReading(UserBook $userBook): UserBookResource
    {
        if ($this->getUser()->id === $userBook->user()->first('id')['id']) {
            $userBook->finishReading();
        }

        return new UserBookResource($userBook);
    }

    public function resumeReading(UserBook $userBook): UserBookResource
    {
        if ($this->getUser()->id === $userBook->user()->first('id')['id']) {
            $userBook->resumeReading();
        }

        return new UserBookResource($userBook);
    }

    /**
     * @param UserBook $userBook
     * @return UserBookResource
     * @throws Exception
     */
    public function makePublic(UserBook $userBook): UserBookResource
    {
        if ($this->getUser()->id === $userBook->user()->first('id')['id']) {
            $userBook->makeReportAccessibleViaPublicLink();
        }

        return new UserBookResource($userBook);
    }

    /**
     * @param UserBook $userBook
     * @return UserBookResource
     * @throws Exception
     */
    public function makePrivate(UserBook $userBook): UserBookResource
    {
        if ($this->getUser()->id === $userBook->user()->first('id')['id']) {
            $userBook->makeReportNotAccessibleViaPublicLink();
        }

        return new UserBookResource($userBook);
    }

    /**
     * @param User $user
     * @return array
     */
    public function topLanguages(User $user): array
    {
        return UserBook::query()
            ->select('lang', DB::raw('count(*) as count'))
            ->where('user_id', '=', $user->id)
            ->join('books', 'books.id', '=', 'book_user.book_id')
            ->groupBy(['lang'])
            ->orderByDesc('count')
            ->get()
            ->toArray();
    }

    /**
     * @param User $user
     * @return array
     */
    public function topAuthors(User $user): array
    {
        return UserBook::query()
            ->select('authors.name', DB::raw('count(*) as count'))
            ->where('user_id', '=', $user->id)
            ->join('books', 'books.id', '=', 'book_user.book_id')
            ->join('authors', 'authors.id', '=', 'books.author_id')
            ->groupBy(['authors.id'])
            ->orderByDesc('count')
            ->get()
            ->toArray();
    }

    /**
     * @param User $user
     * @return array
     */
    public function topStatuses(User $user): array
    {
        return UserBook::query()
            ->select('status', DB::raw('count(*) as count'))
            ->where('user_id', '=', $user->id)
            ->join('books', 'books.id', '=', 'book_user.book_id')
            ->groupBy(['status'])
            ->orderByDesc('count')
            ->get()
            ->toArray();
    }
}
