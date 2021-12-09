(function ($) {
    $('#edit-scopes optgroup').hide();
    if ($('#edit-services option:selected')) {
        serviceChanged();
    }
    $('#edit-services').change(function () {
        serviceChanged();
    });

    function serviceChanged() {
        $('#edit-scopes optgroup').hide();
        let services = $('#edit-services').val();
        $.each(services, function (key, value) {
            $('#edit-scopes optgroup[label=' + value + ']').show();
        });
    }
})(jQuery);
