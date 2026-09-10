<div class="form-group">
    <label for="--switch-add-as-task-comment" class="col-sm-2 control-label"><b>CustomFields</b><br />Add As Task Comment</label>

    <div class="col-sm-6">
        <div class="controls">
            <div class="onoffswitch-wrap">
                <div class="onoffswitch">
                    <input type="checkbox" name="settings[{{ $enabled }}]" value="1" id="--switch-add-as-task-comment" class="onoffswitch-checkbox" @if (old('settings[' . $enabled . ']', $settings[$enabled]))checked="checked"@endif >
                    <label class="onoffswitch-label" for="--switch-add-as-task-comment"></label> <!-- //NOSONAR -->
                </div>
            </div>
        </div>
        <div class="form-help">
            When enabled, it will add all Custom Fields (label and values) as part of a newly created ClickUp Task
        </div>
    </div>
</div>
