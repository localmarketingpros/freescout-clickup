<div class="form-group" id="extension-custom-field-copy-title-to-field-container">
    <label for="--switch" class="col-sm-2 control-label"><b>CustomFields</b><br />Copy Title To Field</label>

    <div class="col-sm-6">
        <div class="controls">
            <div class="onoffswitch-wrap">
                <div class="onoffswitch">
                    <input type="checkbox" name="settings[{{ $title['enabled'] }}]" value="1" id="--switch" class="onoffswitch-checkbox" @if (old('settings[' . $title['enabled'] . ']', $settings[$title['enabled']]))checked="checked"@endif >
                    <label class="onoffswitch-label" for="--switch"></label> <!-- //NOSONAR -->
                </div>
            </div>
            <div id="--name-container" style="margin-top: 5px;">
                Custom Field Name: <input type="text" id="--name-field" class="form-control" name="settings[{{ $title['name'] }}]" value="{{ old('settings[' . $title['name'] . ']', $settings[$title['name']]) }}" maxlength="120">
            </div>
        </div>
        <div class="form-help">
            When enabled, every new conversation created by a customer will copy its title over a specific Custom Field
        </div>
    </div>
</div>
