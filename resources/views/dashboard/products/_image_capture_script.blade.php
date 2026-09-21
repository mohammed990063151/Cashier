<script>
document.addEventListener('DOMContentLoaded', function () {
    var mainInput = document.getElementById('product_image');
    var cameraInput = document.getElementById('product_image_camera');
    var btnCamera = document.getElementById('btn_capture_camera');
    var btnGallery = document.getElementById('btn_pick_gallery');
    var preview = document.querySelector('.image-preview');
    var filenameEl = document.getElementById('product_image_filename');
    var form = mainInput ? mainInput.closest('form') : null;

    if (!mainInput) {
        return;
    }

    function showPreview(file) {
        if (!file) {
            return;
        }

        if (preview) {
            var reader = new FileReader();
            reader.onload = function (e) {
                preview.src = e.target.result;
            };
            reader.readAsDataURL(file);
        }

        if (filenameEl) {
            filenameEl.style.display = 'block';
            filenameEl.textContent = 'تم اختيار: ' + (file.name || 'صورة من الكاميرا');
        }
    }

    function assignFileToMain(file) {
        if (!file) {
            return;
        }

        try {
            var dt = new DataTransfer();
            dt.items.add(file);
            mainInput.files = dt.files;
        } catch (e) {
            if (cameraInput) {
                cameraInput.setAttribute('name', 'image');
                mainInput.removeAttribute('name');
            }
        }

        showPreview(file);
    }

    if (btnCamera && cameraInput) {
        btnCamera.addEventListener('click', function () {
            mainInput.setAttribute('name', 'image');
            cameraInput.removeAttribute('name');
            cameraInput.value = '';
            cameraInput.click();
        });

        cameraInput.addEventListener('change', function () {
            if (this.files && this.files[0]) {
                assignFileToMain(this.files[0]);
            }
        });
    }

    if (btnGallery) {
        btnGallery.addEventListener('click', function () {
            mainInput.setAttribute('name', 'image');
            if (cameraInput) {
                cameraInput.removeAttribute('name');
                cameraInput.value = '';
            }
            mainInput.click();
        });
    }

    mainInput.addEventListener('change', function () {
        if (this.files && this.files[0]) {
            showPreview(this.files[0]);
        }
    });

    if (form) {
        form.addEventListener('submit', function () {
            var mainHas = mainInput.files && mainInput.files.length > 0;
            var camHas = cameraInput && cameraInput.files && cameraInput.files.length > 0;

            if (!mainHas && camHas) {
                cameraInput.setAttribute('name', 'image');
                mainInput.removeAttribute('name');
            } else {
                mainInput.setAttribute('name', 'image');
                if (cameraInput) {
                    cameraInput.removeAttribute('name');
                }
            }
        });
    }
});
</script>
