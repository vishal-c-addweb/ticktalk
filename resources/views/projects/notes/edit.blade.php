<div class="row">
    <div class="col-sm-12">
        <x-form id="save-project-note-data-form" method="PUT">
            <div class="add-client bg-white rounded">
                <h4 class="mb-0 p-20 f-21 font-weight-normal text-capitalize border-bottom-grey">
                    @lang('app.project') @lang('app.note') @lang('app.details')</h4>

                <input type="hidden" name="project_id" value="{{ $projectId }}">

                <div class="row p-20">

                    <div class="col-md-6">
                        <x-forms.text fieldId="title" :fieldLabel="__('modules.client.noteTitle')" fieldName="title"
                            fieldRequired="true" :fieldPlaceholder="__('placeholders.note')" :fieldValue="$note->title">
                        </x-forms.text>
                    </div>

                    <div class="col-md-6 col-lg-6">
                        <div class="form-group my-3">
                            <x-forms.label fieldId="late_yes" :fieldLabel="__('modules.client.noteType')">
                            </x-forms.label>
                            <div class="d-flex">
                                <x-forms.radio fieldId="public" :fieldLabel="__('app.public')" fieldName="type"
                                    fieldValue="0" :checked="$note->type==0">
                                </x-forms.radio>
                                <x-forms.radio fieldId="private" :fieldLabel="__('app.private')" fieldValue="1"
                                    fieldName="type" :checked="$note->type==1"></x-forms.radio>
                            </div>
                        </div>
                    </div>

                </div>

                <div class="row p-20 @if ($note->type == 0) d-none @endif" id="private-note-details">

                    <div class="col-md-12">
                        <div class="form-group my-3">
                            <x-forms.label class="my-3" fieldId="selectEmployee" :fieldLabel="__('app.employee')">
                            </x-forms.label>
                            <x-forms.input-group>
                                <select class="form-control multiple-users" multiple name="user_id[]"
                                    id="selectEmployee" data-live-search="true" data-size="8">
                                    @foreach ($employees as $employee)
                                        <option @if (in_array($employee->id, $noteMembers)) selected @endif
                                            data-content="<span class='badge badge-pill badge-light border'><div class='d-inline-block mr-1'><img class='taskEmployeeImg rounded-circle' src='{{ $employee->image_url }}' ></div> {{ ucfirst($employee->name) }}</span>"
                                            value="{{ $employee->id }}">{{ ucwords($employee->name) }}</option>
                                    @endforeach
                                </select>
                            </x-forms.input-group>
                        </div>
                    </div>

                    <div class="col-lg-6 mb-2">
                        <x-forms.checkbox :fieldLabel="__('modules.client.visibleToClient')" fieldName="is_client_show"
                            fieldId="is_client_show" fieldValue="1" fieldRequired="true"
                            :checked="$note->is_client_show == 1" />
                    </div>

                    <div class="col-lg-6 mb-2">
                        <x-forms.checkbox :fieldLabel="__('modules.client.askToReenterPassword')"
                            fieldName="ask_password" fieldId="ask_password" fieldValue="1" fieldRequired="true"
                            :checked="$note->ask_password == 1" />
                    </div>

                </div>

                <div class="row p-20">
                    <div class="col-md-12 col-lg-12">
                        <div class="form-group my-3">
                            <x-forms.label class="my-3" fieldId="notes" :fieldLabel="__('modules.client.noteDetail')">
                            </x-forms.label>
                            <div id="details">{!! $note->details !!}</div>
                            <textarea name="details" id="details-text" class="d-none"></textarea>
                        </div>
                    </div>
                </div>

                <x-form-actions>
                    <x-forms.button-primary id="save-project-note-form" class="mr-3" icon="check">@lang('app.save')
                    </x-forms.button-primary>
                    <x-forms.button-cancel :link="route('projects.index')" class="border-0">@lang('app.cancel')
                    </x-forms.button-cancel>
                </x-form-actions>

            </div>

        </x-form>

    </div>
</div>

<script>
    $(document).ready(function() {

        $("#selectEmployee").selectpicker({
            actionsBox: true,
            selectAllText: "{{ __('modules.permission.selectAll') }}",
            deselectAllText: "{{ __('modules.permission.deselectAll') }}",
            multipleSeparator: " ",
            selectedTextFormat: "count > 8",
            countSelectedText: function(selected, total) {
                return selected + " {{ __('app.membersSelected') }} ";
            }
        });

        var quill2 = new Quill('#details', {
            modules: {
                toolbar: [
                    [{
                        header: [1, 2, 3, 4, 5, false]
                    }],
                    [{
                        'list': 'ordered'
                    }, {
                        'list': 'bullet'
                    }],
                    ['bold', 'italic', 'underline', 'strike'],
                    ['image', 'code-block', 'link'],
                    [{
                        'direction': 'rtl'
                    }],
                    ['clean']
                ],
                "emoji-toolbar": true,
                "emoji-textarea": true,
                "emoji-shortname": true,
            },
            theme: 'snow'
        });

        $('#save-project-note-form').click(function() {
            var comment = document.getElementById('details').children[0].innerHTML;
            document.getElementById('details-text').value = comment;

            const url = "{{ route('project-notes.update', $note->id) }}";

            $.easyAjax({
                url: url,
                container: '#save-project-note-data-form',
                type: "POST",
                disableButton: true,
                blockUI: true,
                buttonSelector: "#save-project-note-form",
                data: $('#save-project-note-data-form').serialize(),
                success: function(response) {
                    if (response.status == 'success') {
                        location.reload();
                        window.location.href = response.redirectUrl;

                    }
                }
            })
        });

        $('.custom-control-input').click(function() {
            $('#private-note-details').toggleClass('d-none');
        })

        init(RIGHT_MODAL);
    });
</script>
