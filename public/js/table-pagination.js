document.addEventListener("DOMContentLoaded", function() {
    const tables = document.querySelectorAll('.paginated-table');
    
    tables.forEach(table => {
        const tbody = table.querySelector('tbody');
        if (!tbody) return;
        
        let allRows = Array.from(tbody.children).filter(tr => tr.tagName.toLowerCase() === 'tr');
        
        // Skip if there's only a "no data" row (usually contains a td with colspan)
        if (allRows.length === 1 && allRows[0].querySelector('td[colspan]')) {
            return;
        }

        const rowsPerItem = parseInt(table.getAttribute('data-rows-per-item') || '1', 10);
        const totalItems = allRows.length / rowsPerItem;
        
        // As requested: "buatkan pagination... ketika melebihi 15 baris"
        if (totalItems <= 15) {
            // Still show the pagination dropdown if we want to customize, but if total is 0, we shouldn't.
            if (totalItems === 0) return;
        }

        const storageKey = 'pagination_page_' + window.location.pathname + window.location.search;
        let currentPage = 1;
        
        // Restore from sessionStorage if exists
        const savedPage = sessionStorage.getItem(storageKey);
        if (savedPage) {
            currentPage = parseInt(savedPage, 10) || 1;
        }
        
        let itemsPerPage = 15;

        // Create pagination controls container
        const controlsContainer = document.createElement('div');
        controlsContainer.className = 'd-flex justify-content-between align-items-center mt-3 mb-3 pagination-controls';
        
        // Items per page selector
        const selectorHTML = `
            <div class="d-flex align-items-center gap-2">
                <span class="text-muted small">Tampilkan:</span>
                <select class="form-select form-select-sm items-per-page-select" style="width: 70px; border-radius: 8px;">
                    <option value="15">15</option>
                    <option value="30">30</option>
                    <option value="50">50</option>
                </select>
                <span class="text-muted small">baris</span>
            </div>
        `;
        
        // Page navigation
        const navHTML = `
            <div class="d-flex align-items-center gap-2">
                <span class="text-muted small me-3 page-info"></span>
                <button class="btn btn-sm btn-outline-secondary btn-prev" style="border-radius: 8px;"><i class="bi bi-chevron-left"></i></button>
                <button class="btn btn-sm btn-outline-secondary btn-next" style="border-radius: 8px;"><i class="bi bi-chevron-right"></i></button>
            </div>
        `;
        
        controlsContainer.innerHTML = selectorHTML + navHTML;
        
        // Insert controls below table. Often inside a form or div.table-responsive.
        // We'll insert it right after the wrapper element.
        let parentWrapper = table.closest('.table-responsive') || table;
        // Sometimes the form wraps the table-responsive. Let's append it to the parent of the table-responsive.
        parentWrapper.parentNode.insertBefore(controlsContainer, parentWrapper.nextSibling);

        const selectEl = controlsContainer.querySelector('.items-per-page-select');
        const prevBtn = controlsContainer.querySelector('.btn-prev');
        const nextBtn = controlsContainer.querySelector('.btn-next');
        const infoEl = controlsContainer.querySelector('.page-info');

        function renderTable() {
            const totalPages = Math.ceil(totalItems / itemsPerPage);
            if (currentPage > totalPages) currentPage = totalPages;
            if (currentPage < 1) currentPage = 1;

            const startIndex = (currentPage - 1) * itemsPerPage;
            const endIndex = Math.min(startIndex + itemsPerPage, totalItems);

            // Hide all rows
            allRows.forEach(row => { row.style.display = 'none'; });

            // Show current page rows
            for (let i = startIndex; i < endIndex; i++) {
                for (let r = 0; r < rowsPerItem; r++) {
                    let rowIndex = (i * rowsPerItem) + r;
                    if (allRows[rowIndex]) {
                        allRows[rowIndex].style.display = '';
                    }
                }
            }

            // Update info text
            if (totalItems > 0) {
                infoEl.textContent = `Menampilkan ${startIndex + 1} - ${endIndex} dari ${totalItems} data`;
            } else {
                infoEl.textContent = `0 data`;
            }
            
            // Disable/Enable buttons
            prevBtn.disabled = currentPage === 1;
            nextBtn.disabled = currentPage === totalPages || totalPages === 0;
            
            // Hide controls if total items is less than the minimum selectable (15) and we are on page 1
            if (totalItems <= 15 && itemsPerPage === 15) {
                controlsContainer.style.display = 'none'; // hide it visually if not needed, as requested
            } else {
                controlsContainer.style.display = 'flex';
            }
        }

        selectEl.addEventListener('change', function() {
            itemsPerPage = parseInt(this.value, 10);
            currentPage = 1;
            sessionStorage.setItem(storageKey, currentPage);
            renderTable();
        });

        prevBtn.addEventListener('click', function(e) {
            e.preventDefault();
            if (currentPage > 1) {
                currentPage--;
                sessionStorage.setItem(storageKey, currentPage);
                renderTable();
            }
        });

        nextBtn.addEventListener('click', function(e) {
            e.preventDefault();
            const totalPages = Math.ceil(totalItems / itemsPerPage);
            if (currentPage < totalPages) {
                currentPage++;
                sessionStorage.setItem(storageKey, currentPage);
                renderTable();
            }
        });

        // Initialize
        renderTable();
    });
});
