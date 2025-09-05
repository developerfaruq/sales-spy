document.addEventListener("DOMContentLoaded", function () {
    // Get references to filter form elements
    const keywordInput = document.querySelector('input[placeholder="Search by domain, title, description, etc."]');
    
    // Get all select elements and find them by their preceding label text
    const allSelects = document.querySelectorAll('select');
    let platformSelect, snsEmailSelect, tagsSelect, languageSelect, countrySelect, pagesSelect, tldSelect, techSelect, domainRegYearSelect;
    
    // Find selects by looking at their preceding label
    document.querySelectorAll('label.block.text-sm.font-medium.text-gray-700.mb-1').forEach(label => {
        const labelText = label.textContent.trim();
        const select = label.nextElementSibling;
        
        if (labelText === 'Platform') platformSelect = select;
        else if (labelText === 'SNS & Email') snsEmailSelect = select;
        else if (labelText === 'Tags') tagsSelect = select;
        else if (labelText === 'Language') languageSelect = select;
        else if (labelText === 'Country') countrySelect = select;
        else if (labelText === 'Pages') pagesSelect = select;
        else if (labelText === 'Top Level Domain') tldSelect = select;
        else if (labelText === 'Technologies') techSelect = select;
        else if (labelText === 'Domain Reg Year') domainRegYearSelect = select;
    });
    
    const indexedDateInput = document.querySelector('input[type="date"]');
    
    // Get references to action buttons
    const searchBtn = document.querySelector('button .ri-search-line').closest('button');
    const resetBtn = Array.from(document.querySelectorAll('button.bg-gray-100.text-gray-700'))
        .find(btn => btn.textContent.trim() === 'Reset');
    const exportBtn = document.querySelector('button .ri-download-line').closest('button');
    
    // Get references to results elements
    const resultsCountDiv = document.querySelector('.text-sm.text-gray-600');
    const resultsTable = document.querySelector('table');
    const resultsTableBody = resultsTable.querySelector('tbody');
    const paginationDiv = document.querySelector('.flex.items-center.space-x-2');
    
    // Pagination state
    let currentPage = 1;
    const itemsPerPage = 10;
    
    // Function to fetch stores with filters
    function fetchStores(page = 1) {
        currentPage = page;
        
        // Get filter values
        const keyword = keywordInput ? keywordInput.value : '';
        const platform = platformSelect ? platformSelect.value : '';
        const snsEmail = snsEmailSelect ? snsEmailSelect.value : '';
        const indexedDate = indexedDateInput ? indexedDateInput.value : '';
        const tags = tagsSelect ? tagsSelect.value : '';
        const language = languageSelect ? languageSelect.value : '';
        const country = countrySelect ? countrySelect.value : '';
        const pages = pagesSelect ? pagesSelect.value : '';
        const tld = tldSelect ? tldSelect.value : '';
        const tech = techSelect ? techSelect.value : '';
        const domainRegYear = domainRegYearSelect ? domainRegYearSelect.value : '';
        
        // Show loading state
        resultsTableBody.innerHTML = '<tr><td colspan="17" class="px-6 py-4 text-center">Loading...</td></tr>';
        
        // Construct URL with filters and pagination
        const url = `../api/filter_stores.php?page=${currentPage}&limit=${itemsPerPage}`;
        
        // Add filters to URL if they have values
        const params = new URLSearchParams();
        if (keyword) params.append('keyword', keyword);
        if (platform) params.append('platform', platform);
        if (snsEmail) params.append('sns_email', snsEmail);
        if (indexedDate) params.append('indexed_date', indexedDate);
        if (tags) params.append('tags', tags);
        if (language) params.append('language', language);
        if (country) params.append('country', country);
        if (pages) params.append('pages', pages);
        if (tld) params.append('tld', tld);
        if (tech) params.append('tech', tech);
        if (domainRegYear) params.append('domain_reg_year', domainRegYear);
        
        // Make API call
        fetch(`${url}&${params.toString()}`)
            .then(response => {
                if (!response.ok) {
                    throw new Error(`HTTP error! status: ${response.status}`);
                }
                return response.json();
            })
            .then(data => {
                // Clear previous results
                resultsTableBody.innerHTML = '';
                
                // Update results count
                if (resultsCountDiv) {
    const totalCount = data.pagination?.total_stores || 0;
    resultsCountDiv.textContent = `Found ${totalCount.toLocaleString()} Stores`;
}
                
                // Check if we have stores
                const stores = data.stores || [];
if (stores.length > 0) {
                    // Render each store row
                    stores.forEach(store => {
                        const row = document.createElement('tr');
                        row.className = 'hover:bg-gray-50 result-row cursor-pointer';
                        row.setAttribute('data-domain', store.domain);
                        row.setAttribute('data-platform', store.tech_stack || '');
                        row.setAttribute('data-title', store.title || '');
                        row.setAttribute('data-description', store.description || '');
                        row.setAttribute('data-country', store.country || '');
                        row.setAttribute('data-language', store.language || '');
                        row.setAttribute('data-email', store.email || '');
                        row.setAttribute('data-phone', store.phone || '');
                        row.setAttribute('data-tags', (store.tags || []).join(','));
                        row.setAttribute('data-domainregdate', store.domain_reg_date || '');
                        row.setAttribute('data-indexeddate', store.indexed_date || '');
                        
                        // Create HTML for the row
                        row.innerHTML = `
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="flex items-center">
                                    <div class="w-8 h-8 flex items-center justify-center bg-red-100 rounded-full mr-3">
                                        <i class="ri-global-line text-red-500"></i>
                                    </div>
                                    <div class="text-sm font-medium text-gray-900">
                                        ${store.domain}
                                    </div>
                                </div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                ${store.tech_stack || ''}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                ${store.title || ''}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                ${store.description || ''}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                ${store.language || ''}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                ${store.country || ''}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                ${store.email || ''}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                ${store.phone || ''}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                ${(store.tags || []).map(tag => 
                                    `<span class="bg-blue-100 text-blue-800 text-xs px-3 py-1 rounded-full mr-1">${tag}</span>`
                                ).join('')}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                ${store.domain_reg_date || ''}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                ${store.indexed_date || ''}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                ${store.facebook ? `<a href="${store.facebook}">${store.facebook}</a>` : ''}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                ${store.twitter ? `<a href="${store.twitter}">${store.twitter}</a>` : ''}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                ${store.instagram ? `<a href="${store.instagram}">${store.instagram}</a>` : ''}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                ${store.pinterest ? `<a href="${store.pinterest}">${store.pinterest}</a>` : ''}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                ${store.tiktok ? `<a href="${store.tiktok}">${store.tiktok}</a>` : ''}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                ${store.youtube ? `<a href="${store.youtube}">${store.youtube}</a>` : ''}
                            </td>
                        `;
                        
                        resultsTableBody.appendChild(row);
                    });
                    
                    // Update pagination
                    
const totalCount = data.pagination?.total_stores || 0;
const currentPage = data.pagination?.page || 1;
const limit = data.pagination?.limit || itemsPerPage;
updatePagination(totalCount, currentPage, limit);
                } else {
                    // No results
                    resultsTableBody.innerHTML = '<tr><td colspan="17" class="px-6 py-4 text-center">No stores found matching your criteria.</td></tr>';
                    paginationDiv.innerHTML = '';
                }
            })
            .catch(error => {
                console.error('Error fetching stores:', error);
                resultsTableBody.innerHTML = `<tr><td colspan="17" class="px-6 py-4 text-center text-red-500">Error loading data: ${error.message}</td></tr>`;
            });
    }
    
    // Function to update pagination controls
    function updatePagination(totalCount, currentPage, limit) {
        const totalPages = Math.ceil(totalCount / limit);
        let paginationHTML = '';
        
        // Previous page button
        paginationHTML += `
            <button class="text-gray-500 hover:bg-gray-100 w-8 h-8 rounded flex items-center justify-center"
                    ${currentPage === 1 ? 'disabled' : ''}
                    data-page="${currentPage - 1}">
                <div class="w-4 h-4 flex items-center justify-center">
                    <i class="ri-arrow-left-s-line"></i>
                </div>
            </button>
        `;
        
        // Information about current page
        const startItem = (currentPage - 1) * limit + 1;
        const endItem = Math.min(currentPage * limit, totalCount);
        
        // Update the "Showing X to Y of Z results" text
        const paginationInfo = document.querySelector('.text-sm.text-gray-700');
        if (paginationInfo) {
            paginationInfo.innerHTML = `
                Showing <span class="font-medium">${startItem}</span> to
                <span class="font-medium">${endItem}</span> of
                <span class="font-medium">${totalCount.toLocaleString()}</span> results
            `;
        }
        
        // Create pagination buttons
        // First page
        paginationHTML += `
            <button class="${currentPage === 1 ? 'bg-primary text-white' : 'text-gray-500 hover:bg-gray-100'} w-8 h-8 rounded flex items-center justify-center" 
                    ${currentPage === 1 ? 'disabled' : ''} data-page="1">
                <span>1</span>
            </button>
        `;
        
        // Ellipsis if needed
        if (currentPage > 3) {
            paginationHTML += '<span class="text-gray-500">...</span>';
        }
        
        // Pages around current page
        for (let i = Math.max(2, currentPage - 1); i <= Math.min(totalPages - 1, currentPage + 1); i++) {
            if (i === 1 || i === totalPages) continue; // Skip first and last pages as they're handled separately
            paginationHTML += `
                <button class="${currentPage === i ? 'bg-primary text-white' : 'text-gray-500 hover:bg-gray-100'} w-8 h-8 rounded flex items-center justify-center"
                        ${currentPage === i ? 'disabled' : ''} data-page="${i}">
                    <span>${i}</span>
                </button>
            `;
        }
        
        // Ellipsis if needed
        if (currentPage < totalPages - 2) {
            paginationHTML += '<span class="text-gray-500">...</span>';
        }
        
        // Last page (if more than one page)
        if (totalPages > 1) {
            paginationHTML += `
                <button class="${currentPage === totalPages ? 'bg-primary text-white' : 'text-gray-500 hover:bg-gray-100'} w-8 h-8 rounded flex items-center justify-center"
                        ${currentPage === totalPages ? 'disabled' : ''} data-page="${totalPages}">
                    <span>${totalPages}</span>
                </button>
            `;
        }
        
        // Next page button
        paginationHTML += `
            <button class="text-gray-500 hover:bg-gray-100 w-8 h-8 rounded flex items-center justify-center"
                    ${currentPage === totalPages ? 'disabled' : ''}
                    data-page="${currentPage + 1}">
                <div class="w-4 h-4 flex items-center justify-center">
                    <i class="ri-arrow-right-s-line"></i>
                </div>
            </button>
        `;
        
        // Update pagination container
    if (paginationDiv) {
        paginationDiv.innerHTML = paginationHTML;
        
        // Add event listeners to pagination buttons
        paginationDiv.querySelectorAll('button[data-page]').forEach(button => {
            button.addEventListener('click', function() {
                if (!this.hasAttribute('disabled')) {
                    const page = parseInt(this.getAttribute('data-page'));
                    if (!isNaN(page)) {
                        fetchStores(page);
                    } else {
                        console.error('Invalid page number:', this.getAttribute('data-page'));
                    }
                }
            });
        });
    } else {
        console.error('Pagination container not found');
    }
    }
    
    // fetchStores is now handled by event listeners
    
    // Add event listener to search button
    if (searchBtn) {
        searchBtn.addEventListener('click', function() {
            fetchStores(1); // Reset to first page when searching
        });
    } else {
        console.error('Search button not found');
    }
    
    // Add event listener to reset button
    if (resetBtn) {
        resetBtn.addEventListener('click', function() {
            // Reset all form inputs
            if (keywordInput) keywordInput.value = '';
            if (platformSelect) platformSelect.value = '';
            if (snsEmailSelect) snsEmailSelect.value = '';
            if (indexedDateInput) indexedDateInput.value = '';
            if (tagsSelect) tagsSelect.value = '';
            if (languageSelect) languageSelect.value = '';
            if (countrySelect) countrySelect.value = '';
            if (pagesSelect) pagesSelect.value = '';
            if (tldSelect) tldSelect.value = '';
            if (techSelect) techSelect.value = '';
            if (domainRegYearSelect) domainRegYearSelect.value = '';
            
            // Reset results
            fetchStores(1);
        });
    } else {
        console.error('Reset button not found');
    }
    
    // Add event listener to export button
    if (exportBtn) {
        exportBtn.addEventListener('click', function() {
            // Get current filter values
            const keyword = keywordInput ? keywordInput.value : '';
            const platform = platformSelect ? platformSelect.value : '';
            const snsEmail = snsEmailSelect ? snsEmailSelect.value : '';
            const indexedDate = indexedDateInput ? indexedDateInput.value : '';
            const tags = tagsSelect ? tagsSelect.value : '';
            const language = languageSelect ? languageSelect.value : '';
            const country = countrySelect ? countrySelect.value : '';
            const pages = pagesSelect ? pagesSelect.value : '';
            const tld = tldSelect ? tldSelect.value : '';
            const tech = techSelect ? techSelect.value : '';
            const domainRegYear = domainRegYearSelect ? domainRegYearSelect.value : '';
            
            // Construct export URL with current filters
            const params = new URLSearchParams();
            if (keyword) params.append('keyword', keyword);
            if (platform) params.append('platform', platform);
            if (snsEmail) params.append('sns_email', snsEmail);
            if (indexedDate) params.append('indexed_date', indexedDate);
            if (tags) params.append('tags', tags);
            if (language) params.append('language', language);
            if (country) params.append('country', country);
            if (pages) params.append('pages', pages);
            if (tld) params.append('tld', tld);
            if (tech) params.append('tech', tech);
            if (domainRegYear) params.append('domain_reg_year', domainRegYear);
            
            // Redirect to export endpoint
            window.location.href = `../api/export_csv.php?${params.toString()}`;
        });
    } else {
        console.error('Export button not found');
    }
    
    // Initial load of stores
    fetchStores(1);
});