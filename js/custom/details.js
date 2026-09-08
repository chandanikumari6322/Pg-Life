// details.js - handles the "interested" heart icon and Book Now button via AJAX
$(document).ready(function () {

    $('#bookBtn').on('click', function () {
        const $btn = $(this);
        const propertyId = $btn.data('id');
        $btn.prop('disabled', true).text('Booking...');

        $.ajax({
            url: 'api/book_property.php',
            method: 'POST',
            data: { property_id: propertyId },
            dataType: 'json',
            success: function (res) {
                if (res.message === 'please_login') {
                    $btn.prop('disabled', false).text('Book Now');
                    $('#login-modal').modal('show');
                    return;
                }
                if (res.success) {
                    alert(res.message);
                    $btn.removeClass('btn-primary').addClass('btn-secondary').text('Pending');
                } else {
                    $btn.prop('disabled', false).text('Book Now');
                    alert(res.message);
                }
            },
            error: function (xhr) {
                $btn.prop('disabled', false).text('Book Now');
                let msg = 'Something went wrong. Please make sure Apache & MySQL are running.';
                try {
                    const parsed = JSON.parse(xhr.responseText);
                    if (parsed && parsed.message) msg = parsed.message;
                } catch (e) {}
                console.error('Booking error:', xhr.responseText);
                alert(msg);
            }
        });
    });

    $('#interestBtn').on('click', function () {
        const $icon = $(this);
        const propertyId = $icon.data('id');

        $.ajax({
            url: 'api/toggle_interest.php',
            method: 'POST',
            data: { property_id: propertyId },
            dataType: 'json',
            success: function (res) {
                if (res.message === 'please_login') {
                    $('#login-modal').modal('show');
                    return;
                }
                if (res.success) {
                    if (res.interested) {
                        $icon.removeClass('far').addClass('fas');
                    } else {
                        $icon.removeClass('fas').addClass('far');
                    }
                    $('#interestedCount').text(res.interested_count);
                }
            }
        });
    });
});
