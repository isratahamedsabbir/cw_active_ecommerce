@extends('backend.layouts.app')

@section('content')

<div class="aiz-titlebar text-left mt-2 mb-3">
    <div class="row align-items-center">
        <div class="col-auto">
            <h1 class="h3">{{ translate('Custom Reviews') }}</h1>
        </div>
        <div class="col text-right">
            <a href="{{ route('custom-reviews.create') }}" class="btn btn-circle btn-info">
                <span>{{ translate('Add New Custom Review') }}</span>
            </a>
        </div>
    </div>
</div>
<br>

<div class="card">
    <form class="" id="sort_custom_reviews" action="" method="GET">
        <div class="card-header row gutters-5">
            <div class="col">
                <h5 class="mb-md-0 h6">{{ translate('All Custom Reviews') }}</h5>
            </div>
            <div class="col-md-2 ml-auto">
                <div class="form-group mb-0">
                    <input type="text" class="form-control form-control-sm" id="search" name="search" @isset($sort_search) value="{{ $sort_search }}" @endisset placeholder="{{ translate('Type & Enter') }}">
                </div>
            </div>
        </div>

        <div class="card-body">
            <table class="table aiz-table mb-0">
                <thead>
                    <tr>
                        <th data-breakpoints="lg">#</th>
                        <th>{{ translate('Name') }}</th>
                        <th data-breakpoints="lg">{{ translate('Video Link') }}</th>
                        <th data-breakpoints="lg">{{ translate('Status') }}</th>
                        <th class="text-right">{{ translate('Options') }}</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($custom_reviews as $key => $custom_review)
                    <tr>
                        <td>{{ ($key+1) + ($custom_reviews->currentPage() - 1)*$custom_reviews->perPage() }}</td>
                        <td>
                            <div class="row gutters-5 w-200px w-md-300px mw-100">
                                <div class="col-auto">
                                    <img src="{{ uploaded_asset($custom_review->thumbnail) ?? static_asset('assets/img/placeholder.jpg') }}" alt="Image" class="size-50px img-fit">
                                </div>
                                <div class="col">
                                    <span class="text-muted text-truncate-2">{{ Str::limit($custom_review->name, 20, ' ...') }}</span>
                                </div>
                            </div>
                        </td>
                        <td>
                            @if($custom_review->video_link)
                                <a href="{{ $custom_review->video_link }}" target="_blank" class="btn btn-soft-primary btn-icon btn-circle btn-sm" title="{{ translate('View Video') }}">
                                    <i class="las la-play-circle"></i>
                                </a>
                            @else
                                <span class="text-muted">-</span>
                            @endif
                        </td>
                        <td>
                            <label class="aiz-switch aiz-switch-success mb-0">
                                <input onchange="update_status(this)" value="{{ $custom_review->id }}" type="checkbox" @if($custom_review->status == 1) checked @endif>
                                <span class="slider round"></span>
                            </label>
                        </td>
                        <td class="text-right">
                            <div class="btn-group" role="group" aria-label="Button group with nested dropdown">
                                <div class="btn-group" role="group">
                                    <button id="btnGroupDrop1" type="button" class="btn" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                        <i class="las la-ellipsis-v"></i>
                                    </button>
                                    <div class="dropdown-menu" aria-labelledby="btnGroupDrop1">
                                        <a class="dropdown-item" href="{{ route('custom-reviews.edit', $custom_review->id) }}" title="{{ translate('Edit') }}"><i class="las la-edit"></i>&nbsp;Edit</a>
                                        <a href="#" class="dropdown-item confirm-delete" data-href="{{ route('custom-reviews.destroy', $custom_review->id) }}" title="{{ translate('Delete') }}">
                                            <i class="las la-trash"></i>&nbsp;Delete
                                        </a>
                                    </div>
                                </div>
                            </div>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
            <div class="aiz-pagination">
                {{ $custom_reviews->appends(request()->input())->links() }}
            </div>
        </div>
    </form>
</div>

@endsection

@section('modal')
    <!-- Delete modal -->
    @include('modals.delete_modal')
@endsection

@section('script')
    <script type="text/javascript">

        $(document).on('click', '.confirm-delete', function (e) {
            e.preventDefault();
            var url = $(this).data('href');
            if (url) {
                $('#delete-form').attr('action', url);
            }
            $('#delete-modal').modal('show');
        });

        function update_status(el) {
            if (el.checked) {
                var status = 1;
            } else {
                var status = 0;
            }
            $.post('{{ route('custom-reviews.status') }}', {
                _token: '{{ csrf_token() }}',
                id: el.value,
                status: status
            }, function(data) {
                if (data == 1) {
                    AIZ.plugins.notify('success', '{{ translate('Status updated successfully') }}');
                } else {
                    AIZ.plugins.notify('danger', '{{ translate('Something went wrong') }}');
                }
            });
        }

        function sort_custom_reviews(el) {
            $('#sort_custom_reviews').submit();
        }

    </script>
@endsection
