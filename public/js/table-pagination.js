document.addEventListener("DOMContentLoaded", function() {
    const tables = document.querySelectorAll('.paginated-table');
    
    tables.forEach(table => {
        const isAjax = table.getAttribute('data-ajax-pagination') === 'true';
        if (isAjax) {
            initAjaxPagination(table);
            return;
        }

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

    function initAjaxPagination(table) {
        const tbody = table.querySelector('tbody');
        if (!tbody) return;
        
        let totalItems = parseInt(table.getAttribute('data-total-items') || '0', 10);
        let itemsPerPage = parseInt(table.getAttribute('data-per-page') || '15', 10);
        let currentPage = parseInt(table.getAttribute('data-current-page') || '1', 10);
        
        if (totalItems <= 15 && itemsPerPage === 15 && currentPage === 1) {
            if (totalItems === 0) return;
        }

        const controlsContainer = document.createElement('div');
        controlsContainer.className = 'd-flex justify-content-between align-items-center mt-3 mb-3 pagination-controls';
        
        const selectorHTML = `
            <div class="d-flex align-items-center gap-2">
                <span class="text-muted small">Tampilkan:</span>
                <select class="form-select form-select-sm items-per-page-select" style="width: 70px; border-radius: 8px;">
                    <option value="15" ${itemsPerPage === 15 ? 'selected' : ''}>15</option>
                    <option value="30" ${itemsPerPage === 30 ? 'selected' : ''}>30</option>
                    <option value="50" ${itemsPerPage === 50 ? 'selected' : ''}>50</option>
                </select>
                <span class="text-muted small">baris</span>
            </div>
        `;
        
        const navHTML = `
            <div class="d-flex align-items-center gap-2">
                <span class="text-muted small me-3 page-info"></span>
                <button class="btn btn-sm btn-outline-secondary btn-prev" style="border-radius: 8px;"><i class="bi bi-chevron-left"></i></button>
                <button class="btn btn-sm btn-outline-secondary btn-next" style="border-radius: 8px;"><i class="bi bi-chevron-right"></i></button>
            </div>
        `;
        
        controlsContainer.innerHTML = selectorHTML + navHTML;
        
        let parentWrapper = table.closest('.table-responsive') || table;
        parentWrapper.parentNode.insertBefore(controlsContainer, parentWrapper.nextSibling);

        const selectEl = controlsContainer.querySelector('.items-per-page-select');
        const prevBtn = controlsContainer.querySelector('.btn-prev');
        const nextBtn = controlsContainer.querySelector('.btn-next');
        const infoEl = controlsContainer.querySelector('.page-info');
        
        let isFetching = false;

        function updateUI(cPage, iPerPage, tItems) {
            const totalPages = Math.ceil(tItems / iPerPage) || 1;
            const startIndex = (cPage - 1) * iPerPage;
            const endIndex = Math.min(startIndex + iPerPage, tItems);
            
            if (tItems > 0) {
                infoEl.textContent = `Menampilkan ${startIndex + 1} - ${endIndex} dari ${tItems} data`;
            } else {
                infoEl.textContent = `0 data`;
            }
            
            prevBtn.disabled = cPage <= 1;
            nextBtn.disabled = cPage >= totalPages || totalPages === 0;
            selectEl.value = iPerPage;
            
            if (tItems <= 15 && iPerPage === 15 && cPage === 1) {
                controlsContainer.style.display = 'none';
            } else {
                controlsContainer.style.display = 'flex';
            }
        }

        async function fetchPage(page, perPage) {
            if (isFetching) return;
            isFetching = true;
            
            const url = new URL(window.location.href);
            url.searchParams.set('page_riwayat', page);
            url.searchParams.set('per_page', perPage);
            
            tbody.style.opacity = '0.5';
            
            try {
                const response = await fetch(url.toString(), {
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest'
                    }
                });
                
                if (response.ok) {
                    const data = await response.json();
                    if (data.html !== undefined) {
                        tbody.innerHTML = data.html;
                        
                        currentPage = data.currentPage;
                        itemsPerPage = data.perPage;
                        totalItems = data.totalItems;
                        
                        updateUI(currentPage, itemsPerPage, totalItems);
                        
                        window.history.pushState({}, '', url.toString());
                    }
                }
            } catch (e) {
                console.error("AJAX Pagination Error:", e);
            } finally {
                tbody.style.opacity = '1';
                isFetching = false;
            }
        }
        
        selectEl.addEventListener('change', function() {
            fetchPage(1, parseInt(this.value, 10));
        });
        
        prevBtn.addEventListener('click', function(e) {
            e.preventDefault();
            if (currentPage > 1) fetchPage(currentPage - 1, itemsPerPage);
        });
        
        nextBtn.addEventListener('click', function(e) {
            e.preventDefault();
            const totalPages = Math.ceil(totalItems / itemsPerPage);
            if (currentPage < totalPages) fetchPage(currentPage + 1, itemsPerPage);
        });

        // Initialize UI on first load
        updateUI(currentPage, itemsPerPage, totalItems);
    }
});
