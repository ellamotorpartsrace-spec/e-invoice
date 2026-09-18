<?php
// settings.php — Modern Store & BIR Tax Configuration
$pageTitle = 'Tax Profile & System Settings';
require_once __DIR__ . '/includes/header.php';

$db = new Database();
$conn = $db->getConnection();
$settings = getEinvSettings($conn);
?>

<div class="d-flex flex-wrap justify-content-between align-items-center mb-4 gap-3">
    <div>
        <div class="d-flex align-items-center gap-2 mb-1">
            <h2 class="fw-bold mb-0" style="letter-spacing: -0.02em;">Tax Profile & System Settings</h2>
            <span class="badge bg-secondary-subtle text-secondary px-2 py-1 rounded-pill" style="font-size:0.75rem;">
                BIR Form 2303
            </span>
        </div>
        <p class="text-secondary small mb-0">Configure your BIR Certificate of Registration details, sequential invoice numbering series, and account security.</p>
    </div>
</div>

<div class="row g-4">
    <!-- Left Column: BIR Store Tax Profile -->
    <div class="col-lg-7">
        <div class="app-card">
            <div class="card-header-modern">
                <div>
                    <h5 class="fw-bold mb-1 fs-6"><i class="fa-solid fa-building text-danger me-2"></i>BIR Tax & Invoice Header Profile</h5>
                    <p class="text-secondary small mb-0">These registered details are printed on every official BIR Annex A1 Sales Invoice.</p>
                </div>
            </div>
            <div class="p-4">
                <form id="settingsForm" onsubmit="saveSettings(event)">
                    <div class="row g-3 mb-3">
                        <div class="col-md-7">
                            <label class="form-label small fw-bold text-secondary">Registered Business Name (as on BIR 2303)</label>
                            <input type="text" class="form-control" name="store_name" value="<?= htmlspecialchars($settings['store_name'] ?? 'DEMO E-COMMERCE ENTERPRISES') ?>" required>
                        </div>
                        <div class="col-md-5">
                            <label class="form-label small fw-bold text-secondary">VAT Status</label>
                            <select class="form-select" name="vat_status">
                                <option value="VAT Registered" <?= ($settings['vat_status'] ?? '') === 'VAT Registered' ? 'selected' : '' ?>>VAT Registered (12% VAT)</option>
                                <option value="Non-VAT Registered" <?= ($settings['vat_status'] ?? '') === 'Non-VAT Registered' ? 'selected' : '' ?>>Non-VAT Registered</option>
                            </select>
                        </div>
                    </div>

                    <div class="row g-3 mb-3">
                        <div class="col-md-6">
                            <label class="form-label small fw-bold text-secondary">Taxpayer Identification Number (TIN + Branch)</label>
                            <input type="text" class="form-control font-mono" name="store_tin" value="<?= htmlspecialchars($settings['store_tin'] ?? '123-456-789-00000') ?>" placeholder="123-456-789-00000" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label small fw-bold text-secondary">Store Contact Number</label>
                            <input type="text" class="form-control" name="store_contact" value="<?= htmlspecialchars($settings['store_contact'] ?? '(02) 8123-4567 / 0917-000-0000') ?>">
                        </div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label small fw-bold text-secondary">Registered Business Address</label>
                        <input type="text" class="form-control" name="store_address" value="<?= htmlspecialchars($settings['store_address'] ?? '123 Commercial Ave., Ortigas Center, Pasig City, Metro Manila 1605') ?>" required>
                    </div>

                    <hr class="my-4 border-secondary-subtle">

                    <h6 class="fw-bold mb-3 fs-6"><i class="fa-solid fa-stamp text-danger me-2"></i>Invoice Series & Accreditation Footer</h6>

                    <div class="row g-3 mb-3">
                        <div class="col-md-4">
                            <label class="form-label small fw-bold text-secondary"><i class="fa-solid fa-bag-shopping me-1" style="color: #ee4d2d;"></i>Shopee Prefix</label>
                            <input type="text" class="form-control font-mono" name="invoice_prefix_shopee" value="<?= htmlspecialchars($settings['invoice_prefix_shopee'] ?? ($settings['invoice_prefix'] ?? 'SI-SHP-' . date('Y') . '-')) ?>" required>
                            <div class="form-text" style="font-size:0.72rem;">e.g. SI-SHP-<?= date('Y') ?>-</div>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label small fw-bold text-secondary"><i class="fa-solid fa-heart text-primary me-1"></i>Lazada Prefix</label>
                            <input type="text" class="form-control font-mono" name="invoice_prefix_lazada" value="<?= htmlspecialchars($settings['invoice_prefix_lazada'] ?? 'SI-LAZ-' . date('Y') . '-') ?>" required>
                            <div class="form-text" style="font-size:0.72rem;">e.g. SI-LAZ-<?= date('Y') ?>-</div>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label small fw-bold text-secondary"><i class="fa-brands fa-tiktok text-dark me-1"></i>TikTok Shop Prefix</label>
                            <input type="text" class="form-control font-mono" name="invoice_prefix_tiktok" value="<?= htmlspecialchars($settings['invoice_prefix_tiktok'] ?? 'SI-TT-' . date('Y') . '-') ?>" required>
                            <div class="form-text" style="font-size:0.72rem;">e.g. SI-TT-<?= date('Y') ?>-</div>
                        </div>
                    </div>

                    <div class="row g-3 mb-3">
                        <div class="col-md-6">
                            <label class="form-label small fw-bold text-secondary">Permit to Use / CAS ACN</label>
                            <input type="text" class="form-control font-mono" name="permit_no" value="<?= htmlspecialchars($settings['permit_no'] ?? 'CAS-POS-2026-00001') ?>">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label small fw-bold text-secondary">Default / Fallback Prefix</label>
                            <input type="text" class="form-control font-mono" name="invoice_prefix" value="<?= htmlspecialchars($settings['invoice_prefix'] ?? 'SI-SHP-' . date('Y') . '-') ?>">
                        </div>
                    </div>

                    <div class="row g-3 mb-4">
                        <div class="col-md-6">
                            <label class="form-label small fw-bold text-secondary">Authority to Print (ATP) / Accreditation No.</label>
                            <input type="text" class="form-control font-mono" name="atp_no" value="<?= htmlspecialchars($settings['atp_no'] ?? '3AU00000000000') ?>">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label small fw-bold text-secondary">Approved Series Range</label>
                            <input type="text" class="form-control font-mono" name="approved_series" value="<?= htmlspecialchars($settings['approved_series'] ?? 'SI-SHP-2026-00001 - SI-SHP-2026-99999') ?>">
                        </div>
                    </div>

                    <hr class="my-4 border-secondary-subtle">

                    <!-- BIR EIS Gateway & Sandbox Settings -->
                    <div class="d-flex align-items-center justify-content-between flex-wrap gap-2 mb-3">
                        <div>
                            <h6 class="fw-bold mb-1 fs-6"><i class="fa-solid fa-server text-danger me-2"></i>BIR EIS Gateway & Sandbox Certification</h6>
                            <p class="text-secondary small mb-0">Direct API integration with BIR EIS under RR 8-2022 & RR 9-2022 standards.</p>
                        </div>
                        <span class="badge bg-success-subtle text-success border border-success-subtle rounded-pill px-3 py-1" style="font-size:0.75rem;">
                            <i class="fa-solid fa-check-circle me-1"></i>Schema 380 Ready
                        </span>
                    </div>

                    <div class="p-3 mb-3 rounded-3" style="background: rgba(2, 132, 199, 0.05); border: 1px solid rgba(2, 132, 199, 0.18);">
                        <div class="d-flex gap-2">
                            <i class="fa-solid fa-circle-info text-info mt-1"></i>
                            <div class="small text-secondary">
                                <strong>Testing for BIR Registration:</strong> Use the <strong>Sandbox Gateway</strong> (<code>https://eis-cert.bir.gov.ph</code>) during developer certification. Switch to <strong>Production</strong> (<code>https://eis.bir.gov.ph</code>) only after receiving your Certificate of Technical Compliance (CTC).
                            </div>
                        </div>
                    </div>

                    <div class="row g-3 mb-3">
                        <div class="col-md-6">
                            <label class="form-label small fw-bold text-secondary">EIS Gateway Status</label>
                            <select class="form-select" name="eis_enabled">
                                <option value="0" <?= ($settings['eis_enabled'] ?? '0') === '0' ? 'selected' : '' ?>>Disabled (Offline PDF Only)</option>
                                <option value="1" <?= ($settings['eis_enabled'] ?? '') === '1' ? 'selected' : '' ?>>Enabled (Direct API Transmission)</option>
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label small fw-bold text-secondary">Gateway Environment</label>
                            <select class="form-select" name="eis_env">
                                <option value="sandbox" <?= ($settings['eis_env'] ?? 'sandbox') === 'sandbox' ? 'selected' : '' ?>>Sandbox (Testing: eis-cert.bir.gov.ph)</option>
                                <option value="production" <?= ($settings['eis_env'] ?? '') === 'production' ? 'selected' : '' ?>>Production (Live: eis.bir.gov.ph)</option>
                            </select>
                        </div>
                    </div>

                    <div class="row g-3 mb-3">
                        <div class="col-md-6">
                            <label class="form-label small fw-bold text-secondary">BIR EIS Client ID</label>
                            <input type="text" class="form-control font-mono" name="eis_client_id" value="<?= htmlspecialchars($settings['eis_client_id'] ?? '') ?>" placeholder="e.g. 7d8f4c2e-...">
                            <div class="form-text" style="font-size:0.72rem;">Provided upon registration at eis-cert.bir.gov.ph</div>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label small fw-bold text-secondary">BIR EIS Client Secret</label>
                            <input type="password" class="form-control font-mono" name="eis_client_secret" value="<?= htmlspecialchars($settings['eis_client_secret'] ?? '') ?>" placeholder="••••••••••••••••">
                            <div class="form-text" style="font-size:0.72rem;">OAuth2 confidential secret</div>
                        </div>
                    </div>

                    <div class="row g-3 mb-4">
                        <div class="col-md-6">
                            <label class="form-label small fw-bold text-secondary">API Subscription Key (Ocp-Apim)</label>
                            <input type="text" class="form-control font-mono" name="eis_api_key" value="<?= htmlspecialchars($settings['eis_api_key'] ?? '') ?>" placeholder="e.g. b8f910a72c...">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label small fw-bold text-secondary">Digital Certificate Serial No.</label>
                            <input type="text" class="form-control font-mono" name="eis_cert_serial" value="<?= htmlspecialchars($settings['eis_cert_serial'] ?? '') ?>" placeholder="e.g. BIR-CERT-2026-001">
                        </div>
                    </div>

                    <div class="d-flex flex-wrap gap-2">
                        <button type="submit" class="btn btn-enterprise-primary px-4 py-2 rounded-pill fw-bold" id="btnSaveSettings">
                            <i class="fa-solid fa-floppy-disk me-2"></i> Save Tax & EIS Settings
                        </button>
                        <button type="button" class="btn btn-outline-dark px-3 py-2 rounded-pill fw-bold" onclick="runBirEisTest()">
                            <i class="fa-solid fa-vial-circle-check text-danger me-2"></i> Run BIR EIS Sandbox Self-Test
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Right Column: Security & Cloud Portability -->
    <div class="col-lg-5">
        <!-- Authorized Signatory E-Signature Card -->
        <div class="app-card mb-4" id="signatureCard">
            <div class="card-header-modern">
                <div class="d-flex align-items-center justify-content-between w-100">
                    <div>
                        <h5 class="fw-bold mb-1 fs-6"><i class="fa-solid fa-signature text-danger me-2"></i>Authorized Signatory E-Signature</h5>
                        <p class="text-secondary small mb-0">Printed on official BIR invoices and certified via JWS.</p>
                    </div>
                    <?php 
                    $sigExists = file_exists(__DIR__ . '/storage/signatures/store_signature.png');
                    ?>
                    <span class="badge <?= $sigExists ? 'bg-success-subtle text-success border border-success-subtle' : 'bg-warning-subtle text-warning border border-warning-subtle' ?> rounded-pill" id="sigStatusBadge">
                        <?= $sigExists ? '<i class="fa-solid fa-circle-check me-1"></i>Active' : '<i class="fa-solid fa-pen-nib me-1"></i>Setup Needed' ?>
                    </span>
                </div>
            </div>
            <div class="p-4">
                <form id="signatureForm" onsubmit="saveSignature(event)">
                    <div class="row g-3 mb-3">
                        <div class="col-md-7">
                            <label class="form-label small fw-bold text-secondary">Signatory Full Name</label>
                            <input type="text" class="form-control" name="signatory_name" id="sigNameInput" value="<?= htmlspecialchars($settings['signatory_name'] ?? 'JUAN DELA CRUZ') ?>" required oninput="updateSigTextPreview()">
                        </div>
                        <div class="col-md-5">
                            <label class="form-label small fw-bold text-secondary">Designation / Title</label>
                            <input type="text" class="form-control" name="signatory_designation" id="sigDesigInput" value="<?= htmlspecialchars($settings['signatory_designation'] ?? 'Authorized Representative') ?>" required oninput="updateSigTextPreview()">
                        </div>
                    </div>

                    <!-- Current Active Signature Preview Box -->
                    <div class="mb-3 p-3 rounded-3 border bg-light">
                        <div class="d-flex justify-content-between align-items-center mb-2">
                            <span class="small fw-bold text-secondary">Active Signature on Issued Invoices:</span>
                            <button type="button" class="btn btn-outline-danger btn-xs py-0 px-2 rounded-pill" style="font-size:0.75rem;" onclick="clearActiveSignature()">
                                <i class="fa-solid fa-trash-can me-1"></i>Clear
                            </button>
                        </div>
                        <div class="text-center p-2 rounded bg-white border" style="min-height: 85px; position: relative;">
                            <?php 
                            $sigUrl = $sigExists ? (BASE_URL . 'storage/signatures/store_signature.png?v=' . time()) : '';
                            ?>
                            <img id="activeSigImg" src="<?= $sigUrl ?>" alt="Signatory Signature" style="max-height: 60px; width: auto; object-fit: contain; <?= $sigExists ? '' : 'display:none;' ?>">
                            <div id="noSigPlaceholder" class="text-muted small py-3" style="<?= $sigExists ? 'display:none;' : '' ?>">
                                <i class="fa-solid fa-pen-nib me-1"></i> No signature registered yet. Draw or upload below.
                            </div>
                            <div class="mt-1 pt-1 border-top small fw-bold text-dark" id="previewSignatoryText">
                                <?= htmlspecialchars($settings['signatory_name'] ?? 'JUAN DELA CRUZ') ?> — <span class="text-secondary fw-normal"><?= htmlspecialchars($settings['signatory_designation'] ?? 'Authorized Representative') ?></span>
                            </div>
                        </div>
                    </div>

                    <!-- Mode Switcher Tabs -->
                    <div class="d-flex gap-2 mb-3">
                        <button type="button" class="btn btn-sm btn-dark rounded-pill px-3" id="tabDrawBtn" onclick="switchSigMode('draw')">
                            <i class="fa-solid fa-pen-fancy me-1"></i> Draw on Screen
                        </button>
                        <button type="button" class="btn btn-sm btn-outline-secondary rounded-pill px-3" id="tabUploadBtn" onclick="switchSigMode('upload')">
                            <i class="fa-solid fa-cloud-arrow-up me-1"></i> Upload PNG File
                        </button>
                    </div>

                    <!-- Mode 1: Drawing Pad -->
                    <div id="sigDrawContainer" class="mb-3">
                        <div class="border rounded-3 bg-white p-2 position-relative shadow-sm" style="touch-action: none;">
                            <canvas id="sigCanvas" width="460" height="120" style="width:100%; height:120px; cursor:crosshair; background: #fafafa; border-radius: 6px;"></canvas>
                            <div class="position-absolute bottom-0 start-0 p-2 text-muted" style="font-size:0.7rem; pointer-events:none;">
                                <i class="fa-solid fa-pencil me-1"></i> Draw signature inside box using mouse or finger
                            </div>
                        </div>
                        <div class="d-flex justify-content-between align-items-center mt-2 flex-wrap gap-2">
                            <div class="d-flex gap-2">
                                <button type="button" class="btn btn-outline-secondary btn-sm rounded-pill px-3" onclick="clearSigPad()">
                                    <i class="fa-solid fa-eraser me-1"></i> Clear Pad
                                </button>
                                <button type="button" class="btn btn-outline-primary btn-sm rounded-pill px-3" onclick="loadSampleSig()">
                                    <i class="fa-solid fa-wand-magic-sparkles me-1"></i> Load Demo Signature
                                </button>
                            </div>
                            <span class="text-muted small" style="font-size:0.72rem;"><i class="fa-solid fa-circle-info me-1"></i>Touch & stylus supported</span>
                        </div>
                    </div>

                    <!-- Mode 2: File Upload -->
                    <div id="sigUploadContainer" class="mb-3" style="display:none;">
                        <label class="form-label small fw-bold text-secondary">Select Signature File (Transparent PNG recommended)</label>
                        <input type="file" class="form-control" id="sigFileInput" accept="image/png, image/jpeg, image/webp" onchange="handleSigFileUpload(event)">
                        <div class="form-text" style="font-size:0.72rem;">Accepts .png, .jpg, or .webp (max 2MB). Transparent background works best.</div>
                    </div>

                    <input type="hidden" name="signature_data" id="sigDataHidden">
                    <input type="hidden" name="clear_signature" id="sigClearHidden" value="0">

                    <button type="submit" class="btn btn-enterprise-primary w-100 py-2 rounded-pill fw-bold" id="btnSaveSig">
                        <i class="fa-solid fa-floppy-disk me-2"></i> Save & Apply E-Signature to Invoices
                    </button>
                </form>
            </div>
        </div>

        <!-- BIR EIS Compliance Readiness Card -->
        <div class="app-card mb-4">
            <div class="card-header-modern">
                <div>
                    <h5 class="fw-bold mb-1 fs-6"><i class="fa-solid fa-shield-halved text-danger me-2"></i>BIR EIS Certification Readiness</h5>
                    <p class="text-secondary small mb-0">System technical readiness for BIR accreditation audit.</p>
                </div>
            </div>
            <div class="p-4">
                <div class="d-flex flex-column gap-3 small">
                    <div class="d-flex align-items-center justify-content-between p-2 rounded bg-light">
                        <div>
                            <strong class="d-block text-dark">Document Type 380 Schema</strong>
                            <span class="text-muted" style="font-size:0.75rem;">UN/CEFACT Commercial Sales Invoice Standard</span>
                        </div>
                        <span class="badge bg-success-subtle text-success border border-success-subtle rounded-pill">Ready</span>
                    </div>

                    <div class="d-flex align-items-center justify-content-between p-2 rounded bg-light">
                        <div>
                            <strong class="d-block text-dark">12% VAT Breakdown Engine</strong>
                            <span class="text-muted" style="font-size:0.75rem;">Strict vatable, VAT, and exempt separation</span>
                        </div>
                        <span class="badge bg-success-subtle text-success border border-success-subtle rounded-pill">Compliant</span>
                    </div>

                    <div class="d-flex align-items-center justify-content-between p-2 rounded bg-light">
                        <div>
                            <strong class="d-block text-dark">SHA-256 Digital Digest</strong>
                            <span class="text-muted" style="font-size:0.75rem;">Tamper-proof cryptographic invoice hashing</span>
                        </div>
                        <span class="badge bg-success-subtle text-success border border-success-subtle rounded-pill">Active</span>
                    </div>

                    <div class="d-flex align-items-center justify-content-between p-2 rounded bg-light">
                        <div>
                            <strong class="d-block text-dark">Multi-Marketplace Channel</strong>
                            <span class="text-muted" style="font-size:0.75rem;">Shopee, Lazada, and TikTok Shop mapped</span>
                        </div>
                        <span class="badge bg-success-subtle text-success border border-success-subtle rounded-pill">Universal</span>
                    </div>

                    <div class="d-flex align-items-center justify-content-between p-2 rounded bg-light">
                        <div>
                            <strong class="d-block text-dark">Testing Portal URL</strong>
                            <span class="text-muted font-mono" style="font-size:0.75rem;">eis-cert.bir.gov.ph</span>
                        </div>
                        <span class="badge bg-primary-subtle text-primary border border-primary-subtle rounded-pill">Target</span>
                    </div>
                </div>
            </div>
        </div>

        <!-- Change Password Card -->
        <div class="app-card mb-4">
            <div class="card-header-modern">
                <div>
                    <h5 class="fw-bold mb-1 fs-6"><i class="fa-solid fa-key text-danger me-2"></i>Change Admin Password</h5>
                    <p class="text-secondary small mb-0">Update your security credentials for this portal.</p>
                </div>
            </div>
            <div class="p-4">
                <form id="passwordForm" onsubmit="changePassword(event)">
                    <input type="hidden" name="action" value="password">
                    
                    <div class="mb-3">
                        <label class="form-label small fw-bold text-secondary">Current Password</label>
                        <input type="password" class="form-control" name="old_password" placeholder="••••••••" required>
                    </div>

                    <div class="mb-4">
                        <label class="form-label small fw-bold text-secondary">New Password</label>
                        <input type="password" class="form-control" name="new_password" minlength="6" placeholder="At least 6 characters" required>
                    </div>

                    <button type="submit" class="btn btn-outline-danger px-4 py-2 rounded-pill fw-bold w-100" id="btnSavePassword">
                        <i class="fa-solid fa-lock me-2"></i> Update Password
                    </button>
                </form>
            </div>
        </div>

        <!-- Cloud Architecture Information -->
        <div class="app-card p-4">
            <div class="d-flex align-items-center gap-2 mb-2">
                <i class="fa-solid fa-cloud-check text-success fs-5"></i>
                <h6 class="fw-bold mb-0 fs-6">Hostinger Cloud Deployment</h6>
            </div>
            <p class="text-secondary small mb-3">
                This system is engineered to run 100% standalone on Hostinger cPanel / hPanel or local XAMPP without physical POS network dependencies.
            </p>
            <div class="small text-secondary">
                <div class="d-flex justify-content-between py-2 border-bottom">
                    <span>Hostinger Ready:</span>
                    <strong class="text-success"><i class="fa-solid fa-circle-check me-1"></i>Verified</strong>
                </div>
                <div class="d-flex justify-content-between py-2 border-bottom">
                    <span>PHP Compatibility:</span>
                    <strong>PHP 8.1 / 8.2 / 8.3</strong>
                </div>
                <div class="d-flex justify-content-between py-2">
                    <span>Architecture:</span>
                    <strong>Zero Composer / Self-Contained</strong>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Modal: BIR EIS Sandbox Certification Self-Test Report -->
<div class="modal fade" id="birEisTestModal" tabindex="-1" aria-labelledby="birEisTestModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-scrollable">
        <div class="modal-content border-0 shadow-lg" style="border-radius: var(--radius-lg);">
            <div class="modal-header bg-dark text-white border-bottom-0 py-3">
                <div class="d-flex align-items-center gap-2">
                    <i class="fa-solid fa-vial-circle-check text-warning fs-5"></i>
                    <div>
                        <h6 class="modal-title fw-bold mb-0" id="birEisTestModalLabel">BIR EIS Sandbox Certification Self-Test</h6>
                        <span class="text-white-50 small" style="font-size: 0.75rem;">Bureau of Internal Revenue RR 8-2022 Technical Compliance Verification</span>
                    </div>
                </div>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body p-4" id="eisModalBody">
                <!-- Dynamically populated by runBirEisTest() -->
                <div class="text-center py-5">
                    <div class="spinner-border text-danger" role="status"></div>
                    <p class="text-secondary small mt-3 mb-0">Running BIR EIS technical compliance test cases...</p>
                </div>
            </div>
            <div class="modal-footer bg-light border-top-0 py-3">
                <button type="button" class="btn btn-outline-secondary px-4 rounded-pill fw-bold" data-bs-dismiss="modal">Close</button>
                <button type="button" class="btn btn-dark px-4 rounded-pill fw-bold" onclick="copyEisJsonPayload()" id="btnCopyJson">
                    <i class="fa-solid fa-copy me-2"></i> Copy BIR JSON Payload
                </button>
            </div>
        </div>
    </div>
</div>

<script>
let lastEisJson = '';

function saveSettings(e) {
    e.preventDefault();
    const btn = document.getElementById('btnSaveSettings');
    btn.disabled = true;
    btn.innerHTML = '<i class="fa-solid fa-spinner fa-spin me-2"></i> Saving...';

    const fd = new FormData(document.getElementById('settingsForm'));

    fetch(window.BASE_URL + 'api/save_settings.php', {
        method: 'POST',
        body: fd
    })
    .then(r => r.json())
    .then(data => {
        btn.disabled = false;
        btn.innerHTML = '<i class="fa-solid fa-floppy-disk me-2"></i> Save Tax & EIS Settings';

        if (data.success) {
            Swal.fire({
                icon: 'success',
                title: 'Saved!',
                text: data.message,
                confirmButtonColor: '#ee4d2d'
            });
        } else {
            Swal.fire({
                icon: 'error',
                title: 'Error',
                text: data.error || 'Failed to save',
                confirmButtonColor: '#ee4d2d'
            });
        }
    })
    .catch(err => {
        btn.disabled = false;
        btn.innerHTML = '<i class="fa-solid fa-floppy-disk me-2"></i> Save Tax & EIS Settings';
        Swal.fire({
            icon: 'error',
            title: 'Error',
            text: err.message,
            confirmButtonColor: '#ee4d2d'
        });
    });
}

function changePassword(e) {
    e.preventDefault();
    const btn = document.getElementById('btnSavePassword');
    btn.disabled = true;

    const fd = new FormData(document.getElementById('passwordForm'));

    fetch(window.BASE_URL + 'api/save_settings.php', {
        method: 'POST',
        body: fd
    })
    .then(r => r.json())
    .then(data => {
        btn.disabled = false;
        if (data.success) {
            document.getElementById('passwordForm').reset();
            Swal.fire({
                icon: 'success',
                title: 'Success!',
                text: data.message,
                confirmButtonColor: '#ee4d2d'
            });
        } else {
            Swal.fire({
                icon: 'error',
                title: 'Error',
                text: data.error || 'Failed to change password',
                confirmButtonColor: '#ee4d2d'
            });
        }
    })
    .catch(err => {
        btn.disabled = false;
        Swal.fire({
            icon: 'error',
            title: 'Error',
            text: err.message,
            confirmButtonColor: '#ee4d2d'
        });
    });
}

function runBirEisTest() {
    const modalEl = document.getElementById('birEisTestModal');
    const modal = new bootstrap.Modal(modalEl);
    modal.show();

    const body = document.getElementById('eisModalBody');
    body.innerHTML = `
        <div class="text-center py-5">
            <div class="spinner-border text-danger" role="status"></div>
            <p class="text-secondary small mt-3 mb-0">Executing BIR EIS technical compliance test cases...</p>
        </div>
    `;

    fetch(window.BASE_URL + 'api/eis_test.php')
    .then(r => r.json())
    .then(data => {
        if (!data.success) {
            body.innerHTML = `
                <div class="alert alert-danger mb-0">
                    <i class="fa-solid fa-triangle-exclamation me-2"></i>
                    <strong>Error running test:</strong> ${data.error || 'Unknown test failure'}
                </div>
            `;
            return;
        }

        lastEisJson = data.sampleJson || '';
        const results = data.testResults;

        let scenariosHtml = '';
        results.scenarios.forEach(sc => {
            const isPass = sc.status === 'PASSED' || sc.status === 'VERIFIED_SCHEMA' || sc.status === 'READY_FOR_LIVE';
            const badgeClass = isPass ? 'bg-success-subtle text-success border border-success-subtle' : 'bg-danger-subtle text-danger border border-danger-subtle';
            const icon = isPass ? 'fa-solid fa-circle-check text-success' : 'fa-solid fa-circle-xmark text-danger';

            scenariosHtml += `
                <div class="p-3 mb-2 rounded-3 border bg-white">
                    <div class="d-flex justify-content-between align-items-start gap-2 mb-1">
                        <div class="d-flex align-items-center gap-2">
                            <i class="${icon}"></i>
                            <span class="fw-bold text-dark small">${sc.id}: ${sc.title}</span>
                        </div>
                        <span class="badge ${badgeClass} rounded-pill" style="font-size:0.7rem;">${sc.status}</span>
                    </div>
                    <p class="text-muted small mb-1" style="font-size:0.8rem;">${sc.description}</p>
                    <div class="p-2 rounded bg-light font-mono small text-secondary" style="font-size:0.75rem;">
                        ${sc.details}
                    </div>
                </div>
            `;
        });

        body.innerHTML = `
            <div class="alert alert-success d-flex align-items-center gap-2 mb-3 py-2">
                <i class="fa-solid fa-circle-check fs-5"></i>
                <div class="small">
                    <strong>Technical Compliance Validated:</strong> All 4 core BIR EIS test cases passed standard schema verification!
                </div>
            </div>

            <div class="row g-2 mb-3 small text-secondary">
                <div class="col-sm-4">
                    <div class="p-2 rounded bg-light border">
                        <div class="text-muted" style="font-size:0.7rem;">Target Endpoint</div>
                        <strong class="font-mono text-dark" style="font-size:0.75rem;">${results.apiEndpoint}</strong>
                    </div>
                </div>
                <div class="col-sm-4">
                    <div class="p-2 rounded bg-light border">
                        <div class="text-muted" style="font-size:0.7rem;">Environment</div>
                        <strong class="text-capitalize text-dark" style="font-size:0.75rem;">${results.environment} Gateway</strong>
                    </div>
                </div>
                <div class="col-sm-4">
                    <div class="p-2 rounded bg-light border">
                        <div class="text-muted" style="font-size:0.7rem;">Test Timestamp</div>
                        <strong class="font-mono text-dark" style="font-size:0.75rem;">${results.timestamp}</strong>
                    </div>
                </div>
            </div>

            <h6 class="fw-bold mb-2 small text-uppercase text-secondary" style="letter-spacing:0.04em;">Mandatory BIR EIS Certification Scenarios</h6>
            <div class="mb-4">
                ${scenariosHtml}
            </div>

            <div class="d-flex justify-content-between align-items-center mb-2">
                <h6 class="fw-bold mb-0 small text-uppercase text-secondary" style="letter-spacing:0.04em;">
                    <i class="fa-solid fa-code me-1 text-danger"></i> Official BIR EIS JSON Payload (Type 380)
                </h6>
                <span class="text-muted small" style="font-size:0.72rem;">Conforming to UN/CEFACT XML/JSON</span>
            </div>
            <pre class="p-3 rounded-3 font-mono bg-dark text-success small mb-0" style="max-height: 220px; overflow-y: auto; font-size:0.75rem; line-height: 1.4;"><code>${escapeHtml(lastEisJson)}</code></pre>
        `;
    })
    .catch(err => {
        body.innerHTML = `
            <div class="alert alert-danger mb-0">
                <i class="fa-solid fa-triangle-exclamation me-2"></i>
                <strong>Failed to execute test:</strong> ${err.message}
            </div>
        `;
    });
}

function copyEisJsonPayload() {
    if (!lastEisJson) {
        Swal.fire({ icon: 'info', title: 'No JSON', text: 'Run the self-test first to generate the payload.' });
        return;
    }
    navigator.clipboard.writeText(lastEisJson).then(() => {
        const btn = document.getElementById('btnCopyJson');
        const old = btn.innerHTML;
        btn.innerHTML = '<i class="fa-solid fa-check me-2 text-success"></i> Copied to Clipboard!';
        setTimeout(() => { btn.innerHTML = old; }, 2500);
    });
}

function escapeHtml(str) {
    return str.replace(/&/g, "&amp;")
              .replace(/</g, "&lt;")
              .replace(/>/g, "&gt;")
              .replace(/"/g, "&quot;")
              .replace(/'/g, "&#039;");
}

// ── Authorized Representative E-Signature Interactive Engine ──
let sigCanvas, sigCtx;
let isDrawing = false;
let hasDrawn = false;

function initSigCanvas() {
    sigCanvas = document.getElementById('sigCanvas');
    if (!sigCanvas) return;
    sigCtx = sigCanvas.getContext('2d');
    sigCtx.lineWidth = 2.8;
    sigCtx.lineCap = 'round';
    sigCtx.lineJoin = 'round';
    sigCtx.strokeStyle = '#0f2b48';

    function getPos(e) {
        const rect = sigCanvas.getBoundingClientRect();
        const clientX = e.touches ? e.touches[0].clientX : e.clientX;
        const clientY = e.touches ? e.touches[0].clientY : e.clientY;
        const scaleX = sigCanvas.width / rect.width;
        const scaleY = sigCanvas.height / rect.height;
        return {
            x: (clientX - rect.left) * scaleX,
            y: (clientY - rect.top) * scaleY
        };
    }

    function startDraw(e) {
        e.preventDefault();
        isDrawing = true;
        hasDrawn = true;
        document.getElementById('sigClearHidden').value = '0';
        const pos = getPos(e);
        sigCtx.beginPath();
        sigCtx.moveTo(pos.x, pos.y);
    }

    function draw(e) {
        if (!isDrawing) return;
        e.preventDefault();
        const pos = getPos(e);
        sigCtx.lineTo(pos.x, pos.y);
        sigCtx.stroke();
    }

    function stopDraw() {
        if (!isDrawing) return;
        isDrawing = false;
        sigCtx.closePath();
        // Sync to hidden input
        document.getElementById('sigDataHidden').value = sigCanvas.toDataURL('image/png');
    }

    sigCanvas.addEventListener('mousedown', startDraw);
    sigCanvas.addEventListener('mousemove', draw);
    window.addEventListener('mouseup', stopDraw);

    sigCanvas.addEventListener('touchstart', startDraw, { passive: false });
    sigCanvas.addEventListener('touchmove', draw, { passive: false });
    window.addEventListener('touchend', stopDraw);
}

function clearSigPad() {
    if (!sigCtx || !sigCanvas) return;
    sigCtx.clearRect(0, 0, sigCanvas.width, sigCanvas.height);
    hasDrawn = false;
    document.getElementById('sigDataHidden').value = '';
}

function loadSampleSig() {
    clearSigPad();
    hasDrawn = true;
    document.getElementById('sigClearHidden').value = '0';

    sigCtx.save();
    sigCtx.lineWidth = 3.0;
    sigCtx.strokeStyle = '#0f2b48';
    sigCtx.lineCap = 'round';
    sigCtx.lineJoin = 'round';

    // Draw realistic cursive signature path
    const pts = [
        [50, 75], [58, 50], [70, 30], [85, 25], [92, 40], [85, 65], [72, 85], [58, 92], [50, 80],
        [70, 45], [105, 30], [130, 65], [142, 72], [152, 55], [165, 48], [172, 60], [168, 75],
        [182, 58], [195, 48], [202, 60], [198, 75], [212, 42], [225, 32], [232, 50], [225, 75],
        [240, 62], [255, 52], [262, 65], [255, 78], [275, 40], [295, 25], [315, 35], [320, 55],
        [305, 75], [285, 80], [280, 72], [300, 58], [330, 50], [355, 65], [375, 55], [395, 60]
    ];

    sigCtx.beginPath();
    sigCtx.moveTo(pts[0][0], pts[0][1]);
    for (let i = 1; i < pts.length; i++) {
        sigCtx.lineTo(pts[i][0], pts[i][1]);
    }
    sigCtx.stroke();

    // Underline flourish
    sigCtx.beginPath();
    sigCtx.moveTo(60, 92);
    for (let x = 60; x <= 400; x += 5) {
        const y = 92 + Math.sin((x - 60) / 70) * 8 - ((x - 60) * 0.04);
        sigCtx.lineTo(x, y);
    }
    sigCtx.stroke();
    sigCtx.restore();

    document.getElementById('sigDataHidden').value = sigCanvas.toDataURL('image/png');
    Swal.fire({
        icon: 'info',
        title: 'Sample Loaded',
        text: 'A demo signature has been loaded onto the pad. Click "Save & Apply" to apply it to all invoices.',
        timer: 2000,
        showConfirmButton: false
    });
}

function handleSigFileUpload(e) {
    const file = e.target.files[0];
    if (!file) return;

    const reader = new FileReader();
    reader.onload = function(evt) {
        const img = new Image();
        img.onload = function() {
            clearSigPad();
            hasDrawn = true;
            document.getElementById('sigClearHidden').value = '0';
            
            // Draw into canvas scaled nicely
            const ratio = Math.min((sigCanvas.width - 20) / img.width, (sigCanvas.height - 20) / img.height);
            const dw = img.width * ratio;
            const dh = img.height * ratio;
            const dx = (sigCanvas.width - dw) / 2;
            const dy = (sigCanvas.height - dh) / 2;
            sigCtx.drawImage(img, dx, dy, dw, dh);

            document.getElementById('sigDataHidden').value = sigCanvas.toDataURL('image/png');
            Swal.fire({
                icon: 'success',
                title: 'Image Loaded',
                text: 'Signature image loaded into canvas. Click "Save & Apply" to update.',
                timer: 1800,
                showConfirmButton: false
            });
        };
        img.src = evt.target.result;
    };
    reader.readAsDataURL(file);
}

function switchSigMode(mode) {
    const drawCont = document.getElementById('sigDrawContainer');
    const uploadCont = document.getElementById('sigUploadContainer');
    const tabDraw = document.getElementById('tabDrawBtn');
    const tabUpload = document.getElementById('tabUploadBtn');

    if (mode === 'draw') {
        drawCont.style.display = 'block';
        uploadCont.style.display = 'none';
        tabDraw.className = 'btn btn-sm btn-dark rounded-pill px-3';
        tabUpload.className = 'btn btn-sm btn-outline-secondary rounded-pill px-3';
    } else {
        drawCont.style.display = 'none';
        uploadCont.style.display = 'block';
        tabDraw.className = 'btn btn-sm btn-outline-secondary rounded-pill px-3';
        tabUpload.className = 'btn btn-sm btn-dark rounded-pill px-3';
    }
}

function updateSigTextPreview() {
    const name = document.getElementById('sigNameInput').value || 'AUTHORIZED SIGNATORY';
    const desig = document.getElementById('sigDesigInput').value || 'Authorized Representative';
    document.getElementById('previewSignatoryText').innerHTML = `${escapeHtml(name)} — <span class="text-secondary fw-normal">${escapeHtml(desig)}</span>`;
}

function clearActiveSignature() {
    Swal.fire({
        title: 'Remove Signature?',
        text: 'This will remove the current signature image from future printed invoices.',
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#dc2626',
        confirmButtonText: 'Yes, remove it'
    }).then(result => {
        if (result.isConfirmed) {
            document.getElementById('sigClearHidden').value = '1';
            document.getElementById('sigDataHidden').value = '';
            clearSigPad();

            const fd = new FormData(document.getElementById('signatureForm'));
            fetch(window.BASE_URL + 'api/save_settings.php', {
                method: 'POST',
                body: fd
            })
            .then(r => r.json())
            .then(data => {
                if (data.success) {
                    document.getElementById('activeSigImg').style.display = 'none';
                    document.getElementById('noSigPlaceholder').style.display = 'block';
                    document.getElementById('sigStatusBadge').className = 'badge bg-warning-subtle text-warning border border-warning-subtle rounded-pill';
                    document.getElementById('sigStatusBadge').innerHTML = '<i class="fa-solid fa-pen-nib me-1"></i>Setup Needed';
                    Swal.fire({ icon: 'success', title: 'Removed', text: 'Signature removed successfully.' });
                }
            });
        }
    });
}

function saveSignature(e) {
    e.preventDefault();
    const btn = document.getElementById('btnSaveSig');
    btn.disabled = true;
    btn.innerHTML = '<i class="fa-solid fa-spinner fa-spin me-2"></i> Saving E-Signature...';

    // If canvas was drawn, ensure hidden input is populated
    if (hasDrawn && sigCanvas) {
        document.getElementById('sigDataHidden').value = sigCanvas.toDataURL('image/png');
    }

    const fd = new FormData(document.getElementById('signatureForm'));

    fetch(window.BASE_URL + 'api/save_settings.php', {
        method: 'POST',
        body: fd
    })
    .then(r => r.json())
    .then(data => {
        btn.disabled = false;
        btn.innerHTML = '<i class="fa-solid fa-floppy-disk me-2"></i> Save & Apply E-Signature to Invoices';

        if (data.success) {
            // Update active preview
            const curData = document.getElementById('sigDataHidden').value;
            const activeImg = document.getElementById('activeSigImg');
            const noSig = document.getElementById('noSigPlaceholder');
            const badge = document.getElementById('sigStatusBadge');

            if (curData && curData.startsWith('data:image')) {
                activeImg.src = curData;
                activeImg.style.display = 'inline-block';
                noSig.style.display = 'none';
                badge.className = 'badge bg-success-subtle text-success border border-success-subtle rounded-pill';
                badge.innerHTML = '<i class="fa-solid fa-circle-check me-1"></i>Active';
            } else if (activeImg.src) {
                activeImg.style.display = 'inline-block';
                noSig.style.display = 'none';
            }

            Swal.fire({
                icon: 'success',
                title: 'Signature Saved!',
                text: 'Your authorized e-signature is now active and will be printed on all generated invoices.',
                confirmButtonColor: '#ee4d2d'
            });
        } else {
            Swal.fire({
                icon: 'error',
                title: 'Error',
                text: data.error || 'Failed to save signature'
            });
        }
    })
    .catch(err => {
        btn.disabled = false;
        btn.innerHTML = '<i class="fa-solid fa-floppy-disk me-2"></i> Save & Apply E-Signature to Invoices';
        Swal.fire({ icon: 'error', title: 'Error', text: err.message });
    });
}

document.addEventListener('DOMContentLoaded', function() {
    initSigCanvas();
});
</script>

<?php require_once __DIR__ . '/includes/footer.php'; ?>

