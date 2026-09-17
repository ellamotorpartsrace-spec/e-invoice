<?php
// index.php — Modern Multi-Platform Batch Invoice Generator (Shopee, Lazada, TikTok)
$pageTitle = 'Upload & Batch Generate';
require_once __DIR__ . '/includes/header.php';

$db = new Database();
$conn = $db->getConnection();
$settings = getEinvSettings($conn);
?>

<!-- Page Header Banner -->
<div class="d-flex flex-wrap justify-content-between align-items-center mb-4 gap-3">
    <div>
        <div class="d-flex align-items-center gap-2 mb-1">
            <h2 class="fw-bold mb-0" style="letter-spacing: -0.02em;">E-Commerce Batch E-Invoice Generator</h2>
            <span class="badge bg-primary-subtle text-primary border border-primary-subtle px-2 py-1 rounded-pill" style="font-size:0.75rem;">
                Annex A1 Multi-Platform
            </span>
        </div>
        <p class="text-secondary small mb-0">Upload completed orders spreadsheet (.xlsx) from <strong>Shopee, Lazada, or TikTok Shop</strong> to generate official BIR-compliant Sales Invoices.</p>
    </div>
    <div class="d-flex gap-2">
        <a href="<?= BASE_URL ?>history.php" class="btn btn-outline-secondary btn-sm px-3 rounded-pill fw-semibold shadow-xs">
            <i class="fa-solid fa-box-archive me-1"></i> View Invoices History
        </a>
    </div>
</div>

<!-- Platform Selector Tabs -->
<div class="d-flex justify-content-between align-items-center flex-wrap gap-3 mb-4">
    <div class="platform-tabs" id="platformTabs">
        <button type="button" class="platform-tab active" data-platform="Shopee" onclick="selectPlatform('Shopee', 'SI-SHP-<?= date('Y') ?>-')">
            <i class="fa-solid fa-bag-shopping"></i>
            <span>Shopee</span>
        </button>
        <button type="button" class="platform-tab" data-platform="Lazada" onclick="selectPlatform('Lazada', 'SI-LAZ-<?= date('Y') ?>-')">
            <i class="fa-solid fa-heart"></i>
            <span>Lazada</span>
        </button>
        <button type="button" class="platform-tab" data-platform="TikTok Shop" onclick="selectPlatform('TikTok Shop', 'SI-TT-<?= date('Y') ?>-')">
            <i class="fa-brands fa-tiktok"></i>
            <span>TikTok Shop</span>
        </button>
    </div>

    <div class="small text-secondary">
        <span class="badge bg-light text-secondary border px-2 py-1 rounded-pill">
            <i class="fa-solid fa-check-double text-success me-1"></i>Unified Auto-Detection Engine
        </span>
    </div>
</div>

<!-- Info & Tax Configuration Summary Strip -->
<div class="info-strip">
    <div class="info-item">
        <div class="info-icon bg-danger-subtle text-danger">
            <i class="fa-solid fa-building"></i>
        </div>
        <div>
            <div class="text-secondary small" style="font-size:0.7rem; font-weight:700; text-transform:uppercase;">Registered Business</div>
            <div class="fw-bold small"><?= htmlspecialchars($settings['store_name'] ?? 'DEMO E-COMMERCE ENTERPRISES') ?></div>
        </div>
    </div>
    <div class="info-item">
        <div class="info-icon bg-primary-subtle text-primary">
            <i class="fa-solid fa-hashtag"></i>
        </div>
        <div>
            <div class="text-secondary small" style="font-size:0.7rem; font-weight:700; text-transform:uppercase;">Store TIN</div>
            <div class="fw-bold font-mono small"><?= htmlspecialchars($settings['store_tin'] ?? '123-456-789-00000') ?></div>
        </div>
    </div>
    <div class="info-item">
        <div class="info-icon bg-success-subtle text-success">
            <i class="fa-solid fa-stamp"></i>
        </div>
        <div>
            <div class="text-secondary small" style="font-size:0.7rem; font-weight:700; text-transform:uppercase;">Tax Classification</div>
            <div class="fw-bold text-success small"><?= htmlspecialchars($settings['vat_status'] ?? 'VAT Registered') ?> (12%)</div>
        </div>
    </div>
    <div class="info-item">
        <div class="info-icon bg-info-subtle text-info">
            <i class="fa-solid fa-file-invoice"></i>
        </div>
        <div>
            <div class="text-secondary small" style="font-size:0.7rem; font-weight:700; text-transform:uppercase;">Active Prefix</div>
            <div class="fw-bold font-mono small" id="activePrefixDisplay"><?= htmlspecialchars($settings['invoice_prefix'] ?? 'SI-SHP-2026-') ?></div>
        </div>
    </div>
</div>

<!-- Step 1: Upload Dropzone Card -->
<div class="app-card mb-4" id="uploadCard" data-theme="Shopee">
    <div class="p-4 p-md-5">
        <div class="upload-dropzone" id="dropZone">
            <input type="file" id="excelInput" accept=".xlsx" onchange="handleExcelUpload(this)">
            
            <div class="upload-icon-wrapper">
                <div class="upload-icon-circle" id="dzCloudCircle">
                    <i class="fa-solid fa-cloud-arrow-up" id="dzCloudIcon"></i>
                </div>
            </div>

            <h3 class="fw-bold mb-2" id="dzTitle">Drag & Drop Shopee Completed Orders</h3>
            <p class="text-secondary mb-4" id="dzSubtitle" style="max-width: 540px; margin: 0 auto; font-size: 0.92rem;">
                Export your orders report from <strong>Shopee Seller Centre &gt; My Orders &gt; Export (.xlsx)</strong> and drop it here to automatically process.
            </p>

            <button type="button" class="btn btn-platform-action px-4 py-2 rounded-pill fw-bold fs-6 shadow-sm" id="btnSelectFile">
                <i class="fa-solid fa-folder-open me-2"></i> Select Shopee Orders (.xlsx)
            </button>

            <!-- Feature Chips -->
            <div class="feature-chips">
                <div class="feature-chip">
                    <i class="fa-solid fa-circle-check"></i>
                    <span>Multi-line order grouping</span>
                </div>
                <div class="feature-chip">
                    <i class="fa-solid fa-circle-check"></i>
                    <span>Real buyer name unmasking</span>
                </div>
                <div class="feature-chip">
                    <i class="fa-solid fa-circle-check"></i>
                    <span>Voucher & discount deduction</span>
                </div>
                <div class="feature-chip">
                    <i class="fa-solid fa-circle-check"></i>
                    <span>Official 12% VAT allocation</span>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- 3-Step Guide Section (Shown Before Upload) -->
<div class="row g-3 mb-4" id="guideSection">
    <div class="col-md-4">
        <div class="step-card">
            <div class="step-badge">1</div>
            <h6 class="fw-bold mb-1">Export from Seller Center</h6>
            <p class="text-secondary small mb-0" id="guideStep1">Download completed orders report (.xlsx) directly from Shopee, Lazada, or TikTok Shop Order Management.</p>
        </div>
    </div>
    <div class="col-md-4">
        <div class="step-card">
            <div class="step-badge">2</div>
            <h6 class="fw-bold mb-1">Drop & Auto-Parse</h6>
            <p class="text-secondary small mb-0">Our engine identifies buyer TIN, items, and accurately computes net vatable sales and 12% VAT amount.</p>
        </div>
    </div>
    <div class="col-md-4">
        <div class="step-card">
            <div class="step-badge">3</div>
            <h6 class="fw-bold mb-1">Generate & Download</h6>
            <p class="text-secondary small mb-0">Assign sequential BIR serial numbers, compile Annex A1 compliant PDFs, and download all in a ZIP package.</p>
        </div>
    </div>
</div>

<!-- Loading State Card -->
<div class="app-card mb-4 d-none" id="parsingCard">
    <div class="text-center py-5 px-3">
        <div class="spinner-border text-danger mb-3" style="width: 3.5rem; height: 3.5rem; border-width: 3.5px;" role="status"></div>
        <h4 class="fw-bold mb-2" id="loadingTitle">Parsing Orders Spreadsheet...</h4>
        <p class="text-secondary small mb-0" style="max-width: 450px; margin: 0 auto;">
            Extracting buyer tax information, grouping multi-item packages, and verifying against existing invoice records.
        </p>
    </div>
</div>

<!-- Step 2: Batch Review & Table Area (Hidden initially) -->
<div id="batchContainer" class="d-none">

    <!-- KPI Summary Row -->
    <div class="row g-3 mb-4">
        <div class="col-sm-6 col-xl-3">
            <div class="kpi-card">
                <div class="kpi-icon" style="background:var(--primary-light); color:var(--primary);">
                    <i class="fa-solid fa-cart-shopping"></i>
                </div>
                <div>
                    <div class="kpi-label">Orders in File</div>
                    <div class="kpi-value" id="kpiTotal">0</div>
                </div>
            </div>
        </div>
        <div class="col-sm-6 col-xl-3">
            <div class="kpi-card">
                <div class="kpi-icon" style="background:var(--success-bg); color:var(--success);">
                    <i class="fa-solid fa-bolt"></i>
                </div>
                <div>
                    <div class="kpi-label">Ready to Invoice</div>
                    <div class="kpi-value text-success" id="kpiReady">0</div>
                </div>
            </div>
        </div>
        <div class="col-sm-6 col-xl-3">
            <div class="kpi-card">
                <div class="kpi-icon" style="background:var(--info-bg); color:var(--info);">
                    <i class="fa-solid fa-peso-sign"></i>
                </div>
                <div>
                    <div class="kpi-label">Total Gross Sales</div>
                    <div class="kpi-value" id="kpiGross">₱0.00</div>
                </div>
            </div>
        </div>
        <div class="col-sm-6 col-xl-3">
            <div class="kpi-card">
                <div class="kpi-icon" style="background:rgba(217, 119, 6, 0.1); color:#d97706;">
                    <i class="fa-solid fa-coins"></i>
                </div>
                <div>
                    <div class="kpi-label">12% VAT Breakdown</div>
                    <div class="kpi-value" id="kpiVat" style="color:#b45309;">₱0.00</div>
                </div>
            </div>
        </div>
    </div>

    <!-- Options & Filter Toolbar -->
    <div class="app-card p-3 mb-4">
        <div class="row g-3 align-items-center">
            <div class="col-md-3">
                <label class="form-label small fw-bold text-secondary mb-1">Invoice Date</label>
                <input type="date" class="form-control form-control-sm" id="optIssueDate" value="<?= date('Y-m-d') ?>">
            </div>
            <div class="col-md-3">
                <label class="form-label small fw-bold text-secondary mb-1">Starting Serial Number</label>
                <div class="input-group input-group-sm">
                    <span class="input-group-text bg-white text-secondary font-mono border-end-0" id="prefixLabel"><?= htmlspecialchars($settings['invoice_prefix'] ?? 'SI-SHP-2026-') ?></span>
                    <input type="number" class="form-control form-control-sm font-mono fw-bold" id="optStartSeq" value="1" min="1">
                </div>
            </div>
            <div class="col-md-3">
                <label class="form-label small fw-bold text-secondary mb-1">Filter View</label>
                <select class="form-select form-select-sm" id="optFilter" onchange="renderOrdersTable()">
                    <option value="all" selected>Show All Orders</option>
                    <option value="new_only">New Orders (Uninvoiced)</option>
                    <option value="requested_only">Only Buyers with Invoice Info</option>
                </select>
            </div>
            <div class="col-md-3 text-md-end pt-md-3">
                <button type="button" class="btn btn-outline-secondary btn-sm px-3 rounded-pill" onclick="resetFile()">
                    <i class="fa-solid fa-arrow-rotate-left me-1"></i> Upload Another File
                </button>
            </div>
        </div>
    </div>

    <!-- Post-Generation Success Alert -->
    <div class="alert alert-success border-success-subtle shadow-sm rounded-4 p-4 mb-4 d-none" id="doneAlert">
        <div class="d-flex flex-wrap align-items-center justify-content-between gap-3">
            <div class="d-flex align-items-center gap-3">
                <div class="fs-1 text-success"><i class="fa-solid fa-circle-check"></i></div>
                <div>
                    <h5 class="fw-bold mb-1 text-success" id="doneTitle">Invoices Generated!</h5>
                    <p class="mb-0 small text-secondary">All PDF files are compiled and packaged into a ZIP archive ready for seller reporting.</p>
                </div>
            </div>
            <div>
                <a href="#" class="btn btn-success fw-bold px-4 py-2 rounded-pill shadow-sm" id="btnZipDownload">
                    <i class="fa-solid fa-file-zipper me-2"></i> Download All as ZIP
                </a>
            </div>
        </div>
    </div>

    <!-- Interactive Table -->
    <div class="app-card mb-4 overflow-hidden">
        <div class="card-header-modern flex-wrap gap-2">
            <div class="d-flex align-items-center gap-2">
                <h5 class="mb-0 fw-bold fs-6">Orders Extracted</h5>
                <span class="badge bg-secondary-subtle text-secondary rounded-pill" id="rowCountBadge">0 orders</span>
                <span class="badge bg-primary-subtle text-primary rounded-pill" id="platformCurrentBadge">Shopee</span>
            </div>
            <div class="d-flex gap-2">
                <button type="button" class="btn btn-sm btn-outline-primary rounded-pill px-3" onclick="checkAll(true)">Select All</button>
                <button type="button" class="btn btn-sm btn-outline-secondary rounded-pill px-3" onclick="checkAll(false)">Deselect All</button>
            </div>
        </div>
        <div class="table-responsive">
            <table class="table table-modern align-middle mb-0" style="font-size:0.84rem;">
                <thead>
                    <tr>
                        <th width="35" class="text-center">
                            <input type="checkbox" class="form-check-input" id="thCheck" onchange="checkAll(this.checked)" checked>
                        </th>
                        <th width="160">Order ID & Date</th>
                        <th width="200">Buyer Name</th>
                        <th width="150">Buyer TIN</th>
                        <th>Products / Variations</th>
                        <th width="120" class="text-end">Amount Due</th>
                        <th width="130" class="text-end">12% VAT</th>
                        <th width="110" class="text-center">Status</th>
                    </tr>
                </thead>
                <tbody id="ordersTbody">
                    <!-- Populated dynamically -->
                </tbody>
            </table>
        </div>
    </div>



    <!-- Sticky Floating Action Bar -->
    <div class="batch-floating-bar" id="actionFooter">
        <div>
            <span class="fw-bold" id="barCount">0 orders selected</span>
            <span class="text-muted mx-2">|</span>
            <span class="text-secondary small font-mono" id="barTotal">Total: ₱0.00</span>
        </div>
        <div>
            <button type="button" class="btn btn-generate-action px-4 py-2 rounded-pill fw-bold shadow-sm" id="btnRunGenerate" onclick="executeBatchGeneration()" disabled>
                <i class="fa-solid fa-file-invoice-dollar me-2"></i> Generate E-Invoices
            </button>
        </div>
    </div>

</div>

<script>
let parsedOrders = [];
let currentPlatform = 'Shopee';
let nextPrefix = '<?= addslashes($settings['invoice_prefix'] ?? 'SI-SHP-' . date('Y') . '-') ?>';

function selectPlatform(platform, defaultPrefix) {
    currentPlatform = platform;
    $('.platform-tab').removeClass('active');
    $(`.platform-tab[data-platform="${platform}"]`).addClass('active');

    // Dynamically update theme attribute for dropzone and cards
    $('#uploadCard').attr('data-theme', platform);
    $('#batchContainer').attr('data-theme', platform);

    $('#platformCurrentBadge').text(platform);

    const yr = new Date().getFullYear();
    let btnText = '';
    let btnIcon = 'fa-solid fa-folder-open';

    if (platform === 'Shopee') {
        nextPrefix = '<?= addslashes($settings['invoice_prefix_shopee'] ?? ($settings['invoice_prefix'] ?? 'SI-SHP-' . date('Y') . '-')) ?>';
        $('#dzTitle').html('Drag & Drop Shopee Completed Orders');
        $('#dzSubtitle').html('Export your orders report from <strong>Shopee Seller Centre &gt; My Orders &gt; Export (.xlsx)</strong> and drop it here.');
        btnText = 'Select Shopee Orders (.xlsx)';
        btnIcon = 'fa-solid fa-bag-shopping';
    } else if (platform === 'Lazada') {
        nextPrefix = '<?= addslashes($settings['invoice_prefix_lazada'] ?? 'SI-LAZ-' . date('Y') . '-') ?>';
        $('#dzTitle').html('Drag & Drop Lazada Completed Orders');
        $('#dzSubtitle').html('Export your orders report from <strong>Lazada Seller Center &gt; Orders &gt; Export (.xlsx)</strong> and drop it here.');
        btnText = 'Select Lazada Orders (.xlsx)';
        btnIcon = 'fa-solid fa-heart';
    } else if (platform === 'TikTok Shop' || platform === 'TikTok') {
        nextPrefix = '<?= addslashes($settings['invoice_prefix_tiktok'] ?? 'SI-TT-' . date('Y') . '-') ?>';
        $('#dzTitle').html('Drag & Drop TikTok Shop Completed Orders');
        $('#dzSubtitle').html('Export your orders report from <strong>TikTok Shop Seller Center &gt; Orders &gt; Manage Orders &gt; Export (.xlsx)</strong> and drop it here.');
        btnText = 'Select TikTok Shop Orders (.xlsx)';
        btnIcon = 'fa-brands fa-tiktok';
    }

    $('#btnSelectFile').html(`<i class="${btnIcon} me-2"></i> ${btnText}`);

    // Subtle micro-pulse animation on cloud icon circle when switching
    $('#dzCloudCircle').addClass('rotate-pulse');
    setTimeout(() => $('#dzCloudCircle').removeClass('rotate-pulse'), 450);

    if (defaultPrefix) nextPrefix = defaultPrefix;

    $('#prefixLabel').text(nextPrefix);
    $('#activePrefixDisplay').text(nextPrefix);
}

const dz = document.getElementById('dropZone');
['dragenter', 'dragover'].forEach(e => dz.addEventListener(e, ev => { ev.preventDefault(); dz.classList.add('dragover'); }));
['dragleave', 'drop'].forEach(e => dz.addEventListener(e, ev => { ev.preventDefault(); dz.classList.remove('dragover'); }));
dz.addEventListener('drop', ev => {
    if (ev.dataTransfer.files && ev.dataTransfer.files.length) {
        uploadFile(ev.dataTransfer.files[0]);
    }
});

function handleExcelUpload(inp) {
    if (inp.files && inp.files[0]) {
        uploadFile(inp.files[0]);
    }
}

function uploadFile(file) {
    if (!file.name.endsWith('.xlsx')) {
        Swal.fire('Invalid File', 'Please select a valid .xlsx Excel spreadsheet.', 'warning');
        return;
    }

    const fd = new FormData();
    fd.append('excel_file', file);
    fd.append('platform', currentPlatform);

    $('#uploadCard').addClass('d-none');
    $('#guideSection').addClass('d-none');
    $('#parsingCard').removeClass('d-none');
    $('#loadingTitle').text(`Parsing ${currentPlatform} Orders Spreadsheet...`);
    $('#batchContainer').addClass('d-none');
    $('#doneAlert').addClass('d-none');
    $('#actionFooter').removeClass('d-none');

    fetch(window.BASE_URL + 'api/parse_excel.php', {
        method: 'POST',
        body: fd
    })
    .then(r => r.json())
    .then(data => {
        $('#parsingCard').addClass('d-none');
        if (data.success) {
            const detectedPlatform = data.platform || currentPlatform;

            // Platform mismatch warning
            if (data.platform && data.platform !== currentPlatform) {
                $('#uploadCard').removeClass('d-none');
                $('#guideSection').removeClass('d-none');
                Swal.fire({
                    icon: 'error',
                    title: 'Wrong Platform File!',
                    html: `You have <strong>${currentPlatform}</strong> selected, but this Excel file contains <strong>${data.platform}</strong> orders.<br><br>Please switch to the <strong>${data.platform}</strong> tab and upload again.`,
                    confirmButtonText: `Switch to ${data.platform}`,
                    showCancelButton: true,
                    cancelButtonText: 'Cancel',
                    confirmButtonColor: '#0f172a'
                }).then(result => {
                    if (result.isConfirmed) {
                        selectPlatform(data.platform);
                    }
                });
                return;
            }
            parsedOrders = data.orders.map(o => {
                o.platform_name = data.platform || currentPlatform;
                return o;
            });

            nextPrefix = data.prefix || nextPrefix;
            $('#prefixLabel').text(nextPrefix);
            $('#activePrefixDisplay').text(nextPrefix);
            $('#optStartSeq').val(data.next_seq || 1);

            $('#kpiTotal').text(data.total_orders);
            $('#kpiReady').text(data.ready_count);
            $('#kpiGross').text('₱' + Number(data.total_amount).toLocaleString('en-US', {minimumFractionDigits: 2}));
            $('#kpiVat').text('₱' + Number(data.total_vat).toLocaleString('en-US', {minimumFractionDigits: 2}));

            // If no new orders, auto-switch filter to show all so user isn't left with blank table
            if (data.ready_count === 0) {
                $('#optFilter').val('all');
            } else {
                $('#optFilter').val('new_only');
            }

            renderOrdersTable();
            $('#batchContainer').removeClass('d-none');
        } else {
            $('#uploadCard').removeClass('d-none');
            $('#guideSection').removeClass('d-none');
            Swal.fire('Upload Failed', data.error || 'Could not parse Excel file', 'error');
        }
    })
    .catch(err => {
        $('#parsingCard').addClass('d-none');
        $('#uploadCard').removeClass('d-none');
        $('#guideSection').removeClass('d-none');
        Swal.fire('Error', err.message, 'error');
    });
}

function renderOrdersTable() {
    const filter = $('#optFilter').val();
    const tbody = $('#ordersTbody');
    tbody.empty();

    let list = parsedOrders;
    if (filter === 'new_only') {
        list = parsedOrders.filter(o => !o.is_generated);
    } else if (filter === 'requested_only') {
        list = parsedOrders.filter(o => o.buyer_tin !== '000-000-000-00000' || o.invoice_request_type === 'Requested Invoice');
    }

    $('#rowCountBadge').text(list.length + ' orders');

    if (!list.length) {
        tbody.append(`<tr><td colspan="8" class="text-center py-5 text-secondary"><i class="fa-solid fa-inbox fs-3 mb-2 d-block"></i>No orders match this filter view.</td></tr>`);
        recalcSelection();
        return;
    }

    list.forEach(ord => {
        const isChk = 'checked';
        let badge = '';
        if (ord.is_generated) {
            const pdfUrl = window.BASE_URL + 'storage/invoices/' + encodeURIComponent(ord.pdf_filename);
            badge = `<div>
                <span class="badge bg-secondary-subtle text-secondary font-mono rounded-pill px-2 py-1 mb-1" style="font-size:0.7rem;"><i class="fa-solid fa-check me-1"></i>${ord.existing_invoice_number}</span>
                <a href="${pdfUrl}" target="_blank" class="btn btn-outline-danger btn-xs rounded-pill d-inline-block py-0 px-2" style="font-size:0.68rem;"><i class="fa-regular fa-file-pdf me-1"></i>PDF</a>
            </div>`;
        } else {
            badge = `<span class="badge bg-success-subtle text-success rounded-pill px-2 py-1" style="font-size:0.7rem;"><i class="fa-solid fa-bolt me-1"></i>Ready</span>`;
        }

        let itemText = '';
        if (ord.items && ord.items.length) {
            itemText = ord.items[0].product_name;
            if (ord.items[0].variation_name) itemText += ` <span class="text-secondary">[${ord.items[0].variation_name}]</span>`;
            if (ord.items.length > 1) {
                itemText += ` <span class="badge bg-light text-secondary border ms-1">+${ord.items.length - 1} more</span>`;
            }
        }

        tbody.append(`
            <tr data-sn="${ord.order_sn}" class="${ord.is_generated ? 'opacity-75' : ''}">
                <td class="text-center">
                    <input type="checkbox" class="form-check-input row-chk" data-sn="${ord.order_sn}" value="${ord.order_sn}" ${isChk} onchange="recalcSelection()">
                </td>
                <td>
                    <div class="fw-bold font-mono text-dark">${ord.order_sn}</div>
                    <div class="text-secondary" style="font-size:0.7rem;"><i class="fa-regular fa-clock me-1"></i>${ord.order_date ? ord.order_date.substring(0, 16) : ''}</div>
                </td>
                <td>
                    <input type="text" class="table-editable-input fw-bold" value="${ord.buyer_name || ''}" onchange="updateBuyer('${ord.order_sn}', 'buyer_name', this.value)" placeholder="Enter Buyer Name">
                    <div class="text-secondary ms-1" style="font-size:0.68rem;">@${ord.buyer_username || ''}</div>
                </td>
                <td>
                    <input type="text" class="table-editable-input font-mono" value="${ord.buyer_tin || '000-000-000-00000'}" onchange="updateBuyer('${ord.order_sn}', 'buyer_tin', this.value)">
                </td>
                <td>
                    <div style="max-width:260px;" class="text-truncate">${itemText}</div>
                    ${ord.discount_amount > 0 ? `<span class="badge bg-danger-subtle text-danger border border-danger-subtle rounded-pill mt-1" style="font-size:0.65rem;">Voucher: -₱${Number(ord.discount_amount).toFixed(2)}</span>` : ''}
                </td>
                <td class="text-end fw-bold font-mono">₱${Number(ord.grand_total).toLocaleString('en-US', {minimumFractionDigits: 2})}</td>
                <td class="text-end font-mono text-secondary" style="font-size:0.75rem;">
                    <div>Net: ₱${Number(ord.vatable_sales).toFixed(2)}</div>
                    <div class="fw-semibold" style="color:#b45309;">VAT: ₱${Number(ord.vat_amount).toFixed(2)}</div>
                </td>
                <td class="text-center">${badge}</td>
            </tr>
        `);
    });

    recalcSelection();
}

function updateBuyer(sn, field, val) {
    const snStr = String(sn);
    const o = parsedOrders.find(x => String(x.order_sn) === snStr);
    if (o) o[field] = val;
}

function checkAll(val) {
    $('.row-chk').prop('checked', val);
    $('#thCheck').prop('checked', val);
    recalcSelection();
}

function recalcSelection() {
    const checked = $('.row-chk:checked');
    const count = checked.length;
    let sum = 0;

    checked.each(function() {
        const sn = String($(this).attr('data-sn') || $(this).val() || '');
        const o = parsedOrders.find(x => String(x.order_sn) === sn);
        if (o) sum += Number(o.grand_total || 0);
    });

    $('#barCount').text(`${count} orders selected`);
    $('#barTotal').text(`Total: ₱${sum.toLocaleString('en-US', {minimumFractionDigits: 2})}`);
    $('#btnRunGenerate').prop('disabled', count === 0);
}

function executeBatchGeneration() {
    const checked = $('.row-chk:checked');
    if (!checked.length) {
        Swal.fire('No Orders Selected', 'Please select at least one order to generate.', 'warning');
        return;
    }

    const selectedSns = [];
    checked.each(function() { 
        const sn = String($(this).attr('data-sn') || $(this).val() || '');
        if (sn) selectedSns.push(sn); 
    });

    const targetOrders = parsedOrders.filter(o => selectedSns.includes(String(o.order_sn)));
    if (!targetOrders.length) {
        Swal.fire('Selection Error', 'Could not locate the selected orders. Please refresh and try again.', 'error');
        return;
    }

    const issueDate = $('#optIssueDate').val() || '<?= date('Y-m-d') ?>';
    const startSeq = $('#optStartSeq').val() || 1;

    const confirmColor = currentPlatform === 'Shopee' ? '#ee4d2d' : (currentPlatform === 'Lazada' ? '#1050d8' : '#0f172a');
    Swal.fire({
        title: 'Generate Invoices?',
        html: `Generating <b>${targetOrders.length} ${currentPlatform} BIR Annex A1 Sales Invoices</b>.<br>Sequential serial numbers will be assigned automatically.`,
        icon: 'question',
        showCancelButton: true,
        confirmButtonColor: confirmColor,
        confirmButtonText: 'Yes, Generate Now'
    }).then(res => {
        if (!res.isConfirmed) return;

        Swal.fire({
            title: `Generating ${currentPlatform} Invoices...`,
            text: 'Compiling PDF invoices and ZIP archive. Please wait...',
            allowOutsideClick: false,
            didOpen: () => Swal.showLoading()
        });

        fetch(window.BASE_URL + 'api/generate.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({
                orders: targetOrders,
                platform: currentPlatform,
                issue_date: issueDate,
                starting_seq: startSeq,
                prefix: nextPrefix
            })
        })
        .then(r => r.json())
        .then(data => {
            Swal.close();
            if (data.success) {
                data.invoices.forEach(inv => {
                    const o = parsedOrders.find(x => String(x.order_sn) === String(inv.order_sn));
                    if (o) {
                        o.is_generated = true;
                        o.existing_invoice_number = inv.invoice_number;
                        o.pdf_filename = inv.pdf_filename;
                    }
                });

                $('#doneTitle').text(`${data.generated_count} ${currentPlatform} Invoices Generated Successfully!`);
                if (data.zip_download_url) {
                    $('#btnZipDownload').attr('href', data.zip_download_url).removeClass('d-none');
                } else {
                    $('#btnZipDownload').addClass('d-none');
                }
                $('#doneAlert').removeClass('d-none');
                $('#actionFooter').addClass('d-none');

                renderOrdersTable();
                Swal.fire({
                    icon: 'success',
                    title: 'Complete!',
                    text: `${data.generated_count} ${currentPlatform} invoices generated and ready to download.`,
                    confirmButtonColor: confirmColor
                });
            } else {
                Swal.fire('Failed', data.error || 'Generation failed', 'error');
            }
        })
        .catch(err => {
            Swal.fire('Error', err.message, 'error');
        });
    });
}

function resetFile() {
    $('#excelInput').val('');
    $('#batchContainer').addClass('d-none');
    $('#doneAlert').addClass('d-none');
    $('#uploadCard').removeClass('d-none');
    $('#guideSection').removeClass('d-none');
    parsedOrders = [];
}
</script>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
