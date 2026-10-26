@extends('backend.layouts.app')

@section('content')

<div class="aiz-titlebar text-left mt-2 mb-3">
    <div class="row align-items-center">
        <div class="col">
            <h1 class="h3">{{ translate('Admin History') }}</h1>
        </div>
    </div>
</div>
<br>

<div class="card">
    <form class="" id="sort_admin_histories" action="" method="GET">
        <div class="card-header row gutters-5">
            <div class="col">
                <h5 class="mb-md-0 h6">{{ translate('All Admin Activities') }}</h5>
            </div>
            <div class="col-md-3">
                <div class="form-group mb-0">
                    <select class="form-control form-control-sm aiz-selectpicker" data-live-search="true" id="user_id" name="user_id" onchange="sort_admin_histories()">
                        <option value="">{{ translate('All Users') }}</option>
                        @foreach($users as $user)
                        <option value="{{ $user->id }}" @if($user_id == $user->id) selected @endif>{{ $user->name }}</option>
                        @endforeach
                    </select>
                </div>
            </div>
            <div class="col-md-3 ml-auto">
                <div class="form-group mb-0">
                    <input type="text" class="form-control form-control-sm" id="search" name="search" @isset($sort_search) value="{{ $sort_search }}" @endisset placeholder="{{ translate('Type & Enter') }}">
                </div>
            </div>
        </div>

        <div class="card-body">
            <table class="table aiz-table mb-0">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>{{ translate('User') }}</th>
                        <th>{{ translate('IP') }}</th>
                        <th>{{ translate('Method') }}</th>
                        <th>{{ translate('Client') }}</th>
                        <th>{{ translate('URL') }}</th>
                        <th>{{ translate('Date') }}</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($histories as $key => $history)
                    <tr>
                        <td>{{ ($key+1) + ($histories->currentPage() - 1)*$histories->perPage() }}</td>
                        <td>
                            <div class="row gutters-5 w-200px w-md-250px mw-100">
                                <div class="col-auto">
                                    <span class="avatar avatar-sm avatar-soft-primary">
                                        <img class="rounded-circle" src="{{ uploaded_asset($history->user->avatar_original ?? null) ?? static_asset('assets/img/avatar-place.png') }}" onerror="this.onerror=null;this.src='{{ static_asset('assets/img/avatar-place.png') }}';">
                                    </span>
                                </div>
                                <div class="col">
                                    <span class="text-muted text-truncate-2">{{ $history->user->name ?? 'Unknown' }}</span>
                                </div>
                            </div>
                        </td>
                        <td><span class="text-muted">{{ $history->ip }}</span></td>
                        <td><span class="badge badge-inline badge-soft-primary">{{ $history->method }}</span></td>
                        <td>
                            <span class="text-muted text-truncate-2" title="{{ $history->client }}">{{ Str::limit($history->client, 40, ' ...') }}</span>
                        </td>
                        <td>
                            <a href="{{ $history->url }}" target="_blank" class="text-muted text-truncate-2" style="max-width: 300px;" title="{{ $history->url }}">{{ Str::limit($history->url, 60, ' ...') }}</a>
                        </td>
                        <td><span class="text-muted">{{ $history->created_at->format('d M, Y h:i A') }}</span></td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
            <div class="aiz-pagination">
                {{ $histories->appends(request()->input())->links() }}
            </div>
        </div>
    </form>
</div>

@endsection

@section('script')
    <script type="text/javascript">
        function sort_admin_histories() {
            $('#sort_admin_histories').submit();
        }
    </script>
@endsection
