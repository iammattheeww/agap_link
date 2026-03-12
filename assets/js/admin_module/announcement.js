const annModal = document.getElementById('announcementModal');
        const openModal  = () => annModal.classList.add('active');
        const closeModal = () => annModal.classList.remove('active');

        document.getElementById('openAnnouncementModal').addEventListener('click', openModal);
        document.getElementById('closeAnnouncementModal').addEventListener('click', closeModal);
        document.getElementById('cancelAnnouncementModal').addEventListener('click', closeModal);

        const emptyBtn = document.getElementById('openAnnouncementModalEmpty');
        if (emptyBtn) emptyBtn.addEventListener('click', openModal);

        annModal.addEventListener('click', e => { if (e.target === annModal) closeModal(); });

        // File upload preview
        const uploadArea = document.getElementById('annFileUploadArea');
        const fileInput  = document.getElementById('annImageInput');
        const preview    = document.getElementById('annPreviewContainer');
        const previewImg = document.getElementById('annPreviewImage');
        const removeBtn  = document.getElementById('annRemoveImageBtn');

        uploadArea.addEventListener('click', () => fileInput.click());

        fileInput.addEventListener('change', function () {
            const file = this.files[0];
            if (!file) return;
            const reader = new FileReader();
            reader.onload = e => { previewImg.src = e.target.result; preview.style.display = 'block'; };
            reader.readAsDataURL(file);
        });

        removeBtn.addEventListener('click', () => { fileInput.value = ''; preview.style.display = 'none'; });
