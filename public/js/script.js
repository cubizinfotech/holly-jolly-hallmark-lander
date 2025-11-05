$(document).ready(function() {

    toastr.options = {
        "closeButton": false,
        "debug": false,
        "newestOnTop": false,
        "progressBar": true,
        "positionClass": "toast-bottom-right",
        "preventDuplicates": true,
        "onclick": null,
        "showDuration": "300",
        "hideDuration": "500",
        "timeOut": "3000",
        "extendedTimeOut": "500",
        "showEasing": "swing",
        "hideEasing": "linear",
        "showMethod": "fadeIn",
        "hideMethod": "fadeOut"
    };

    function showToast(message, type = 'info') {
        toastr[type](message);
    }

    // helper: clear all error texts
    function clearErrors() {
        $('.error-text').text('');
        $('input, textarea, label').removeClass('field-error');
    }

    // helper: set error text for a field
    function setError(fieldId, message) {
        $('#error-' + fieldId).text(message);
    }

    // email regex (reasonably strict)
    function isValidEmail(email) {
        // Step-by-step validated regex (RFC-like simplified)
        var re = /^[^\s@]+@[^\s@]+\.[^\s@]{2,}$/;
        return re.test(email);
    }

    // Collect data from form, return an object for AJAX
    function collectFormData($form) {
        var data = {};
        data.name = $.trim($('#name').val() || '');
        data.email = $.trim($('#email').val() || '');
        data.favorite_star = $.trim($('#favorite_star').val() || '');
        data.message = $.trim($('#message').val() || '');
        data.participate = $form.find('input[name="participate"]:checked').val() || '';
        // collect plotline[] checkboxes
        var plotlines = [];
        $form.find('input[name="plotline[]"]:checked').each(function() {
            plotlines.push($(this).val());
        });
        data.plotline = plotlines; // array
        return data;
    }

    // client-side validation; returns {valid: bool, errors: {field: message}}
    function validateClient(data) {
        var errors = {};

        if (!data.name) {
            errors.name = 'Name is required.';
        } else if (data.name.length > 150) {
            errors.name = 'Name is too long (max 150 chars).';
        }

        if (!data.email) {
            errors.email = 'Email is required.';
        } else if (!isValidEmail(data.email)) {
            errors.email = 'Please enter a valid email address.';
        } else if (data.email.length > 255) {
            errors.email = 'Email is too long.';
        }

        // if (!data.favorite_star) {
        //     errors.favorite_star = 'Please tell us your favorite Hallmark star.';
        // } else if (data.favorite_star.length > 255) {
        //     errors.favorite_star = 'Value too long.';
        // }

        // if (!data.participate) {
        //     errors.participate = 'Please choose whether you will participate.';
        // } else if (!['yes', 'no'].includes(data.participate)) {
        //     errors.participate = 'Invalid selection.';
        // }

        // if (!Array.isArray(data.plotline) || data.plotline.length === 0) {
        //     errors.plotline = 'Please select at least one plotline.';
        // }

        // message is optional, but limit length
        if (data.message && data.message.length > 2000) {
            errors.message = 'Message is too long (max 2000 chars).';
        }

        return { valid: Object.keys(errors).length === 0, errors: errors };
    }

    // disable/blur form UI
    function setFormBusy($form, busy) {
        var $section = $form; // we will blur entire form area
        if (busy) {
            $section.addClass('form-disabled');
            $section.find('input, textarea, button, select').prop('disabled', true);
            $('#submitBtn').addClass('loading');
        } else {
            $section.removeClass('form-disabled');
            $section.find('input, textarea, button, select').prop('disabled', false);
            $('#submitBtn').removeClass('loading');
        }
    }

    // map server validation errors to UI
    function applyErrorsToUI(errors) {
        // errors expected as { fieldName: "message" }
        Object.keys(errors).forEach(function(field) {
            setError(field, errors[field]);
        });
    }

    $('#submitForm').on('submit', function(e) {
        e.preventDefault();

        clearErrors();

        var $form = $(this);
        var formData = collectFormData($form);

        // Client-side validation
        var validation = validateClient(formData);
        if (!validation.valid) {
            applyErrorsToUI(validation.errors);
            showToast('Please fix the highlighted errors and try again.', 'error');
            return;
        }

        // Prepare payload for server: use FormData so arrays are properly sent
        var fd = new FormData();
        fd.append('name', formData.name);
        fd.append('email', formData.email);
        fd.append('favorite_star', formData.favorite_star);
        fd.append('participate', formData.participate);
        fd.append('message', formData.message);
        // plotline[] array
        formData.plotline.forEach(function(v) {
            fd.append('plotline[]', v);
        });

        // UI busy
        setFormBusy($form, true);

        $.ajax({
            url: $form.attr('action'),
            method: 'POST',
            data: fd,
            dataType: 'json',
            processData: false,
            contentType: false,
            success: function(resp, textStatus, jqXHR) {
                // Expecting JSON like { success: true, message: "..."} or { success:false, errors:{...}}
                if (resp && resp.success) {
                    showToast(resp.message || 'Submitted successfully!', 'success');
                    setTimeout(function () {
                        window.location.href = 'thankyou.php';
                    }, 2000);
                    // reset but keep disabled state briefly for UX
                    setTimeout(function(){
                        $form[0].reset();
                        setFormBusy($form, false);
                    }, 800);
                } else {
                    // If server returns validation errors
                    if (resp && resp.errors && typeof resp.errors === 'object') {
                        applyErrorsToUI(resp.errors);
                        showToast(resp.message || 'Validation failed.', 'error');
                    } else {
                        showToast(resp.message || 'Unexpected server response.', 'error');
                    }
                    setFormBusy($form, false);
                }
            },
            error: function(jqXHR, textStatus, errorThrown) {
                // try parse JSON from server if possible
                var parsed = null;
                try {
                    parsed = jqXHR.responseJSON || JSON.parse(jqXHR.responseText || '{}');
                } catch (err) {
                    parsed = null;
                }

                if (parsed && parsed.errors) {
                    applyErrorsToUI(parsed.errors);
                    showToast(parsed.message || 'Server validation failed.', 'error');
                } else {
                    if (jqXHR.status === 422) {
                        showToast('Validation failed (422). Please check your data.', 'error');
                    } else if (jqXHR.status === 500) {
                        showToast('Server error. Please try again later.', 'error');
                    } else if (textStatus === 'timeout') {
                        showToast('Request timed out. Check your connection.', 'error');
                    } else {
                        showToast('Network or server error occurred.', 'error');
                    }
                }
                setFormBusy($form, false);
            }
        });

    });

});
