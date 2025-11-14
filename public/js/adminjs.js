
// Featured Image Modal Functions
let currentGalleryId = null;
let currentSearchTerm = '';
let currentPage = 1;

/**
 * Otevře modal pro výběr tématického obrázku
 */
function openFeaturedImageModal(galleryId = null) {
    if (galleryId) {
        currentGalleryId = galleryId;
    }

    // Vytvoření overlay
    const overlay = document.createElement('div');
    overlay.className = 'modal-overlay';
    overlay.id = 'featuredImageModalOverlay';
    overlay.onclick = closeFeaturedImageModal;

    // Vytvoření modal container
    const modalContainer = document.createElement('div');
    modalContainer.id = 'featuredImageModalContainer';
    modalContainer.innerHTML = '<div class="loading-spinner"></div>';

    document.body.appendChild(overlay);
    document.body.appendChild(modalContainer);

    // Načtení obsahu modalu
    loadFeaturedImagesPage(1);
}

/**
 * Načte stránku s obrázky
 */
function loadFeaturedImagesPage(page = 1, search = '') {
    currentPage = page;
    currentSearchTerm = search;

    const container = document.getElementById('featuredImageModalContainer');
    if (!container) return;

    container.classList.add('loading');

    const params = new URLSearchParams({
        page: page,
        search: search
    });

    fetch(`${baseUrl}admin/gallery/featured-image-modal?${params}`)
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                container.innerHTML = data.html;
                container.classList.remove('loading');
            } else {
                alert('Chyba při načítání obrázků');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('Chyba při načítání obrázků');
        });
}

/**
 * Vyhledá obrázky
 */
function searchFeaturedImages(searchTerm) {
    // Debounce pro optimalizaci
    clearTimeout(window.searchTimeout);
    window.searchTimeout = setTimeout(() => {
        loadFeaturedImagesPage(1, searchTerm);
    }, 300);
}

/**
 * Vybere obrázek jako tématický
 */
function selectFeaturedImage(imageId, element) {
    if (!currentGalleryId) {
        // Pokud nemáme galleryId, uložíme do hidden input ve formuláři
        const hiddenInput = document.getElementById('featuredImageId');
        if (hiddenInput) {
            hiddenInput.value = imageId;

            // Načteme informace o obrázku pro zobrazení náhledu
            fetch(`${baseUrl}admin/gallery/image-info/${imageId}`)
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        updateFeaturedImagePreview(data.image);
                    }
                });
        }

        closeFeaturedImageModal();
        return;
    }

    // Odeslání výběru na server
    const formData = new FormData();
    formData.append('csrf_token', document.querySelector('#galleryForm input[name="csrf_token"]').value);
    formData.append('image_id', imageId);

    fetch(`${baseUrl}admin/gallery/select-featured-image/${currentGalleryId}`, {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            // Aktualizace náhledu v hlavním formuláři
            if (data.image_id) {
                fetch(`${baseUrl}admin/gallery/image-info/${data.image_id}`)
                    .then(response => response.json())
                    .then(imageData => {
                        if (imageData.success) {
                            updateFeaturedImagePreview(imageData.image);
                        }
                    });
            }
            closeFeaturedImageModal();
        } else {
            alert(data.message || 'Chyba při výběru obrázku');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('Chyba při výběru obrázku');
    });
}

/**
 * Aktualizuje náhled tématického obrázku ve formuláři
 */
function updateFeaturedImagePreview(image) {
    const selector = document.querySelector('.featured-image-selector');
    if (!selector) return;

    selector.innerHTML = `
        <div class="current-featured-image">
            <img src="${image.thumb_url}" alt="${image.title}">
            <div class="image-details">
                <strong>${image.title}</strong>
                <br><small>${image.width}×${image.height}</small>
            </div>
        </div>
        <div class="featured-image-actions">
            <button type="button" class="btn btn-outline-primary"
                    onclick="openFeaturedImageModal()">
                Změnit obrázek
            </button>
            <button type="button" class="btn btn-outline-danger"
                    onclick="removeFeaturedImage()">
                Odstranit
            </button>
        </div>
        <input type="hidden" name="featured_image_id" id="featuredImageId" value="${image.id}">
    `;
}

/**
 * Odstraní tématický obrázek
 */
function removeFeaturedImage() {
    const hiddenInput = document.getElementById('featuredImageId');
    if (hiddenInput) {
        hiddenInput.value = '';
    }

    const selector = document.querySelector('.featured-image-selector');
    if (selector) {
        selector.innerHTML = `
            <div class="no-image-selected">
                <span>Není vybrán žádný obrázek</span>
            </div>
            <div class="featured-image-actions">
                <button type="button" class="btn btn-outline-primary"
                        onclick="openFeaturedImageModal()">
                    Vybrat tématický obrázek
                </button>
            </div>
            <input type="hidden" name="featured_image_id" id="featuredImageId" value="">
        `;
    }
}

/**
 * Zavře modal
 */
function closeFeaturedImageModal() {
    const overlay = document.getElementById('featuredImageModalOverlay');
    const container = document.getElementById('featuredImageModalContainer');

    if (overlay) overlay.remove();
    if (container) container.remove();

    currentGalleryId = null;
    currentSearchTerm = '';
    currentPage = 1;
}

// Přidáme event listener pro ESC klávesu
document.addEventListener('keydown', function(event) {
    if (event.key === 'Escape') {
        closeFeaturedImageModal();
    }
});