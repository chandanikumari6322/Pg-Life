// app.js - wires the existing signup/login modal forms to the PHP backend via AJAX
// Uses jQuery (already loaded on every page) - no page reload needed.

$(document).ready(function () {

    // ---------- SIGNUP ----------
    $('#signup-form').on('submit', function (e) {
        e.preventDefault();
        const $form = $(this);
        const $btn = $form.find('button[type="submit"]');
        $btn.prop('disabled', true).text('Please wait...');

        $.ajax({
            url: 'api/signup.php',
            method: 'POST',
            data: $form.serialize(),
            dataType: 'json',
            success: function (res) {
                $btn.prop('disabled', false).text('Create Account');
                if (res.success) {
                    alert(res.message);
                    $('#signup-modal').modal('hide');
                    location.reload(); // refresh nav to show logged-in state
                } else {
                    alert(res.message);
                }
            },
            error: function (xhr) {
                $btn.prop('disabled', false).text('Create Account');
                let msg = 'Something went wrong. Please make sure Apache & MySQL are running and pglife.sql is imported.';
                try {
                    const parsed = JSON.parse(xhr.responseText);
                    if (parsed && parsed.message) msg = parsed.message;
                } catch (e) { /* not JSON, keep default message */ }
                console.error('Signup error:', xhr.responseText);
                alert(msg);
            }
        });
    });

    // ---------- LOGIN ----------
    $('#login-form').on('submit', function (e) {
        e.preventDefault();
        const $form = $(this);
        const $btn = $form.find('button[type="submit"]');
        $btn.prop('disabled', true).text('Please wait...');

        $.ajax({
            url: 'api/login.php',
            method: 'POST',
            data: $form.serialize(),
            dataType: 'json',
            success: function (res) {
                $btn.prop('disabled', false).text('Login');
                if (res.success) {
                    alert(res.message);
                    $('#login-modal').modal('hide');
                    location.reload();
                } else {
                    alert(res.message);
                }
            },
            error: function (xhr) {
                $btn.prop('disabled', false).text('Login');
                let msg = 'Something went wrong. Please make sure Apache & MySQL are running and pglife.sql is imported.';
                try {
                    const parsed = JSON.parse(xhr.responseText);
                    if (parsed && parsed.message) msg = parsed.message;
                } catch (e) { /* not JSON, keep default message */ }
                console.error('Login error:', xhr.responseText);
                alert(msg);
            }
        });
    });

});
