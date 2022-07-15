@extends('layouts.app')

@section('content')

    <!-- SETTINGS START -->
    <div class="w-100 d-flex">

        <!-- SETTINGS BOX START -->
        <div class="settings-box bg-additional-grey rounded ml-0">

            <x-form id="editSettings" method="PUT" class="ajax-form">
                <a class="mb-0 d-block d-lg-none text-dark-grey s-b-mob-sidebar" onclick="openSettingsSidebar()"><i
                        class="fa fa-ellipsis-v"></i></a>
                <div class="s-b-inner s-b-notifications bg-white b-shadow-4 rounded">
                    <div class="s-b-n-header" id="tabs">
                    <nav class="tabs px-4 border-bottom-grey">
                        <div class="nav" id="nav-tab" role="tablist">

                            @if($gdpr->terms || $gdpr->policy || $gdpr->customer_footer)
                                <a class="nav-item nav-link f-15 ajax-tab right-to-informed active" href="{{ route('gdpr.index') }}?tab=right-to-informed" role="tab"
                                    aria-controls="nav-rightToBeInformed" aria-selected="true">@lang('app.menu.rightToBeInformed')
                                </a>
                            @endif

                            @if($gdpr->public_lead_edit)
                                <a class="nav-item nav-link f-15 ajax-tab right-to-access" href="{{ route('gdpr.index') }}?tab=right-to-access" role="tab"
                                    aria-controls="nav-rightOfRectification" aria-selected="true">@lang('app.menu.rightOfRectification')
                                </a>
                            @endif

                            @if($gdpr->data_removal)
                                <a class="nav-item nav-link f-15 ajax-tab right-to-erasure" href="{{ route('gdpr.index') }}?tab=right-to-erasure" role="tab"
                                    aria-controls="nav-rightToErasure" aria-selected="true">@lang('app.menu.rightToErasure')
                                </a>
                            @endif

                            @if($gdpr->enable_export)
                                <a class="nav-item nav-link f-15 ajax-tab right-to-data-portability" href="{{route('gdpr.index')}}?tab=right-to-data-portability" role="tab"
                                aria-controls="nav-rightToDataPortability" aria-selected="true">@lang('app.menu.rightToDataPortability')
                                </a>
                            @endif

                            @if($gdpr->consent_customer)
                                <a class="nav-item nav-link f-15 ajax-tab consent" href="{{ route('gdpr.index') }}?tab=consent" role="tab"
                                    aria-controls="nav-consent" aria-selected="true">@lang('app.menu.consent')
                                </a>
                            @endif

                        </div>
                    </nav>
                </div>
                    <div class="s-b-n-content">
                        <div class="tab-content" id="nav-tabContent">
                            <!--  TAB CONTENT START -->
                            <div class="tab-pane fade show active" id="nav-email" role="tabpanel"
                                aria-labelledby="nav-email-tab">
                                <div class="d-flex flex-wrap justify-content-between">
                                    @include($view)
                                </div>
                            </div>
                            <!-- TAB CONTENT END -->
                        </div>
                    </div>
                </div>
            </x-form>
        </div>
        <!-- SETTINGS BOX END -->


    </div>
    <!-- SETTINGS END -->

@endsection

@push('scripts')
<script>

    /* manage menu active class */
    $('.nav-item').removeClass('active');
    const activeTab = "{{ $activeTab }}";
    $('.' + activeTab).addClass('active');

    showBtn(activeTab);

    function showBtn(activeTab) {
        $('.actionBtn').addClass('d-none');
        $('.'+activeTab+'-btn').removeClass('d-none');
    }

    $("body").on("click", ".ajax-tab", function(event) {
        event.preventDefault();

        $('.nav-item').removeClass('active');
        $(this).addClass('active');


        const requestUrl = this.href;

        $.easyAjax({
            url: requestUrl,
            blockUI: true,
            container: ".content-wrapper",
            historyPush: true,
            success: function(response) {
                if (response.status == "success") {
                    $('#nav-tabContent').html(response.html);
                    init('#nav-tabContent');
                }
            }
        });
    });

</script>
@endpush
