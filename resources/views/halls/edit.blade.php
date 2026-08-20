@extends('dashboard.includes.partial.base')

@section('title', 'Edit '.$hall->name)

@section('content')
    <x-page-header :title="'Edit '.$hall->name" subtitle="Venue details, spaces and commercial settings"
        icon="edit_note"
        :breadcrumbs="['Halls' => route('halls.index'), $hall->name => route('halls.show', $hall), 'Edit' => null]" />

    <form action="{{ route('halls.update', $hall) }}" method="POST" enctype="multipart/form-data">
        @include('halls._form', ['isEdit' => true])
    </form>
@endsection
