jQuery(document).ready(function ($) {
    $('#product_id').select2();

    var video_desk = $('#video_url').val();
    console.log(video_desk);
    let videoFrame;
    $('#select-video').on('click', function (e) {
        e.preventDefault();
        // If the frame already exists, reopen it
        if (videoFrame) {
            videoFrame.open();
            return;
        }
        // Create a new media frame
        videoFrame = wp.media({
            title: 'Select or Upload a Video',
            button: {
                text: 'Use This Video'
            },
            library: {
                type: ['video'] // Restrict to videos
            },
            multiple: false // Do not allow multiple selections
        });

        // When a video is selected
        videoFrame.on('select', function () {
            const attachment = videoFrame.state().get('selection').first().toJSON();
            $('#video_url').val(attachment.url); // Set the video URL in the hidden input
            $('#selected-video-preview').html('<video src="' + attachment.url + '" width="320" controls></video>'); // Show preview
        });

        // Open the media frame
        videoFrame.open();
    });

    // Similar code for mobile video selection
    let videoFrameMbl;
    $('#select-video-mbl').on('click', function (e) {
        e.preventDefault();
        if (videoFrameMbl) {
            videoFrameMbl.open();
            return;
        }
        videoFrameMbl = wp.media({
            title: 'Select or Upload a Video for Mobile',
            button: {
                text: 'Use This Video'
            },
            library: {
                type: ['video']
            },
            multiple: false
        });

        videoFrameMbl.on('select', function () {
            const attachment = videoFrameMbl.state().get('selection').first().toJSON();
            $('#video_url_mbl').val(attachment.url);
            $('#selected-video-preview_mbl').html('<video src="' + attachment.url + '" width="320" controls></video>');
        });

        videoFrameMbl.open();
    });
});
