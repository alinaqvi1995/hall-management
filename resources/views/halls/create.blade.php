@extends('dashboard.includes.partial.base')

@section('title', 'Add Hall')

@section('content')
    <x-page-header title="Add Hall" subtitle="Onboard a new venue and its bookable spaces" icon="add_business"
        :breadcrumbs="['Halls' => route('halls.index'), 'Add Hall' => null]" />

    <form action="{{ route('halls.store') }}" method="POST" enctype="multipart/form-data">
        @include('halls._form', ['isEdit' => false, 'lawns' => collect()])
    </form>
@endsection
