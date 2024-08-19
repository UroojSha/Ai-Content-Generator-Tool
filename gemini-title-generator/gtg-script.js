jQuery(document).ready(function($) {
    // Generate Titles Button Click Handler
    $('#gtg-generate-button').on('click', function() {
        var input = $('#gtg-input').val();
        if (!input) {
            alert('Please enter a topic.');
            return;
        }

        // Show loading image and hide others
        $('#gtg-image-container .gtg-start-image').addClass('gtg-hidden');
        $('#gtg-image-container .gtg-error-image').addClass('gtg-hidden');
        $('#gtg-image-container .gtg-loading-image').removeClass('gtg-hidden');

        $('#gtg-title-list').empty(); // Clear any existing content
        $('#gtg-image-container').removeClass('gtg-hidden'); // Show the container if hidden

        $.ajax({
            url: gtg_ajax_obj.ajax_url,
            type: 'POST',
            data: {
                action: 'gtg_generate_titles',
                input: input,
                security: gtg_ajax_obj.nonce
            },
            success: function(response) {
                if (response.success) {
                    var titleList = response.data.titles;
                    if (titleList.length > 0) {
                        var $ol = $('<ol></ol>'); // Create an ordered list

                        titleList.slice(1).forEach(function(title) {
                            if (title.trim() !== "") { // Ensure title is not empty
                                $ol.append(
                                    '<li class="gtg-result-item">' +
                                    '<span>' + title + '</span>' +
                                    '<button class="gtg-copy-button"><i class="fa fa-copy"></i></button>' +
                                    '</li>'
                                );
                            }
                        });

                        if ($ol.children().length > 0) { // Only append if there are items
                            $('#gtg-title-list').append($ol);
                        } else {
                            $('#gtg-title-list').append('<li class="gtg-result-item">No titles found. Please try again.</li>');
                        }
                    } else {
                        $('#gtg-title-list').append('<li class="gtg-result-item">No titles found. Please try again.</li>');
                    }
                } else {
                    $('#gtg-title-list').append('<li class="gtg-result-item">Oops! Something went wrong. Please try again.</li>');
                    $('#gtg-image-container .gtg-error-image').removeClass('gtg-hidden');
                }
            },
            error: function() {
                $('#gtg-title-list').append('<li class="gtg-result-item">Oops! Something went wrong. Please try again.</li>');
                $('#gtg-image-container .gtg-error-image').removeClass('gtg-hidden');
            },
            complete: function() {
                $('#gtg-image-container .gtg-loading-image').addClass('gtg-hidden');
                console.log('AJAX request complete');
            }
        });
    });

    // Delegate copy button click event
    $(document).on('click', '.gtg-copy-button', function() {
        var text = $(this).siblings('span').text();
        copyToClipboard(text, this);
    });

    // Copy to clipboard function
    function copyToClipboard(text, button) {
        var textArea = document.createElement("textarea");
        textArea.value = text;
        textArea.style.position = "fixed";
        document.body.appendChild(textArea);
        textArea.focus();
        textArea.select();

        try {
            document.execCommand('copy');
            $(button).find('i').removeClass('fa-copy').addClass('fa-check');
            setTimeout(function() {
                $(button).find('i').removeClass('fa-check').addClass('fa-copy');
            }, 2000);
        } catch (err) {
            console.error('Unable to copy', err);
        }

        document.body.removeChild(textArea);
    }
});
