/**
 * Specific extension JS
 */
$(() => {
    const $container = $('#extension-custom-field-copy-title-to-field-container')

    if ($container.length) {
        const $switch = $container.find('#--switch')
        const $nameContainer = $container.find('#--name-container')
        const $nameField = $nameContainer.find('#--name-field')

        $switch.on('change', function() {
            if ($(this).is(':checked')) {
                $nameContainer.removeClass('hide')
                $nameField.prop('required', true)
            } else {
                $nameContainer.addClass('hide')
                $nameField.prop('required', false)
            }
        })

        // Initial events
        $switch.trigger('change')
    }
});
