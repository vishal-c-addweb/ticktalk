<div class="col-lg-12 col-md-12 ntfcn-tab-content-left w-100 p-4 ">
    <div class="row">

        <div class="col-lg-12">
            <x-forms.file allowedFileExtensions="png jpg jpeg" class="mr-0 mr-lg-2 mr-md-2" :fieldLabel="__('modules.profile.profilePicture')"
                :fieldValue="$user->image_url" fieldName="logo" fieldId="logo" />
        </div>

        <div class="col-lg-6">
            <x-forms.text class="mr-0 mr-lg-2 mr-md-2" :fieldLabel="__('modules.profile.yourName')"
                :fieldPlaceholder="__('placeholders.name')" fieldName="name" fieldId="name"
                :fieldValue="ucwords($user->name)"></x-forms.text>
        </div>

        <div class="col-lg-6">
            <x-forms.text class="mr-0 mr-lg-2 mr-md-2" :fieldLabel="__('modules.profile.yourEmail')"
                :fieldPlaceholder="__('placeholders.email')" fieldName="email" fieldId="email"
                :fieldValue="$user->email"></x-forms.text>
        </div>

        <div class="col-lg-6">
            <x-forms.password class="mr-0 mr-lg-2 mr-md-2" :fieldLabel="__('modules.profile.yourPassword')"
                fieldName="password" fieldId="password" :fieldHelp="__('modules.client.passwordUpdateNote')"
                fieldPlaceholder="Must have at least 6 characters"></x-forms.password>
        </div>

        <div class="col-lg-6">
            <x-forms.tel fieldId="mobile" :fieldLabel="__('app.mobile')" fieldName="mobile"
                :fieldPlaceholder="__('placeholders.mobile')" :fieldValue="$user->mobile"></x-forms.tel>
        </div>

        <!-- COMPANY NAME START -->
        <div class="col-md-6">
            <div class="form-group mb-lg-0 mb-md-0 mb-4">
                <x-forms.label fieldId="company_name" :fieldLabel="__('app.company_name')">
                </x-forms.label>
                <div class="input-group" id="client_company_div">
                    <input type="text" id="company_name" name="company_name"
                        class="px-6 position-relative text-dark font-weight-normal form-control height-35 rounded p-0 text-left f-15"
                        placeholder="" value="">
                </div>
            </div>
        </div>
        <!-- COMPANY NAME END -->

        <div class="col-md-6">
            <x-forms.text class="mb-3 mt-3 mt-lg-0 mt-md-0" fieldId="website"
                :fieldLabel="__('modules.client.website')" fieldName="website"
                fieldPlaceholder="e.g. https://www.spacex.com/" fieldValue="">
            </x-forms.text>
        </div>

        <div class="col-lg-6">
            <x-forms.text class="mr-0 mr-lg-2 mr-md-2" :fieldLabel="__('app.gstNumber')"
                :fieldPlaceholder="__('placeholders.invoices.gstNumber')" fieldName="gst_number"
                fieldId="gst_number" fieldValue="" />
        </div>

        <div class="col-lg-4">
            <div class="form-group my-3">
                <label class="f-14 text-dark-grey mb-12 w-100" for="usr">@lang('modules.emailSettings.emailNotifications')</label>
                <div class="d-flex">
                    <x-forms.radio fieldId="login-yes" :fieldLabel="__('app.enable')" fieldName="email_notifications"
                        fieldValue="1" checked="true" :checked="($user->email_notifications === 1) ? 'checked' : ''">
                    </x-forms.radio>
                    <x-forms.radio fieldId="login-no" :fieldLabel="__('app.disable')" fieldValue="0"
                        fieldName="email_notifications" :checked="($user->email_notifications === 0) ? 'checked' : ''">
                    </x-forms.radio>
                </div>
            </div>
        </div>

         <div class="col-md-6">
            <div class="form-group my-3">
                <x-forms.textarea class="mr-0 mr-lg-2 mr-md-2"
                    :fieldLabel="__('modules.gdpr.publicPageConsentInformationBlock')" fieldName="consent_block"
                    fieldId="consent_block" :fieldPlaceholder="__('placeholders.sampleText')"
                    :fieldValue="$gdprSetting->consent_block">
                </x-forms.textarea>
            </div>
        </div>

         <div class="col-md-6">
            <div class="form-group my-3">
                <x-forms.textarea class="mr-0 mr-lg-2 mr-md-2"
                    :fieldLabel="__('modules.gdpr.publicPageConsentInformationBlock')" fieldName="consent_block"
                    fieldId="consent_block" :fieldPlaceholder="__('placeholders.sampleText')"
                    :fieldValue="$gdprSetting->consent_block">
                </x-forms.textarea>
            </div>
        </div>


    </div>
</div>

<!-- Buttons Start -->
<div class="w-100 border-top-grey">
    <div class="settings-btns py-3 d-none d-lg-flex d-md-flex justify-content-end px-4">
        <x-forms.button-primary id="save-right-to-access-data" icon="check">@lang('app.save')</x-forms.button-primary>
    </div>
</div>
<!-- Buttons End -->


<script>
    $(body).on('click', '#save-right-to-access-data', function() {
        $.easyAjax({
            url: "{{route('gdpr_settings.update_general')}}",
            container: '#editSettings',
            type: "POST",
            disableButton: true,
            buttonSelector: "#save-right-to-access-data",
            data: $('#editSettings').serialize(),
        })
    })
</script>
