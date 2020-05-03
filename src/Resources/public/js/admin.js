const imageTypeFields = document.getElementsByClassName('js-admin-media-type');

function showSpinner() {
    const spinner = document.querySelector('.js-spinner-container');
    spinner.classList.add('d-b');
}

function hideSpinner() {
    const spinner = document.querySelector('.js-spinner-container');
    spinner.classList.remove('d-b');
}

function setRatioOnTypeChange() {
    [...imageTypeFields].forEach((element) => {
        // on l'utilise pas d'arrow function ici car les arrow function ne bind pas le this
        element.addEventListener('change', function(){

            showSpinner();

            const input = this;
            const type = this.value;
            const baseURl = this.dataset.crop_ratio_url;
            const typeUrl = baseURl + '?type=' + type;

            fetch(typeUrl)
                .then(response => response.json())
                .then((data) => {

                    const modalRatio = input.closest('.js-admin-collection-media-item').querySelector('[data-ratio]');
                    modalRatio.setAttribute('data-ratio', data['ratio']);

                    hideSpinner();

                })
                .catch((error) => {
                    hideSpinner();
                    console.error(error);
                });
        });


        // au click sur un format, on repli les modals qui peuvent être ouvertes
        element.addEventListener('click', function(e){
            console.log('clicked');
            const modals = element.closest('.js-admin-collection-media-item').getElementsByClassName('.modal');

            [...modals].forEach((modal) => {
                modal.classList.remove('show');
            });

        });
    });
}

function docReady(fn) {
    // see if DOM is already available
    if (document.readyState === "complete" || document.readyState === "interactive") {
        // call on next available tick
        setTimeout(fn, 1);
    } else {
        document.addEventListener("DOMContentLoaded", fn);
    }
}

docReady(() => {
    setRatioOnTypeChange();
});

$("body").on('DOMSubtreeModified', ".js-admin-collection-media", function(e) {

    if ($(e.target).hasClass('js-admin-collection-media')) {
        var lastFileUpload = $('.js-admin-collection-media').find('.fileupload').last()[0];

        initFileUpload(lastFileUpload);

        setRatioOnTypeChange();

    }

    /**
     * permet de détruire le fileupload de l'item qui vient d'être supprimé
     */
    $('.js-admin-collection-media-item').find('.button').on('click', function() {

        var $lastFileUpload = $(this).parent('.js-admin-collection-media-item').find('.fileupload');

        $lastFileUpload.fileupload('destroy');
    });

});


/**
 * MEDIA BUNDLE OVERRIDE
 */
$(function () {

    var $document = $(document);
    var $artgrisPath;
    var $artgrisTarget;

    $document
        .on('click', '.btn-manager', function () {
            $artgrisPath = $(this).closest('.artgris-media').find('.artgris-media-path');
            $artgrisTarget = $(this).attr('data-target');
            var $iframe = $($artgrisTarget).find('.iframe');
            if (!$iframe.attr('src')) {
                $iframe.attr('src', $iframe.data('src'));
            }
        })
        .on('click', '.artgris-media-button-erase', function (e) {
            e.preventDefault();
            e.stopPropagation();
            var $artgrisMedia = $(this).closest('.artgris-media');
            var $imagePath = $artgrisMedia.find('.artgris-media-path');
            $imagePath.val('');
            $imagePath.change();
            $artgrisMedia.find('.img-preview').html('');
        })
        .on('change', '.artgris-media-path', function (e) {
            var path = $(this).val();
            var id = $(this).attr('id');
            updatePreview(path, $(this).closest('.artgris-media').find('.img-preview'));
        })
        .on('show.bs.modal', '.modal.artgris-media-modal', function() {
            // affiche la div qui met une opacité
            $(this).closest('.js-admin-collection-media-item').find('.js-modal-background').removeClass('d-n');

            var $iframe = $(this).find('.iframe');
            $iframe.on('load', function () {
                applyIFrameEvents($(this));
            });
            applyIFrameEvents($iframe);

            function applyIFrameEvents($iframe) {
                $iframe.contents().on('click', '.select', function () {
                    var path = $(this).attr('data-path');
                    $artgrisPath.val(path).change();
                    $($artgrisTarget).modal('hide')
                });
            }
        })
        .on('hide.bs.modal', '.modal.artgris-media-modal', function() {
            // masque la div qui met une opacité
            $(this).closest('.js-admin-collection-media-item').find('.js-modal-background').addClass('d-n');
        })
        .on('show.bs.modal', '.modal.artgris-media-crop-modal', function(e) {
            $(this).find('.modal-body').hide();
            // affiche la div qui met une opacité
            $(this).closest('.js-admin-collection-media-item').find('.js-modal-background').removeClass('d-n');
        })
        .on('shown.bs.modal', '.modal.artgris-media-crop-modal', function(e) {
            var $this = $(this);
            var $imgPreview = $(e.relatedTarget).closest('.img-preview');
            var src = $imgPreview.find('img').attr('src');
            var conf = $imgPreview.data('conf');
            var $pathInput = $(e.relatedTarget).closest('.artgris-media').find('.artgris-media-path');
            var $cropContainer = $this.find('.modal-crop-container');

            var $modalBody = $this.find('.modal-body');

            // override
            var ratio = $modalBody.attr('data-ratio');

            $modalBody.show();
            $img = $('<img src="' + src + '">').css('max-width', '100%');
            $cropContainer.html('');
            $cropContainer.append($img);
            var $x = $this.find('.js-x');
            var $y = $this.find('.js-y');
            var $width = $this.find('.js-width');
            var $height = $this.find('.js-height');
            var $jsSave = $this.find('.js-save');

            var cropper = new Cropper($img[0], {
                aspectRatio: ratio ? ratio : 'free',
                zoomable: true,
                viewMode: 1,
                crop(event) {
                    $x.html(Math.round(event.detail.x));
                    $y.html(Math.round(event.detail.y));
                    $width.html(Math.round(event.detail.width));
                    $height.html(Math.round(event.detail.height));
                    $jsSave.attr('disabled', (event.detail.width <= 4 || event.detail.height <= 4));
                }
            });

            $modalBody.find('.js-rotate-right').click(function (e) {
                e.preventDefault();
                cropper.rotate(90);
            });
            $modalBody.find('.js-rotate-left').click(function (e) {
                e.preventDefault();
                cropper.rotate(-90);
            });
            $modalBody.find('.js-flip-x').click(function (e) {
                e.preventDefault();
                cropper.scaleX(-cropper.imageData.scaleX);
            });
            $modalBody.find('.js-flip-y').click(function (e) {
                e.preventDefault();
                cropper.scaleY(-cropper.imageData.scaleY);
            });

            $jsSave.off('click');
            $jsSave.click(function (e) {
                e.preventDefault();
                var data = cropper.getData();
                $.ajax({
                    url: crop_url,
                    type: "post",
                    data: {
                        conf: conf,
                        src: src,
                        x: Math.round(data.x),
                        y: Math.round(data.y),
                        width: Math.round(data.width),
                        height: Math.round(data.height),
                        scaleX: data.scaleX,
                        scaleY: data.scaleY,
                        rotate: data.rotate,
                        checkCrossOrigin: false
                    },
                    success: function (res) {
                        cropper.destroy();
                        $pathInput.val(res).change();
                        $this.modal('hide');
                    },
                    error: function (res) {
                        cropper.destroy();
                        console.error(res);
                    }
                });
            });
        })
        .on('hide.bs.modal', '.modal.artgris-media-crop-modal', function() {
            // masque la div qui met une opacité
            $(this).closest('.js-admin-collection-media-item').find('.js-modal-background').addClass('d-n');
        });

    $('.artgris-media-collection').each(function () {
        var $this = $(this);
        $this.collection({
            fade_out: false,
            max: $this.data('max'),
            min: $this.data('min'),
            init_with_n_elements: $this.data('init-with-n-elements'),
            add: '<span class="images-add"><a href="#" class="btn btn-light"><span class="fas fa-plus"></span> ' + addStr + '</a></span>',
            add_at_the_end: $this.data('add-at-the-end'),
            after_add: function (collection, element) {
                initFileUpload(element.find('.fileupload'));
                return true;
            },
            before_remove: function (collection, element) {
                $(element.find('.fileupload')).fileupload('destroy');
                return true;
            }
        })
    });

    var media = '.artgris-media';

    $document.on('dragover dragenter', media, function () {
        $(this).addClass('is-dragover');
    })
        .on('dragleave dragend drop', media, function () {
            $(this).removeClass('is-dragover');
        });

    // filemanager
    initFileUpload('.fileupload.alone');
    initFileUpload('.ui-sortable-handle .fileupload');

});

function updatePreview(path, dest) {
    $.ajax({
        url: url,
        data: {'path': path},
        type: 'GET',
        success: function (res) {
            var basePath = dest.data('base-path');
            if (res.icon.html.indexOf('<img') !== -1 && res.icon.html.indexOf('.svg') === -1 && path.indexOf(basePath) === 0) {
                var id = dest.data('id');
                dest.html('<a href="#" class="js-crop crop-hover" data-toggle="modal" data-backdrop="static" data-target="#crop-modal-' + id + '"><span class="artgris-media-crop-wrapper"><i class="fas fa-crop"></i></span>'+res.icon.html+'</a>');
            } else {
                dest.html(res.icon.html);
            }
            hideSpinner();
        },
        error: function () {
            hideSpinner();
            alert('Une erreur est survenue');
        }
    });
}

function initFileUpload(selector) {
    var $this = $(selector);
    $this.each(function () {
        $(this).fileupload({
            dataType: 'json',
            processQueue: false,
            dropZone: $(this).closest('.artgris-media')
        }).on('fileuploadsubmit', function (e, data) {
            showSpinner();
        }).on('fileuploaddone', function (e, data) {
            var $unusedPaths;
            $.each(data.result.files, function (index, file) {
                if (file.url) {
                    // Ajax update view
                    displaySuccess('<strong>' + file.name + '</strong> ' + successMessage);

                    var $input = null;
                    if (data.originalFiles.length > 1) {
                        $unusedPaths = $(e.target).closest('.artgris-media-collection').find('input.artgris-media-path').filter(function() {
                            return !this.value;
                        });
                        if ($unusedPaths.length > 0) {
                            $input = $unusedPaths.first();
                        }
                    }

                    if ($input === null) {
                        $input = $(e.target).closest('.artgris-media').find('input.artgris-media-path');
                    }

                    // Update preview
                    $input.val(file.url).change();

                    // update iframe
                    $('.iframe').attr('src', function (i, val) {
                        return val;
                    });

                } else if (file.error) {
                    hideSpinner();
                    displayError('<strong>' + file.name + '</strong> ' + file.error);
                    $unusedPaths = $(e.target).closest('.artgris-media-collection').find('input.artgris-media-path').filter(function() {
                        return !this.value;
                    });
                    $unusedPaths.closest('.artgris-media').find('.js-remove-collection').click();
                }
            });
        }).on('fileuploadfail', function (e, data) {
            hideSpinner();
            $.each(data.files, function () {
                displayError('File upload failed.');
            });
        }).on('fileuploadchange', function (e, data) {
            var $collection = $(e.target).closest('.artgris-media-collection');

            if (data.files.length > 1 && $collection.length > 0) {
                for (var i = 1; i < data.files.length; i++) {
                    $collection.find('.images-add a').click();
                }
            }
        });
    });
}

