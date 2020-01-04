@extends('layouts.app')

@section('content')

                <div class="card">
                    <div class="card-header">
                        Books
                        <a href="/books/add" class="btn btn-sm btn-success float-right"><i class="fa fa-plus-circle"></i>&nbsp;{{__('Add')}}</a>
                    </div>

                    <div class="card-body">
                        @forelse ($books as $book)
                            {{ $book->name }}
                        @empty
                            {{__('No books')}}
                        @endforelse
                    </div>

                    <div class="card-footer">
                        {{ $books->links() }}
                    </div>
                </div>

@endsection
