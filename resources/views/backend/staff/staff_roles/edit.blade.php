@extends('backend.layouts.app')

@section('content')
<div class="aiz-titlebar text-left mt-2 mb-3">
    <h5 class="mb-0 h6">{{translate('Role Information')}}</h5>
</div>

<div class="col-lg-12 mx-auto">
    <div class="card">
        <div class="card-body p-0">
            <ul class="nav nav-tabs nav-fill language-bar">
                @foreach (get_all_active_language() as $key => $language)
                    <li class="nav-item">
                        <a class="nav-link text-reset @if ($language->code == $lang) active @endif py-3" href="{{ route('roles.edit', ['id'=>$role->id, 'lang'=> $language->code] ) }}">
                            <img src="{{ static_asset('assets/img/flags/'.$language->code.'.png') }}" height="11" class="mr-1">
                            <span>{{$language->name}}</span>
                        </a>
                    </li>
                @endforeach
            </ul>
            <form class="p-4" action="{{ route('roles.update', $role->id) }}" method="POST">
                <input name="_method" type="hidden" value="PATCH">
                <input type="hidden" name="lang" value="{{ $lang }}">
            	   @csrf
                <div class="form-group row">
                    <label class="col-md-3 col-from-label" for="name">{{translate('Name')}} <i class="las la-language text-danger" title="{{translate('Translatable')}}"></i></label>
                    <div class="col-md-9">
                        @php $roleForTranslation = \App\Models\Role::where('id',$role->id)->first(); @endphp
                        <input type="text" placeholder="{{translate('Name')}}" id="name" name="name" class="form-control" value="{{ $roleForTranslation->getTranslation('name', $lang) }}" required>
                    </div>
                </div>
                <div class="card-header">
                    <h5 class="mb-0 h6">{{ translate('Permissions') }}</h5>
                </div>
                <br>
                @php
                    $permission_groups =  \App\Models\Permission::all()->groupBy('section');
                    $addons = array("offline_payment", "club_point", "pos_system", "paytm", "seller_subscription", "otp_system", "refund_request", "affiliate_system", "african_pg", "delivery_boy", "auction", "wholesale");
                @endphp
                @foreach ($permission_groups as $key => $permission_group)
                    @php
                        $show_permission_group = true;
                        
                        if(in_array($permission_group[0]['section'], $addons)){

                            if (addon_is_activated($permission_group[0]['section']) == false) {
                                $show_permission_group = false;
                            }
                        }
                    @endphp
                    @if($show_permission_group)
                        <ul class="list-group mb-4">
                            <li class="list-group-item bg-light d-flex align-items-center justify-content-between" aria-current="true">
                                <span>{{ translate(Str::headline($permission_group[0]['section'])) }}</span>
                                <div class="d-flex align-items-center">
                                    <span class="mr-2">{{ translate('All') }}</span>
                                    <label class="aiz-switch aiz-switch-success mb-0">
                                        <input type="checkbox" class="form-control section-select-all" data-section="section-{{ $loop->index }}"
                                            onchange="toggleSectionPermissions(this)"
                                            @if ($permission_group->every(fn($permission) => $role->hasPermissionTo($permission->name)))
                                                checked
                                            @endif>
                                        <span class="slider round"></span>
                                    </label>
                                </div>
                            </li>
                            <li class="list-group-item">
                                <div class="row" id="section-{{ $loop->index }}">
                                    @foreach ($permission_group as $key => $permission)
                                        <div class="col-lg-2 col-md-3 col-sm-4 col-xs-6">
                                            <div class="p-2 border mt-1 mb-2">
                                                <label class="control-label d-flex">{{ translate(Str::headline($permission->name))}}</label>
                                                <label class="aiz-switch aiz-switch-success">
                                                    <input type="checkbox" name="permissions[]" class="form-control demo-sw" value="{{ $permission->id }}"
                                                        @if ($role->hasPermissionTo($permission->name))
                                                            checked
                                                        @endif >
                                                    <span class="slider round"></span>
                                                </label>
                                            </div>
                                        </div>
                                    @endforeach
                                </div>
                            </li>
                        </ul>
                    @endif
                @endforeach

                <div class="form-group mb-3 mt-3 text-right">
                    <button type="submit" class="btn btn-primary">{{translate('Update')}}</button>
                </div>
            </form>
        </div>

    </div>
</div>

@endsection

@section('script')
    <script type="text/javascript">
        function toggleSectionPermissions(toggle) {
            var section = document.getElementById(toggle.getAttribute('data-section'));
            section.querySelectorAll('input[type="checkbox"][name="permissions[]"]').forEach(function (checkbox) {
                checkbox.checked = toggle.checked;
            });
        }
    </script>
@endsection
