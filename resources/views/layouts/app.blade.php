<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
@include('layouts/desktop/head')
<body class="page-header-fixed sidemenu-closed-hidelogo page-content-white page-md header-white white-sidebar-color logo-indigo sidemenu-closed">
<div id="app" class="page-wrapper">
    @include('layouts/desktop/top_nav')
    <div class="page-container">
        @include('layouts/desktop/'.Auth::user()->getCurrentRoleSlug().'/sidebar')
        <div class="page-content-wrapper">
            <div class="page-content">
                @if($autoThumbnail)
                {{ \App\Utils\UI\Thumbnail::Print($pageTitle) }}
                @endif
                @include('reusable_elements.section.session_flash_msg')
                @yield('content')
            </div>
        </div>
    </div>
    @include('layouts/desktop/footer')
</div>
@include('layouts/desktop/js')
</body>
</html>
