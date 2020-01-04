@extends('layouts.app')

@section('content')
            <div class="card">
                <div class="card-body">
                    <div class="card-block">{!! Form::open(array('url' => 'books/add')) !!}
                        {!! Form::text('title', __('Title')) !!}
                        {!! Form::text('year', __('Year'))->type('number')->max(date('Y'))->min(1) !!}
                        {!! Form::text('pages', __('Pages'))->type('number')->max(9999)->min(1) !!}
                        {!! Form::text('isbn10', __('ISBN 10')) !!}
                        {!! Form::text('isbn13', __('ISBN 13')) !!}
                        {!! Form::select('lang', __('Language'), $languages) !!}
                        {!! Form::textarea('description', __('Description')) !!}
                        {!! Form::close() !!}
                    </div>
                </div>
            </div>
@endsection
