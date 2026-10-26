@extends('backend.layouts.app')

@section('content')
<div class="aiz-titlebar text-left mt-2 mb-3">
    <div class="row align-items-center">
        <div class="col">
            <h1 class="h3">{{ translate('Add New Custom Review') }}</h1>
        </div>
    </div>
</div>

<div class="row">
    <div class="col-lg-8 mx-auto">
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0 h6">{{ translate('Custom Review Information') }}</h5>
            </div>
            <div class="card-body">
                <form class="form-horizontal" action="{{ route('custom-reviews.store') }}" method="POST" enctype="multipart/form-data">
                    @csrf

                    <div class="form-group row">
                        <label class="col-md-3 col-form-label">{{ translate('Name') }} <span class="text-danger">*</span></label>
                        <div class="col-md-9">
                            <input type="text" class="form-control" name="name" value="{{ old('name') }}" placeholder="{{ translate('Name') }}" required>
                            @error('name')
                                <small class="form-text text-danger">{{ $message }}</small>
                            @enderror
                        </div>
                    </div>

                    <div class="form-group row">
                        <label class="col-md-3 col-form-label">{{ translate('Thumbnail') }}</label>
                        <div class="col-md-9">
                            <div class="input-group" data-toggle="aizuploader" data-type="image">
                                <div class="input-group-prepend">
                                    <div class="input-group-text bg-soft-secondary font-weight-medium">{{ translate('Browse') }}</div>
                                </div>
                                <div class="form-control file-amount">{{ translate('Choose File') }}</div>
                                <input type="hidden" name="thumbnail" class="selected-files">
                            </div>
                            <div class="file-preview"></div>
                            <small class="form-text text-muted">{{ translate('Upload a thumbnail image for the review') }}</small>
                        </div>
                    </div>

                    <div class="form-group row">
                        <label class="col-md-3 col-form-label">{{ translate('Video Link') }}</label>
                        <div class="col-md-9">
                            <input type="url" class="form-control" name="video_link" value="{{ old('video_link') }}" placeholder="{{ translate('https://www.youtube.com/watch?v=...') }}">
                            <small class="form-text text-muted">{{ translate('Paste the video URL (YouTube, Vimeo, etc.)') }}</small>
                            @error('video_link')
                                <small class="form-text text-danger">{{ $message }}</small>
                            @enderror
                        </div>
                    </div>

                    <div class="form-group mb-0 text-right">
                        <button type="submit" class="btn btn-primary">{{ translate('Save') }}</button>
                    </div>

                </form>
            </div>
        </div>
    </div>
</div>
@endsection
