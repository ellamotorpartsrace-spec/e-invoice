<?php
// history.php — Modern Multi-Platform Invoices History & BIR Tax Reports with Server-Side Pagination
$pageTitle = 'Invoices History & Tax Reports';
require_once __DIR__ . '/includes/header.php';
?>

<!-- Moment.js & DateRangePicker (Dual Calendar) -->
<script src="https://cdn.jsdelivr.net/npm/moment@2.29.4/moment.min.js"></script>
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/daterangepicker/daterangepicker.css">
<script src="https://cdn.jsdelivr.net/npm/daterangepicker/daterangepicker.min.js"></script>

<style>
/* Modern Table Row Action Buttons */
.action-btn-group {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    gap: 6px;
    white-space: nowrap;
}

.action-btn {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    gap: 5px;
    height: 29px;
    padding: 0 11px;
    font-size: 0.74rem;
    font-weight: 600;
    line-height: 1;
    border-radius: 9999px;
    text-decoration: none !important;
    white-space: nowrap !important;
    user-select: none;
    transition: all 0.16s cubic-bezier(0.16, 1, 0.3, 1);
    box-shadow: 0 1px 2px rgba(0, 0, 0, 0.04);
}

.action-btn i {
    font-size: 0.74rem;
    line-height: 1;
}

.action-btn-icon-only {
    width: 29px;
    padding: 0 !important;
}

/* View Button */
.action-btn-view {
    background: #ffffff;
    color: #334155;
    border: 1px solid #cbd5e1;
}

.action-btn-view:hover {
    background: #f8fafc;
    color: #0f172a;
    border-color: #94a3b8;
    transform: translateY(-1px);
    box-shadow: 0 3px 8px rgba(0, 0, 0, 0.08);
}

.action-btn-view:active {
    transform: translateY(0);
}

/* Download Button */
.action-btn-dl {
    background: #eff6ff;
    color: #1d4ed8;
    border: 1px solid #bfdbfe;
}

.action-btn-dl:hover {
    background: linear-gradient(135deg, #2563eb 0%, #1d4ed8 100%);
    color: #ffffff;
    border-color: #1d4ed8;
    transform: translateY(-1px);
    box-shadow: 0 4px 12px rgba(37, 99, 235, 0.3);
}

.action-btn-dl:active {
    transform: translateY(0);
}

/* Top Header Quick Action Buttons */
.btn-header-csv {
    background: #f0fdf4 !important;
    color: #15803d !important;
    border: 1px solid #bbf7d0 !important;
    transition: all 0.18s ease !important;
}
.btn-header-csv:hover {
    background: #dcfce7 !important;
    color: #166534 !important;
    border-color: #86efac !important;
    transform: translateY(-1px);
    box-shadow: 0 3px 8px rgba(22, 101, 52, 0.15) !important;
}

.btn-header-dl {
    background: #eff6ff !important;
    color: #1d4ed8 !important;
    border: 1px solid #bfdbfe !important;
    transition: all 0.18s ease !important;
}
.btn-header-dl:hover {
    background: #dbeafe !important;
    color: #1e40af !important;
    border-color: #93c5fd !important;
    transform: translateY(-1px);
    box-shadow: 0 3px 8px rgba(37, 99, 235, 0.18) !important;
}
</style>

<!-- Header Banner -->
<div class="d-flex flex-wrap justify-content-between align-items-center mb-4 gap-3">
    <div>
        <div class="d-flex align-items-center gap-2 mb-1">
            <h2 class="fw-bold mb-0" style="letter-spacing: -0.02em;">Invoices History & Tax Reports</h2>
            <span class="badge bg-success-subtle text-success border border-success-subtle px-2 py-1 rounded-pill" style="font-size:0.75rem;">
                BIR 2550Q Audit Ready
            </span>
        </div>
        <p class="text-secondary small mb-0">Official sequential audit log of all issued BIR Annex A1 Sales Invoices across Shopee, Lazada, and TikTok Shop with automated 12% VAT reconciliation.</p>
    </div>
    <div class="d-flex flex-wrap gap-2">
        <button type="button" class="btn btn-sm px-3 py-2 rounded-pill fw-bold shadow-xs btn-header-csv" onclick="exportCsv()">
            <i class="fa-solid fa-file-excel me-1"></i> Export BIR 2550Q CSV
        </button>
        <button type="button" class="btn btn-sm px-3 py-2 rounded-pill fw-bold shadow-xs btn-header-dl" onclick="downloadAllFiltered()">
            <i class="fa-solid fa-file-zipper me-1"></i> Download All Filtered ZIP
        </button>
        <a href="<?= BASE_URL ?>index.php" class="btn btn-enterprise-primary btn-sm px-3 py-2 rounded-pill fw-bold shadow-xs">
            <i class="fa-solid fa-plus me-1"></i> New Batch Upload
        </a>
    </div>
</div>

<!-- KPI Summary Cards -->
<div class="row g-3 mb-4">
    <div class="col-sm-6 col-xl-3">
        <div class="kpi-card">
            <div class="kpi-icon" style="background:var(--primary-light); color:var(--primary);">
                <i class="fa-solid fa-receipt"></i>
            </div>
            <div>
                <div class="kpi-label">Invoices Issued</div>
                <div class="kpi-value" id="kpiCount">0</div>
            </div>
        </div>
    </div>
    <div class="col-sm-6 col-xl-3">
        <div class="kpi-card">
            <div class="kpi-icon" style="background:var(--success-bg); color:var(--success);">
                <i class="fa-solid fa-wallet"></i>
            </div>
            <div>
                <div class="kpi-label">Total Gross Sales</div>
                <div class="kpi-value" id="kpiGross">₱0.00</div>
            </div>
        </div>
    </div>
    <div class="col-sm-6 col-xl-3">
        <div class="kpi-card">
            <div class="kpi-icon" style="background:var(--info-bg); color:var(--info);">
                <i class="fa-solid fa-file-invoice"></i>
            </div>
            <div>
                <div class="kpi-label">Net Vatable Sales</div>
                <div class="kpi-value" id="kpiVatable">₱0.00</div>
            </div>
        </div>
    </div>
    <div class="col-sm-6 col-xl-3">
        <div class="kpi-card">
            <div class="kpi-icon" style="background:rgba(217, 119, 6, 0.1); color:#d97706;">
                <i class="fa-solid fa-coins"></i>
            </div>
            <div>
                <div class="kpi-label">12% VAT Collected</div>
                <div class="kpi-value" id="kpiVat" style="color:#b45309;">₱0.00</div>
            </div>
        </div>
    </div>
</div>
<!-- Search & Filter Card -->
<div class="app-card mb-4" style="border:1px solid #e2e8f0; overflow:hidden;">
    <input type="hidden" id="fPlatform" value="all">
    <div class="d-flex align-items-stretch" style="min-height:52px;">

        <!-- Search Bar — grows to fill available space -->
        <div class="d-flex align-items-center gap-2 px-4 flex-grow-1" id="searchWrapper"
            style="border-right:1px solid #e2e8f0; background:#fafbfc; transition:background 0.15s, border-color 0.15s;">
            <i class="fa-solid fa-magnifying-glass" style="color:#94a3b8; font-size:0.88rem; flex-shrink:0;"></i>
            <input type="text" class="form-control border-0 p-0 fw-medium" id="fSearch"
                placeholder="Search invoice number, order SN, or buyer name..."
                onkeyup="if(event.key==='Enter') applyFilters()"
                onfocus="this.closest('#searchWrapper').style.background='#fff'; this.closest('#searchWrapper').style.borderRightColor='#2563eb';"
                onblur="this.closest('#searchWrapper').style.background='#fafbfc'; this.closest('#searchWrapper').style.borderRightColor='#e2e8f0';"
                style="box-shadow:none; font-size:0.875rem; background:transparent; color:#0f172a; width:100%;">
            <button class="btn p-0 border-0 d-none" type="button" id="btnClearSearch" onclick="clearSearchFilter()"
                style="line-height:1; flex-shrink:0; color:#94a3b8;">
                <i class="fa-solid fa-circle-xmark" style="font-size:0.9rem;"></i>
            </button>
        </div>

        <!-- Platform Pills — fixed, centered -->
        <div class="d-flex align-items-center gap-1 px-3 flex-shrink-0" id="platformPillFilter"
            style="border-right:1px solid #e2e8f0; background:#fff;">
            <button type="button" class="filter-plat-btn btn btn-sm rounded-pill px-3 py-1 fw-semibold active"
                data-platform="all" onclick="selectFilterPlatform('all')"
                style="font-size:0.8rem; white-space:nowrap; transition:all 0.2s; letter-spacing:-0.01em;">
                <i class="fa-solid fa-border-all me-1" style="font-size:0.75rem;"></i>All
            </button>
            <button type="button" class="filter-plat-btn btn btn-sm rounded-pill px-3 py-1 fw-semibold"
                data-platform="Shopee" onclick="selectFilterPlatform('Shopee')"
                style="font-size:0.8rem; white-space:nowrap; transition:all 0.2s; color:#64748b; letter-spacing:-0.01em;">
                <i class="fa-solid fa-bag-shopping me-1" style="color:#ee4d2d; font-size:0.75rem;"></i>Shopee
            </button>
            <button type="button" class="filter-plat-btn btn btn-sm rounded-pill px-3 py-1 fw-semibold"
                data-platform="Lazada" onclick="selectFilterPlatform('Lazada')"
                style="font-size:0.8rem; white-space:nowrap; transition:all 0.2s; color:#64748b; letter-spacing:-0.01em;">
                <i class="fa-solid fa-heart me-1" style="color:#1050d8; font-size:0.75rem;"></i>Lazada
            </button>
            <button type="button" class="filter-plat-btn btn btn-sm rounded-pill px-3 py-1 fw-semibold"
                data-platform="TikTok Shop" onclick="selectFilterPlatform('TikTok Shop')"
                style="font-size:0.8rem; white-space:nowrap; transition:all 0.2s; color:#64748b; letter-spacing:-0.01em;">
                <i class="fa-brands fa-tiktok me-1" style="color:#18181b; font-size:0.75rem;"></i>TikTok
            </button>
        </div>

        <!-- Date Range Picker — fixed width right -->
        <div class="d-flex align-items-center gap-2 px-4 flex-shrink-0" style="background:#fff; min-width:260px;">
            <i class="fa-regular fa-calendar-days" style="color:#94a3b8; font-size:0.88rem; flex-shrink:0;"></i>
            <div class="position-relative flex-grow-1">
                <input type="text" class="form-control border-0 p-0 fw-semibold font-mono" id="fDateRange"
                    placeholder="Select date range..."
                    readonly
                    style="cursor:pointer; background:transparent; color:#374151; font-size:0.82rem; box-shadow:none; padding-right:1.4rem;">
                <button class="btn border-0 p-0 d-none position-absolute" type="button" onclick="clearDateFilterAndApply()" id="btnClearDate"
                    style="right:0; top:50%; transform:translateY(-50%); line-height:1; background:none; color:#94a3b8;">
                    <i class="fa-solid fa-circle-xmark" style="font-size:0.82rem;"></i>
                </button>
            </div>
            <input type="hidden" id="fStart" value="">
            <input type="hidden" id="fEnd" value="">
        </div>

    </div>
</div>


<!-- Table Card -->
<div class="app-card overflow-hidden mb-4">
    <div class="card-header-modern flex-wrap gap-2">
        <div class="d-flex align-items-center gap-2">
            <h5 class="mb-0 fw-bold fs-6">Registered Invoices</h5>
            <span class="badge bg-secondary-subtle text-secondary rounded-pill" id="archiveCountBadge">0 records</span>
        </div>
        <div class="d-flex align-items-center gap-2">
            <button type="button" class="btn btn-sm btn-outline-primary rounded-pill px-3" onclick="selectAllOnCurrentPage(true)">Select Page</button>
            <button type="button" class="btn btn-sm btn-outline-secondary rounded-pill px-3" onclick="clearSelection()">Deselect All</button>
        </div>
    </div>
    <div class="table-responsive">
        <table class="table table-modern align-middle mb-0" style="font-size:0.84rem;">
            <thead>
                <tr>
                    <th width="40" class="text-center">
                        <input type="checkbox" class="form-check-input" id="thCheckAll" onchange="toggleSelectAllCurrentPage(this.checked)" title="Select all on this page">
                    </th>
                    <th width="135">Invoice #</th>
                    <th width="90">Platform</th>
                    <th width="145">Order SN</th>
                    <th>Buyer Name & TIN</th>
                    <th width="95">Date Issued</th>
                    <th width="100" class="text-end">Vatable Sales</th>
                    <th width="90" class="text-end">12% VAT</th>
                    <th width="100" class="text-end">Total Amount</th>
                    <th width="90" class="text-center">Actions</th>
                </tr>
            </thead>
            <tbody id="historyTbody">
                <tr>
                    <td colspan="10" class="text-center py-5 text-secondary">
                        <i class="fa-solid fa-spinner fa-spin fs-4 text-primary mb-2 d-block"></i> Loading invoice archive...
                    </td>
                </tr>
            </tbody>
        </table>
    </div>

    <!-- Pagination & Records Per Page Bar -->
    <div class="p-3 border-top d-flex flex-wrap justify-content-between align-items-center gap-3 bg-light-subtle">
        <div class="d-flex align-items-center gap-3">
            <span class="text-secondary small">
                Showing <strong id="pagFrom">0</strong> to <strong id="pagTo">0</strong> of <strong id="pagTotal">0</strong> records
            </span>
            <div class="d-flex align-items-center gap-1">
                <span class="text-secondary small">Per page:</span>
                <select class="form-select form-select-sm font-mono" id="perPageSelect" style="width: auto;" onchange="changePerPage(this.value)">
                    <option value="25">25</option>
                    <option value="50" selected>50</option>
                    <option value="100">100</option>
                    <option value="250">250</option>
                </select>
            </div>
        </div>

        <nav aria-label="Invoices pagination">
            <ul class="pagination pagination-sm mb-0 gap-1" id="paginationNav">
                <!-- Populated dynamically -->
            </ul>
        </nav>
    </div>
</div>

<div class="batch-floating-bar d-none" id="historyBatchBar">
    <div class="d-flex align-items-center gap-3">
        <div class="rounded-circle d-flex align-items-center justify-content-center" style="width:38px;height:38px;background:rgba(37,99,235,0.12);">
            <i class="fa-solid fa-check-double" style="color:#2563eb;"></i>
        </div>
        <div>
            <div class="fw-bold" id="batchBarCount">0 invoices selected</div>
            <div class="text-secondary small font-mono" id="batchBarTotal">Total: ₱0.00</div>
        </div>
    </div>
    <div class="d-flex align-items-center gap-2">
        <button type="button" class="btn btn-sm px-3 rounded-pill fw-semibold" style="background:rgba(255,255,255,0.15); color:#fff; border:1px solid rgba(255,255,255,0.3); backdrop-filter:blur(4px);" onclick="clearSelection()">
            <i class="fa-solid fa-xmark me-1"></i> Clear
        </button>
        <button type="button" class="btn btn-sm px-4 py-2 rounded-pill fw-bold" id="btnDownloadSelected" onclick="downloadSelectedZip()" style="background:linear-gradient(135deg,#2563eb,#1d4ed8); color:#fff; box-shadow:0 4px 12px rgba(37,99,235,0.35); border:none;">
            <i class="fa-solid fa-file-zipper me-2"></i> Download ZIP
        </button>
    </div>
</div>

<script>
let currentPage = 1;
let currentPerPage = 50;
let currentInvoices = [];
const selectedInvoices = new Map(); // id => { id, invoice_number, total_amount, pdf_filename }

document.addEventListener('DOMContentLoaded', () => {
    initDateRangePicker();
});

function initDateRangePicker() {
    if (typeof $.fn.daterangepicker === 'undefined') {
        // Retry after a short delay in case lib is still loading
        setTimeout(initDateRangePicker, 300);
        return;
    }

    const todayStr = moment().format('YYYY-MM-DD');

    $('#fDateRange').daterangepicker({
        autoUpdateInput: false,
        opens: 'left',
        drops: 'down',
        showDropdowns: true,
        linkedCalendars: false,
        alwaysShowCalendars: true,
        startDate: moment(),
        endDate: moment(),
        maxDate: moment(),
        ranges: {
            'Today':        [moment(), moment()],
            'Yesterday':    [moment().subtract(1, 'days'), moment().subtract(1, 'days')],
            'Last 7 Days':  [moment().subtract(6, 'days'), moment()],
            'Last 30 Days': [moment().subtract(29, 'days'), moment()],
            'This Month':   [moment().startOf('month'), moment().endOf('month')],
            'Last Month':   [moment().subtract(1, 'month').startOf('month'), moment().subtract(1, 'month').endOf('month')]
        },
        locale: {
            format: 'MMM D, YYYY',
            separator: ' – ',
            cancelLabel: 'Show All',
            applyLabel: 'Apply'
        }
    });

    // Apply today as default immediately and load records
    setDateFilter(todayStr, todayStr);
    loadHistory(1);

    $('#fDateRange').on('apply.daterangepicker', function(ev, picker) {
        const start = picker.startDate.format('YYYY-MM-DD');
        const end   = picker.endDate.format('YYYY-MM-DD');
        setDateFilter(start, end);
        applyFilters();
    });

    $('#fDateRange').on('cancel.daterangepicker', function() {
        clearDateFilterAndApply();
    });

    $('#fDateRange').on('show.daterangepicker', function() {
        $(this).closest('div').css('border-right-color', '#2563eb');
    });
    $('#fDateRange').on('hide.daterangepicker', function() {
        if (!$('#fStart').val()) {
            $(this).closest('div').css('border-right-color', '#e2e8f0');
        }
    });
}

function setDateFilter(start, end) {
    const startMoment = moment(start);
    const endMoment   = moment(end);
    let label;
    if (start === end) {
        label = startMoment.format('MMM D, YYYY');
        if (start === moment().format('YYYY-MM-DD')) label = 'Today · ' + label;
    } else {
        label = startMoment.format('MMM D') + ' – ' + endMoment.format('MMM D, YYYY');
    }
    $('#fDateRange').val(label);
    $('#fStart').val(start);
    $('#fEnd').val(end);
    $('#btnClearDate').removeClass('d-none');
}

function clearDateRange() {
    // Reset to today (not blank) — the default state is "today"
    const todayStr = moment().format('YYYY-MM-DD');
    setDateFilter(todayStr, todayStr);
}

function applyFilters() {
    currentPage = 1;
    // show/hide clear search button
    if ($('#fSearch').val().trim()) {
        $('#btnClearSearch').removeClass('d-none');
    } else {
        $('#btnClearSearch').addClass('d-none');
    }
    loadHistory(1);
}

function resetFilters() {
    $('#fSearch').val('');
    $('#btnClearSearch').addClass('d-none');
    $('#fPlatform').val('all');
    clearDateRange();
    selectFilterPlatform('all');
    currentPage = 1;
    loadHistory(1);
}

function clearSearchFilter() {
    $('#fSearch').val('');
    applyFilters();
}

function clearPlatformFilter() {
    $('#fPlatform').val('all');
    applyFilters();
}

function clearDateFilterAndApply() {
    clearDateRange();
    applyFilters();
}

function escapeHtml(text) {
    const map = { '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#039;' };
    return String(text).replace(/[&<>"']/g, m => map[m]);
}

function changePerPage(newVal) {
    currentPerPage = parseInt(newVal, 10) || 50;
    currentPage = 1;
    loadHistory(1);
}

function loadHistory(page) {
    currentPage = page || currentPage || 1;
    const s = $('#fSearch').val() || '';
    const plat = $('#fPlatform').val() || 'all';
    const start = $('#fStart').val() || '';
    const end = $('#fEnd').val() || '';

    const p = new URLSearchParams({
        page: currentPage,
        per_page: currentPerPage,
        search: s,
        platform: plat,
        start_date: start,
        end_date: end
    });

    const tbody = $('#historyTbody');
    tbody.html('<tr><td colspan="10" class="text-center py-5 text-secondary"><i class="fa-solid fa-spinner fa-spin fs-4 text-primary mb-2 d-block"></i> Loading invoices...</td></tr>');

    fetch(window.BASE_URL + 'api/history.php?' + p.toString())
    .then(r => r.json())
    .then(data => {
        if (data.success) {
            currentInvoices = data.invoices;

            // Update KPI cards
            $('#kpiCount').text(Number(data.kpi.total_invoices).toLocaleString());
            $('#kpiGross').text('₱' + Number(data.kpi.total_gross).toLocaleString('en-US', {minimumFractionDigits: 2}));
            $('#kpiVatable').text('₱' + Number(data.kpi.total_vatable).toLocaleString('en-US', {minimumFractionDigits: 2}));
            $('#kpiVat').text('₱' + Number(data.kpi.total_vat).toLocaleString('en-US', {minimumFractionDigits: 2}));
            $('#archiveCountBadge').text(Number(data.pagination.total_records).toLocaleString() + ' records');

            // Pagination info
            $('#pagFrom').text(data.pagination.from);
            $('#pagTo').text(data.pagination.to);
            $('#pagTotal').text(Number(data.pagination.total_records).toLocaleString());

            renderTableRows(data.invoices);
            renderPaginationNav(data.pagination);
            syncHeaderCheckbox();
            updateBatchFloatingBar();
        } else {
            tbody.html(`<tr><td colspan="10" class="text-center py-5 text-danger">${data.error}</td></tr>`);
        }
    })
    .catch(err => {
        tbody.html(`<tr><td colspan="10" class="text-center py-5 text-danger">${err.message}</td></tr>`);
    });
}

function updateActiveFilterSummary() {
    // Removed - no active filter summary bar shown
}

function renderTableRows(invoices) {
    const tbody = $('#historyTbody');
    tbody.empty();

    if (!invoices.length) {
        tbody.append('<tr><td colspan="10" class="text-center py-5 text-secondary"><i class="fa-solid fa-folder-open fs-3 text-muted mb-2 d-block"></i>No matching invoices found in archive.</td></tr>');
        return;
    }

    invoices.forEach(inv => {
        const isChecked = selectedInvoices.has(inv.id);
        const pdfUrl = window.BASE_URL + 'api/download.php?file=' + encodeURIComponent(inv.pdf_filename);
        const viewUrl = pdfUrl + '&view=1';

        const pName = inv.platform_name || 'Shopee';
        let platformBadge = '';
        if (pName === 'Lazada') {
            platformBadge = '<span class="badge rounded-pill px-2 py-1" style="background:#eff6ff; color:#1050d8; border:1px solid rgba(16,80,216,0.25); font-weight:600; font-size:0.75rem;"><i class="fa-solid fa-heart me-1" style="color:#1050d8;"></i>Lazada</span>';
        } else if (pName === 'TikTok' || pName === 'TikTok Shop') {
            platformBadge = '<span class="badge rounded-pill px-2 py-1" style="background:#f1f5f9; color:#0f172a; border:1px solid rgba(15,23,42,0.25); font-weight:600; font-size:0.75rem;"><i class="fa-brands fa-tiktok me-1" style="color:#0f172a;"></i>TikTok</span>';
        } else {
            platformBadge = '<span class="badge rounded-pill px-2 py-1" style="background:#fff1ee; color:#ee4d2d; border:1px solid rgba(238,77,45,0.25); font-weight:600; font-size:0.75rem;"><i class="fa-solid fa-bag-shopping me-1" style="color:#ee4d2d;"></i>Shopee</span>';
        }

        tbody.append(`
            <tr data-id="${inv.id}" class="${isChecked ? 'table-active' : ''}">
                <td class="text-center">
                    <input type="checkbox" class="form-check-input inv-row-chk" value="${inv.id}" ${isChecked ? 'checked' : ''} onchange="toggleInvoiceSelection(${inv.id}, this.checked)">
                </td>
                <td>
                    <span class="badge bg-primary-subtle text-primary font-mono fw-bold rounded-pill px-2 py-1" style="font-size:0.75rem;">
                        ${escapeHtml(inv.invoice_number)}
                    </span>
                </td>
                <td>${platformBadge}</td>
                <td class="font-mono fw-semibold text-dark">${escapeHtml(inv.order_sn)}</td>
                <td>
                    <div class="fw-bold">${escapeHtml(inv.buyer_name)}</div>
                    <div class="text-secondary font-mono text-nowrap" style="font-size:0.72rem;"><i class="fa-solid fa-id-card me-1"></i>TIN: ${escapeHtml(inv.buyer_tin)}</div>
                </td>
                <td class="text-secondary font-mono">${escapeHtml(inv.issue_date)}</td>
                <td class="text-end font-mono">₱${Number(inv.vatable_sales).toFixed(2)}</td>
                <td class="text-end font-mono fw-semibold" style="color:#b45309;">₱${Number(inv.vat_amount).toFixed(2)}</td>
                <td class="text-end font-mono fw-bold text-dark">₱${Number(inv.total_amount).toFixed(2)}</td>
                <td class="text-center">
                    <div class="action-btn-group">
                        <a href="${viewUrl}" target="_blank" class="action-btn action-btn-view" title="Preview / Print PDF">
                            <i class="fa-regular fa-eye"></i><span>View</span>
                        </a>
                        <a href="${pdfUrl}" class="action-btn action-btn-dl action-btn-icon-only" title="Download PDF File" download>
                            <i class="fa-solid fa-download"></i>
                        </a>
                    </div>
                </td>
            </tr>
        `);
    });
}

function renderPaginationNav(p) {
    const nav = $('#paginationNav');
    nav.empty();

    if (p.total_pages <= 1) return;

    // Previous Button
    nav.append(`
        <li class="page-item ${p.page <= 1 ? 'disabled' : ''}">
            <a class="page-link rounded-pill px-2" href="javascript:void(0)" onclick="loadHistory(${p.page - 1})" aria-label="Previous">
                <i class="fa-solid fa-chevron-left"></i>
            </a>
        </li>
    `);

    // Windowed Page Numbers
    const delta = 2;
    const range = [];
    for (let i = Math.max(2, p.page - delta); i <= Math.min(p.total_pages - 1, p.page + delta); i++) {
        range.push(i);
    }

    // Always include page 1
    nav.append(`
        <li class="page-item ${p.page === 1 ? 'active' : ''}">
            <a class="page-link rounded-pill px-3" href="javascript:void(0)" onclick="loadHistory(1)">1</a>
        </li>
    `);

    if (range.length && range[0] > 2) {
        nav.append('<li class="page-item disabled"><span class="page-link border-0">…</span></li>');
    }

    range.forEach(pg => {
        nav.append(`
            <li class="page-item ${p.page === pg ? 'active' : ''}">
                <a class="page-link rounded-pill px-3" href="javascript:void(0)" onclick="loadHistory(${pg})">${pg}</a>
            </li>
        `);
    });

    if (range.length && range[range.length - 1] < p.total_pages - 1) {
        nav.append('<li class="page-item disabled"><span class="page-link border-0">…</span></li>');
    }

    // Always include last page
    if (p.total_pages > 1) {
        nav.append(`
            <li class="page-item ${p.page === p.total_pages ? 'active' : ''}">
                <a class="page-link rounded-pill px-3" href="javascript:void(0)" onclick="loadHistory(${p.total_pages})">${p.total_pages}</a>
            </li>
        `);
    }

    // Next Button
    nav.append(`
        <li class="page-item ${p.page >= p.total_pages ? 'disabled' : ''}">
            <a class="page-link rounded-pill px-2" href="javascript:void(0)" onclick="loadHistory(${p.page + 1})" aria-label="Next">
                <i class="fa-solid fa-chevron-right"></i>
            </a>
        </li>
    `);
}

// Checkbox and Multi-Select Handlers
function toggleInvoiceSelection(id, checked) {
    const inv = currentInvoices.find(x => x.id == id);
    if (!inv) return;

    if (checked) {
        selectedInvoices.set(id, inv);
        $(`tr[data-id="${id}"]`).addClass('table-active');
    } else {
        selectedInvoices.delete(id);
        $(`tr[data-id="${id}"]`).removeClass('table-active');
    }

    syncHeaderCheckbox();
    updateBatchFloatingBar();
}

function toggleSelectAllCurrentPage(checked) {
    selectAllOnCurrentPage(checked);
}

function selectAllOnCurrentPage(check) {
    currentInvoices.forEach(inv => {
        if (check) {
            selectedInvoices.set(inv.id, inv);
            $(`tr[data-id="${inv.id}"]`).addClass('table-active');
            $(`.inv-row-chk[value="${inv.id}"]`).prop('checked', true);
        } else {
            selectedInvoices.delete(inv.id);
            $(`tr[data-id="${inv.id}"]`).removeClass('table-active');
            $(`.inv-row-chk[value="${inv.id}"]`).prop('checked', false);
        }
    });

    syncHeaderCheckbox();
    updateBatchFloatingBar();
}

function clearSelection() {
    selectedInvoices.clear();
    $('.inv-row-chk').prop('checked', false);
    $('tr').removeClass('table-active');
    syncHeaderCheckbox();
    updateBatchFloatingBar();
}

function syncHeaderCheckbox() {
    if (!currentInvoices.length) {
        $('#thCheckAll').prop('checked', false);
        return;
    }
    const allCheckedOnPage = currentInvoices.every(x => selectedInvoices.has(x.id));
    $('#thCheckAll').prop('checked', allCheckedOnPage);
}

function updateBatchFloatingBar() {
    const count = selectedInvoices.size;
    if (count > 0) {
        let total = 0;
        selectedInvoices.forEach(inv => { total += Number(inv.total_amount || 0); });
        $('#batchBarCount').text(`${count} invoice${count > 1 ? 's' : ''} selected`);
        $('#batchBarTotal').text(`Total: ₱${total.toLocaleString('en-US', {minimumFractionDigits: 2})}`);
        $('#historyBatchBar').removeClass('d-none');
    } else {
        $('#historyBatchBar').addClass('d-none');
    }
}

// Batch ZIP Download of Selected Invoices
function downloadSelectedZip() {
    const ids = Array.from(selectedInvoices.keys());
    if (!ids.length) {
        Swal.fire('No Selection', 'Please select at least one invoice to download.', 'warning');
        return;
    }

    Swal.fire({
        title: 'Packaging Invoices ZIP...',
        text: `Compiling ${ids.length} selected invoices into a single ZIP archive. Please wait...`,
        allowOutsideClick: false,
        didOpen: () => Swal.showLoading()
    });

    fetch(window.BASE_URL + 'api/download_batch.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ mode: 'selected', ids: ids })
    })
    .then(r => r.json())
    .then(data => {
        Swal.close();
        if (data.success) {
            Swal.fire({
                icon: 'success',
                title: 'ZIP Ready!',
                html: `Compiled <b>${data.count} invoices</b> into archive: <code>${data.zip_filename}</code>`,
                confirmButtonColor: '#ee4d2d',
                confirmButtonText: '<i class="fa-solid fa-download me-1"></i> Download Now'
            }).then(() => {
                window.location.href = data.download_url;
            });
        } else {
            Swal.fire('Error', data.error || 'Failed to generate ZIP', 'error');
        }
    })
    .catch(err => {
        Swal.close();
        Swal.fire('Error', err.message, 'error');
    });
}

// Download All Filtered Invoices in ZIP
function downloadAllFiltered() {
    const s = $('#fSearch').val() || '';
    const plat = $('#fPlatform').val() || 'all';
    const start = $('#fStart').val() || '';
    const end = $('#fEnd').val() || '';

    Swal.fire({
        title: 'Download All Filtered Invoices?',
        text: 'This will package all invoices matching your active search/filter criteria into a single ZIP package.',
        icon: 'question',
        showCancelButton: true,
        confirmButtonColor: '#ee4d2d',
        confirmButtonText: 'Yes, Package & Download'
    }).then(res => {
        if (!res.isConfirmed) return;

        Swal.fire({
            title: 'Packaging All Filtered Invoices...',
            text: 'Extracting and compiling PDFs into ZIP. This may take a few moments...',
            allowOutsideClick: false,
            didOpen: () => Swal.showLoading()
        });

        fetch(window.BASE_URL + 'api/download_batch.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({
                mode: 'filtered',
                search: s,
                platform: plat,
                start_date: start,
                end_date: end
            })
        })
        .then(r => r.json())
        .then(data => {
            Swal.close();
            if (data.success) {
                Swal.fire({
                    icon: 'success',
                    title: 'ZIP Ready!',
                    html: `Compiled <b>${data.count} invoices</b> into archive: <code>${data.zip_filename}</code>`,
                    confirmButtonColor: '#ee4d2d',
                    confirmButtonText: '<i class="fa-solid fa-download me-1"></i> Download Now'
                }).then(() => {
                    window.location.href = data.download_url;
                });
            } else {
                Swal.fire('Error', data.error || 'Failed to generate ZIP', 'error');
            }
        })
        .catch(err => {
            Swal.close();
            Swal.fire('Error', err.message, 'error');
        });
    });
}

// Export Filtered Records to CSV (BIR 2550Q Sales Book)
function exportCsv() {
    const s = $('#fSearch').val() || '';
    const plat = $('#fPlatform').val() || 'all';
    const start = $('#fStart').val() || '';
    const end = $('#fEnd').val() || '';

    const p = new URLSearchParams({
        search: s,
        platform: plat,
        start_date: start,
        end_date: end
    });

    window.location.href = window.BASE_URL + 'api/export_csv.php?' + p.toString();
}

function selectFilterPlatform(plat) {
    $('#fPlatform').val(plat);

    // Reset all to inactive state
    $('.filter-plat-btn').each(function() {
        const p = $(this).data('platform');
        $(this).attr('style', 'font-size:0.82rem; transition: all 0.18s;');
        $(this).css({ background: 'transparent', color: '#64748b', border: 'none', fontWeight: '600' });
    });

    // Activate selected
    const btn = $(`.filter-plat-btn[data-platform="${plat}"]`);
    btn.addClass('active');
    if (plat === 'Shopee') {
        btn.css({ background: '#fff1ee', color: '#ee4d2d', border: '1.5px solid rgba(238,77,45,0.25)' });
    } else if (plat === 'Lazada') {
        btn.css({ background: '#eff6ff', color: '#1050d8', border: '1.5px solid rgba(16,80,216,0.25)' });
    } else if (plat === 'TikTok Shop') {
        btn.css({ background: '#f1f5f9', color: '#18181b', border: '1.5px solid rgba(24,24,27,0.2)' });
    } else {
        btn.css({ background: '#ffffff', color: '#0f172a', border: '1.5px solid #e2e8f0', boxShadow: '0 1px 4px rgba(15,23,42,0.08)' });
    }

    applyFilters();
}
</script>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
