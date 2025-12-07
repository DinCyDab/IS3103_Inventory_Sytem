// document.addEventListener('DOMContentLoaded', function() {
    
//     // Initialize dashboard
//     initDashboard();
    
//     // Auto-refresh dashboard data every 5 minutes
//     setInterval(refreshDashboardStats, 300000);
// });

// // Initialize Dashboard
// function initDashboard() {
//     console.log('Dashboard initialized');
    
//     // Add hover effects to cards
//     addCardInteractions();
    
//     // Format numbers
//     formatCurrency();
    
//     // Add table sorting if needed
//     // initTableSorting();
// }

// // Add interactive effects to cards
// function addCardInteractions() {
//     const cards = document.querySelectorAll('.dashboard-card');
    
//     cards.forEach(card => {
//         card.addEventListener('mouseenter', function() {
//             this.style.transform = 'translateY(-2px)';
//         });
        
//         card.addEventListener('mouseleave', function() {
//             this.style.transform = 'translateY(0)';
//         });
//     });
// }

// // Format currency values
// function formatCurrency() {
//     const currencyElements = document.querySelectorAll('.currency');
    
//     currencyElements.forEach(element => {
//         const value = parseFloat(element.textContent.replace(/[^0-9.-]+/g, ''));
//         if (!isNaN(value)) {
//             element.textContent = '₱' + value.toLocaleString('en-PH', {
//                 minimumFractionDigits: 2,
//                 maximumFractionDigits: 2
//             });
//         }
//     });
// }

// // Refresh dashboard statistics (AJAX)
// function refreshDashboardStats() {
//     fetch('?view=dashboardStats')
//         .then(response => response.json())
//         .then(data => {
//             console.log('Dashboard stats refreshed', data);
//             // Update the UI with new data if needed
//             updateInventorySummary(data.inventorySummary);
//         })
//         .catch(error => {
//             console.error('Error refreshing dashboard:', error);
//         });
// }

// // Update inventory summary display
// function updateInventorySummary(data) {
//     if (!data) return;
    
//     const quantityInHand = document.querySelector('.summary-card.orange .summary-value');
//     const toBeReceived = document.querySelector('.summary-card.purple .summary-value');
    
//     if (quantityInHand) {
//         animateNumber(quantityInHand, data.quantity_in_hand);
//     }
    
//     if (toBeReceived) {
//         animateNumber(toBeReceived, data.to_be_received);
//     }
// }

// // Animate number changes
// function animateNumber(element, targetValue) {
//     const currentValue = parseInt(element.textContent.replace(/,/g, ''));
//     const duration = 1000;
//     const steps = 30;
//     const increment = (targetValue - currentValue) / steps;
//     let current = currentValue;
//     let step = 0;
    
//     const timer = setInterval(() => {
//         step++;
//         current += increment;
//         element.textContent = Math.round(current).toLocaleString();
        
//         if (step >= steps) {
//             clearInterval(timer);
//             element.textContent = targetValue.toLocaleString();
//         }
//     }, duration / steps);
// }

// // Table sorting functionality (optional)
// function initTableSorting() {
//     const tables = document.querySelectorAll('.dashboard-table');
    
//     tables.forEach(table => {
//         const headers = table.querySelectorAll('th');
        
//         headers.forEach((header, index) => {
//             header.style.cursor = 'pointer';
//             header.addEventListener('click', () => sortTable(table, index));
//         });
//     });
// }

// // Sort table by column
// function sortTable(table, columnIndex) {
//     const tbody = table.querySelector('tbody');
//     const rows = Array.from(tbody.querySelectorAll('tr'));
//     const isAscending = table.dataset.sortOrder !== 'asc';
    
//     rows.sort((a, b) => {
//         const aValue = a.cells[columnIndex].textContent.trim();
//         const bValue = b.cells[columnIndex].textContent.trim();
        
//         // Try to parse as number
//         const aNum = parseFloat(aValue.replace(/[^0-9.-]+/g, ''));
//         const bNum = parseFloat(bValue.replace(/[^0-9.-]+/g, ''));
        
//         if (!isNaN(aNum) && !isNaN(bNum)) {
//             return isAscending ? aNum - bNum : bNum - aNum;
//         }
        
//         // Sort as string
//         return isAscending 
//             ? aValue.localeCompare(bValue)
//             : bValue.localeCompare(aValue);
//     });
    
//     // Clear and re-append sorted rows
//     tbody.innerHTML = '';
//     rows.forEach(row => tbody.appendChild(row));
    
//     // Update sort order
//     table.dataset.sortOrder = isAscending ? 'asc' : 'desc';
// }

// // Search/Filter functionality
// function filterTable(tableId, searchValue) {
//     const table = document.getElementById(tableId);
//     if (!table) return;
    
//     const tbody = table.querySelector('tbody');
//     const rows = tbody.querySelectorAll('tr');
//     const searchLower = searchValue.toLowerCase();
    
//     rows.forEach(row => {
//         const text = row.textContent.toLowerCase();
//         row.style.display = text.includes(searchLower) ? '' : 'none';
//     });
// }

// // Export table to CSV
// function exportTableToCSV(tableId, filename = 'export.csv') {
//     const table = document.getElementById(tableId);
//     if (!table) return;
    
//     const rows = table.querySelectorAll('tr');
//     const csv = [];
    
//     rows.forEach(row => {
//         const cols = row.querySelectorAll('td, th');
//         const rowData = Array.from(cols).map(col => {
//             return '"' + col.textContent.trim().replace(/"/g, '""') + '"';
//         });
//         csv.push(rowData.join(','));
//     });
    
//     // Download CSV
//     const csvContent = csv.join('\n');
//     const blob = new Blob([csvContent], { type: 'text/csv;charset=utf-8;' });
//     const link = document.createElement('a');
//     link.href = URL.createObjectURL(blob);
//     link.download = filename;
//     link.click();
// }

// // Print dashboard
// function printDashboard() {
//     window.print();
// }

// // Show notification
// function showNotification(message, type = 'info') {
//     // Create notification element
//     const notification = document.createElement('div');
//     notification.className = `notification notification-${type}`;
//     notification.textContent = message;
    
//     // Style notification
//     notification.style.cssText = `
//         position: fixed;
//         top: 20px;
//         right: 20px;
//         padding: 1rem 1.5rem;
//         background: ${type === 'success' ? '#10b981' : type === 'error' ? '#ef4444' : '#3b82f6'};
//         color: white;
//         border-radius: 0.5rem;
//         box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
//         z-index: 9999;
//         animation: slideIn 0.3s ease;
//     `;
    
//     document.body.appendChild(notification);
    
//     // Remove after 3 seconds
//     setTimeout(() => {
//         notification.style.animation = 'slideOut 0.3s ease';
//         setTimeout(() => notification.remove(), 300);
//     }, 3000);
// }

// // Add CSS animations
// const style = document.createElement('style');
// style.textContent = `
//     @keyframes slideIn {
//         from {
//             transform: translateX(100%);
//             opacity: 0;
//         }
//         to {
//             transform: translateX(0);
//             opacity: 1;
//         }
//     }
    
//     @keyframes slideOut {
//         from {
//             transform: translateX(0);
//             opacity: 1;
//         }
//         to {
//             transform: translateX(100%);
//             opacity: 0;
//         }
//     }
// `;
// document.head.appendChild(style);

// // Utility function to format date
// function formatDate(dateString) {
//     const date = new Date(dateString);
//     const options = { 
//         year: 'numeric', 
//         month: 'short', 
//         day: 'numeric',
//         hour: '2-digit',
//         minute: '2-digit'
//     };
//     return date.toLocaleDateString('en-PH', options);
// }

// // Check for low stock alerts
// function checkLowStockAlerts() {
//     const lowStockItems = document.querySelectorAll('.low-stock-item');
    
//     if (lowStockItems.length > 0) {
//         showNotification(`You have ${lowStockItems.length} items with low stock!`, 'warning');
//     }
// }

// // Initialize tooltips (if needed)
// function initTooltips() {
//     const tooltipElements = document.querySelectorAll('[data-tooltip]');
    
//     tooltipElements.forEach(element => {
//         element.addEventListener('mouseenter', function() {
//             const tooltip = document.createElement('div');
//             tooltip.className = 'tooltip';
//             tooltip.textContent = this.dataset.tooltip;
//             tooltip.style.cssText = `
//                 position: absolute;
//                 background: #1f2937;
//                 color: white;
//                 padding: 0.5rem;
//                 border-radius: 0.25rem;
//                 font-size: 0.875rem;
//                 z-index: 9999;
//                 pointer-events: none;
//             `;
            
//             document.body.appendChild(tooltip);
            
//             const rect = this.getBoundingClientRect();
//             tooltip.style.top = (rect.top - tooltip.offsetHeight - 5) + 'px';
//             tooltip.style.left = (rect.left + (rect.width / 2) - (tooltip.offsetWidth / 2)) + 'px';
            
//             this._tooltip = tooltip;
//         });
        
//         element.addEventListener('mouseleave', function() {
//             if (this._tooltip) {
//                 this._tooltip.remove();
//                 this._tooltip = null;
//             }
//         });
//     });
// }

// // Smooth scroll to section
// function scrollToSection(sectionId) {
//     const section = document.getElementById(sectionId);
//     if (section) {
//         section.scrollIntoView({ behavior: 'smooth', block: 'start' });
//     }
// }

// // Handle responsive chart resize
// window.addEventListener('resize', function() {
//     if (typeof Chart !== 'undefined' && window.salesChart) {
//         window.salesChart.resize();
//     }
// });