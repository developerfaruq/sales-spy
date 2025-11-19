// API Configuration
const API_BASE_URL = 'https://sales-spy.test/home/webs/api'; // Change this to your API URL
let currentPage = 1;
let currentFilters = {};

// Initialize on page load
document.addEventListener('DOMContentLoaded', function() {
    console.log('Initializing dashboard...');
    loadWebsites();
    setupFilters();
    setupPagination();
});

// Load websites from API
async function loadWebsites(page = 1) {
    try {
        showLoading();
        
        const params = new URLSearchParams({
            page: page,
            limit: 10,
            ...currentFilters
        });
        
        const url = `${API_BASE_URL}/get_websites.php?${params}`;
        console.log('Fetching from:', url);
        
        const response = await fetch(url);
        console.log('Response status:', response.status);
        
        const text = await response.text();
        console.log('Response text:', text);
        
        let result;
        try {
            result = JSON.parse(text);
        } catch (parseError) {
            console.error('JSON parse error:', parseError);
            throw new Error('Invalid JSON response from server');
        }
        
        console.log('Parsed result:', result);
        
        if (result.success) {
            renderWebsites(result.data);
            updatePagination(result.pagination);
            updateResultsCount(result.pagination.total);
        } else {
            showError('Failed to load websites: ' + result.error);
        }
    } catch (error) {
        console.error('Load websites error:', error);
        showError('Error connecting to server: ' + error.message);
    } finally {
        hideLoading();
    }
}

// Render websites in table
function renderWebsites(websites) {
    const tbody = document.querySelector('table tbody');
    if (!tbody) return;
    
    tbody.innerHTML = '';
    
    if (websites.length === 0) {
        tbody.innerHTML = `
            <tr>
                <td colspan="8" class="px-6 py-8 text-center text-gray-500">
                    No websites found. Try adjusting your filters.
                </td>
            </tr>
        `;
        return;
    }
    
    websites.forEach(site => {
        const row = createWebsiteRow(site);
        tbody.appendChild(row);
    });
    
    // Re-attach click handlers for details panel
    attachRowClickHandlers();
}

// Create a website row
function createWebsiteRow(site) {
    const row = document.createElement('tr');
    row.className = 'hover:bg-gray-50 result-row cursor-pointer';
    
    // Store data attributes
    row.setAttribute('data-id', site.id);
    row.setAttribute('data-domain', site.domain);
    row.setAttribute('data-platform', site.platform);
    row.setAttribute('data-title', site.title || '');
    row.setAttribute('data-description', site.description || '');
    row.setAttribute('data-language', site.language || '');
    row.setAttribute('data-country', site.country || '');
    row.setAttribute('data-email', site.email || '');
    row.setAttribute('data-phone', site.phone || '');
    row.setAttribute('data-domainregdate', site.domain_reg_date || '');
    row.setAttribute('data-indexeddate', site.indexed_date || '');
    
    // Generate random icon color
    const colors = ['red', 'blue', 'purple', 'green', 'yellow', 'indigo', 'pink', 'orange', 'teal'];
    const randomColor = colors[Math.floor(Math.random() * colors.length)];
    
    row.innerHTML = `
        <td class="px-6 py-4 whitespace-nowrap" data-column-key="domain">
            <div class="flex items-center">
                <div class="w-8 h-8 flex items-center justify-center bg-${randomColor}-100 rounded-full mr-3">
                    <i class="ri-global-line text-${randomColor}-500"></i>
                </div>
                <div class="text-sm font-medium text-gray-900 max-w-xs truncate">
                    ${site.domain || 'N/A'}
                </div>
            </div>
        </td>
        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500" data-column-key="platform">
            ${site.platform || 'Wix'}
        </td>
        <td class="px-6 py-4 text-sm text-gray-500" data-column-key="title">
            <div class="max-w-xs truncate" title="${site.title || 'N/A'}">
                ${site.title || 'N/A'}
            </div>
        </td>
        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500" data-column-key="country">
            ${site.country || 'N/A'}
        </td>
        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500" data-column-key="tags">
            ${generateTags(site)}
        </td>
        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500" data-column-key="email">
            <div class="max-w-xs truncate" title="${site.email || ''}">
                ${site.email || 'N/A'}
            </div>
        </td>
        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500" data-column-key="phone">
            ${site.phone || 'N/A'}
        </td>
        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500" data-column-key="indexed-date">
            ${site.indexed_date || 'N/A'}
        </td>
    `;
    
    return row;
}

// Generate tags from description or country
function generateTags(site) {
    const tags = [];
    
    if (site.country && site.country !== 'N/A') {
        tags.push(`<span class="bg-blue-100 text-blue-800 text-xs px-2 py-1 rounded-full mr-1">${site.country}</span>`);
    }
    
    if (site.platform && site.platform !== 'N/A' && site.platform !== 'Wix') {
        tags.push(`<span class="bg-purple-100 text-purple-800 text-xs px-2 py-1 rounded-full">${site.platform}</span>`);
    }
    
    return tags.length > 0 ? tags.join('') : '<span class="text-gray-400 text-xs">-</span>';
}

// Attach click handlers to rows
function attachRowClickHandlers() {
    const rows = document.querySelectorAll('.result-row');
    rows.forEach(row => {
        row.addEventListener('click', async function() {
            const siteId = this.getAttribute('data-id');
            await showWebsiteDetails(siteId);
        });
    });
}

// Show website details in panel
async function showWebsiteDetails(siteId) {
    try {
        const response = await fetch(`${API_BASE_URL}/get_website_details.php?id=${siteId}`);
        const result = await response.json();
        
        if (result.success) {
            renderDetailsPanel(result.data);
            openDetailsPanel();
        } else {
            showError('Failed to load details: ' + result.error);
        }
    } catch (error) {
        showError('Error loading details: ' + error.message);
    }
}

// Render details panel
function renderDetailsPanel(data) {
    const detailsContent = document.getElementById('detailsContent');
    if (!detailsContent) return;
    
    detailsContent.innerHTML = `
<div class="p-4 md:p-8">
  <div class="bg-white rounded-lg shadow-md p-4 md:p-6 mb-6">
    <div class="flex flex-col md:flex-row md:items-center md:justify-between mb-6 gap-4">
      <div class="flex items-center">
        <div class="w-8 h-8 bg-gray-700 rounded-full flex items-center justify-center mr-3">
          <i class="ri-global-line text-white text-lg"></i>
        </div>
        <h2 class="text-xl font-semibold break-words">${data.domain || data.name || ''}</h2>
      </div>
      <div class="flex flex-wrap gap-2 mt-2 md:mt-0">
        ${generateTags(data)}
      </div>
    </div>
    <div class="space-y-6">
      <h3 class="text-lg font-semibold mb-4">Website Details</h3>
      <div class="grid grid-cols-1 md:grid-cols-2 gap-x-12 gap-y-6">
        <div>
          <p class="text-sm text-gray-500 mb-1">Site Name</p>
          <div class="font-medium break-words">${data.name || 'N/A'}</div>
        </div>
        <div>
          <p class="text-sm text-gray-500 mb-1">Owner</p>
          <div class="font-medium break-words">${data.owner || 'N/A'}</div>
        </div>
        <div>
          <p class="text-sm text-gray-500 mb-1">Domain</p>
          <div class="font-medium break-words">${data.domain || 'N/A'}</div>
        </div>
        <div>
          <p class="text-sm text-gray-500 mb-1">Traffic</p>
          <div class="font-medium break-words">${data.traffic || 'N/A'}</div>
        </div>
        <div>
          <p class="text-sm text-gray-500 mb-1">Date Added</p>
          <div class="font-medium break-words">${data.domain_reg_date || 'N/A'}</div>
        </div>
        <div>
          <p class="text-sm text-gray-500 mb-1">Hosting Type</p>
          <div class="font-medium break-words">${data.hosting || 'N/A'}</div>
        </div>
        <div>
          <p class="text-sm text-gray-500 mb-1">Country</p>
          <div class="font-medium break-words">${data.country || 'N/A'}</div>
        </div>
        <div>
          <p class="text-sm text-gray-500 mb-1">Language</p>
          <div class="font-medium break-words">${data.language || 'N/A'}</div>
        </div>
        <div class="md:col-span-2">
          <p class="text-sm text-gray-500 mb-1">Description</p>
          <div class="font-medium break-words">${data.description || 'N/A'}</div>
        </div>
        <div>
          <p class="text-sm text-gray-500 mb-1">Email</p>
          <div class="font-medium break-words">${data.email || 'N/A'}</div>
        </div>
        <div>
          <p class="text-sm text-gray-500 mb-1">Phone</p>
          <div class="font-medium break-words">${data.phone || 'N/A'}</div>
        </div>
        <div>
          <p class="text-sm text-gray-500 mb-1">WhatsApp</p>
          <div class="font-medium break-words">${data.whatsapp || 'N/A'}</div>
        </div>
        <div>
          <p class="text-sm text-gray-500 mb-1">Domain Reg Date</p>
          <div class="font-medium break-words">${data.domain_reg_date || 'N/A'}</div>
        </div>
        <div>
          <p class="text-sm text-gray-500 mb-1">Indexed Date</p>
          <div class="font-medium break-words">${data.indexed_date || 'N/A'}</div>
        </div>
        <div>
          <p class="text-sm text-gray-500 mb-1">Facebook</p>
          <div class="font-medium break-words">${data.facebook ? `<a href="${data.facebook}" target="_blank" class="text-blue-600 hover:underline">${data.facebook}</a>` : 'N/A'}</div>
        </div>
        <div>
          <p class="text-sm text-gray-500 mb-1">Instagram</p>
          <div class="font-medium break-words">${data.instagram ? `<a href="${data.instagram}" target="_blank" class="text-blue-600 hover:underline">${data.instagram}</a>` : 'N/A'}</div>
        </div>
        <div>
          <p class="text-sm text-gray-500 mb-1">YouTube</p>
          <div class="font-medium break-words">${data.youtube ? `<a href="${data.youtube}" target="_blank" class="text-blue-600 hover:underline">${data.youtube}</a>` : 'N/A'}</div>
        </div>
        <div>
          <p class="text-sm text-gray-500 mb-1">TikTok</p>
          <div class="font-medium break-words">${data.tiktok ? `<a href="${data.tiktok}" target="_blank" class="text-blue-600 hover:underline">${data.tiktok}</a>` : 'N/A'}</div>
        </div>
        <div>
          <p class="text-sm text-gray-500 mb-1">Twitter</p>
          <div class="font-medium break-words">${data.twitter ? `<a href="${data.twitter}" target="_blank" class="text-blue-600 hover:underline">${data.twitter}</a>` : 'N/A'}</div>
        </div>
      </div>
    </div>
  </div>
</div>
    `;
}

// Setup filter handlers
function setupFilters() {
    // Search button
    const searchBtn = document.getElementById('search-btn');
    if (searchBtn) {
        searchBtn.addEventListener('click', handleSearch);
    }
    
    // Reset button
    const resetBtn = document.getElementById('reset-btn');
    if (resetBtn) {
        resetBtn.addEventListener('click', handleReset);
    }
    
    // Enter key on inputs
    document.querySelectorAll('input[type="text"], input[type="date"], select').forEach(input => {
        input.addEventListener('keypress', function(e) {
            if (e.key === 'Enter') {
                handleSearch();
            }
        });
    });
}

// Handle search
function handleSearch() {
    const filters = {};
    
    // Get keyword
    const keywordInput = document.getElementById('filter-keyword');
    if (keywordInput && keywordInput.value) {
        filters.keyword = keywordInput.value;
    }
    
    // Get platform
    const platformSelect = document.getElementById('filter-platform');
    if (platformSelect && platformSelect.value) {
        filters.platform = platformSelect.value;
    }
    
    // Get country (from advanced filters)
    const countrySelect = document.getElementById('filter-country');
    if (countrySelect && countrySelect.value) {
        filters.country = countrySelect.value;
    }
    
    // Get indexed date
    const dateInput = document.getElementById('filter-indexed-date');
    if (dateInput && dateInput.value) {
        filters.indexed_date = dateInput.value;
    }
    
    console.log('Searching with filters:', filters);
    currentFilters = filters;
    currentPage = 1;
    loadWebsites(currentPage);
}

// Handle reset
function handleReset() {
    // Clear all inputs
    const keywordInput = document.getElementById('filter-keyword');
    const platformSelect = document.getElementById('filter-platform');
    const countrySelect = document.getElementById('filter-country');
    const dateInput = document.getElementById('filter-indexed-date');
    
    if (keywordInput) keywordInput.value = '';
    if (platformSelect) platformSelect.selectedIndex = 0;
    if (countrySelect) countrySelect.selectedIndex = 0;
    if (dateInput) dateInput.value = '';
    
    currentFilters = {};
    currentPage = 1;
    loadWebsites(currentPage);
}

// Setup pagination
function setupPagination() {
    const paginationContainer = document.getElementById('pagination-buttons');
    if (!paginationContainer) return;
    
    paginationContainer.addEventListener('click', function(e) {
        const button = e.target.closest('button');
        if (!button) return;
        
        const pageText = button.textContent.trim();
        if (!isNaN(pageText)) {
            currentPage = parseInt(pageText);
            loadWebsites(currentPage);
        } else if (button.querySelector('.ri-arrow-right-s-line')) {
            currentPage++;
            loadWebsites(currentPage);
        }
    });
}

// Update pagination display
function updatePagination(pagination) {
    const container = document.getElementById('pagination-buttons');
    if (!container) return;
    
    const { page, total_pages } = pagination;
    
    let html = '';
    
    // Show first 3 pages
    for (let i = 1; i <= Math.min(3, total_pages); i++) {
        html += `
            <button class="${i === page ? 'bg-primary text-white' : 'text-gray-500 hover:bg-gray-100'} w-8 h-8 rounded flex items-center justify-center">
                <span>${i}</span>
            </button>
        `;
    }
    
    // Show ellipsis
    if (total_pages > 5) {
        html += '<span class="text-gray-500">...</span>';
    }
    
    // Show last 2 pages
    if (total_pages > 3) {
        for (let i = Math.max(4, total_pages - 1); i <= total_pages; i++) {
            html += `
                <button class="${i === page ? 'bg-primary text-white' : 'text-gray-500 hover:bg-gray-100'} w-8 h-8 rounded flex items-center justify-center">
                    <span>${i}</span>
                </button>
            `;
        }
    }
    
    // Next button
    if (page < total_pages) {
        html += `
            <button class="text-gray-500 hover:bg-gray-100 w-8 h-8 rounded flex items-center justify-center">
                <div class="w-4 h-4 flex items-center justify-center">
                    <i class="ri-arrow-right-s-line"></i>
                </div>
            </button>
        `;
    }
    
    container.innerHTML = html;
}

// Update results count
function updateResultsCount(total) {
    const countElement = document.getElementById('results-count');
    if (countElement) {
        countElement.textContent = `Found ${total.toLocaleString()} Stores`;
    }
    
    const showingElement = document.getElementById('pagination-info');
    if (showingElement) {
        const start = (currentPage - 1) * 10 + 1;
        const end = Math.min(currentPage * 10, total);
        showingElement.innerHTML = `
            Showing <span class="font-medium">${start}</span> to
            <span class="font-medium">${end}</span> of
            <span class="font-medium">${total.toLocaleString()}</span> results
        `;
    }
}

// Helper: Truncate text
function truncateText(text, maxLength) {
    if (!text) return 'N/A';
    if (text.length <= maxLength) return text;
    return text.substring(0, maxLength) + '...';
}

// Helper: Show loading
function showLoading() {
    const tbody = document.querySelector('table tbody');
    if (tbody) {
        tbody.innerHTML = `
            <tr>
                <td colspan="8" class="px-6 py-8 text-center">
                    <div class="flex items-center justify-center">
                        <div class="animate-spin rounded-full h-8 w-8 border-b-2 border-primary"></div>
                        <span class="ml-2 text-gray-600">Loading...</span>
                    </div>
                </td>
            </tr>
        `;
    }
}

// Helper: Hide loading
function hideLoading() {
    // Loading is replaced by actual content
}

// Helper: Show error
function showError(message) {
    console.error('Error:', message);
    const tbody = document.querySelector('table tbody');
    if (tbody) {
        tbody.innerHTML = `
            <tr>
                <td colspan="8" class="px-6 py-8 text-center">
                    <div class="text-red-600">
                        <i class="ri-error-warning-line text-2xl mb-2"></i>
                        <p>${message}</p>
                    </div>
                </td>
            </tr>
        `;
    }
}

// Helper: Open details panel
function openDetailsPanel() {
    const panel = document.getElementById('detailsPanel');
    if (panel) {
        panel.classList.remove('translate-x-full');
        panel.classList.add('translate-x-0');
    }
}

// Export button handler
document.addEventListener('DOMContentLoaded', function() {
    const exportBtn = document.getElementById('export-btn');
    if (exportBtn) {
        exportBtn.addEventListener('click', async function() {
            try {
                const params = new URLSearchParams(currentFilters);
                window.open(`${API_BASE_URL}/export_websites.php?${params}`, '_blank');
            } catch (error) {
                showError('Export failed: ' + error.message);
            }
        });
    }
});