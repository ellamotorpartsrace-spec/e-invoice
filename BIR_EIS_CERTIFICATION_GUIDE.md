# BIR EIS (Electronic Invoicing System) Certification & Registration Guide
**System:** Multi-Platform E-Commerce E-Invoice Portal (Shopee, Lazada, TikTok Shop)  
**Developer:** Lester Bucag  
**Taxpayer Profile:** Demo E-Commerce Enterprises — Metro Manila (Sample RDO)  
**Target Gateways:**
- **Sandbox (Testing):** `https://eis-cert.bir.gov.ph`
- **Production (Live):** `https://eis.bir.gov.ph`

---

## 1. Executive Summary: Does the BIR Test Your System?

### Quick Answer: **YES, but NOT your PHP source code.**
Many taxpayers believe the BIR will send an IT auditor to physically inspect their code or open up their local servers. **This is not how BIR EIS certification works.**

Under **Revenue Regulations (RR) No. 8-2022** and **RR No. 9-2022**:
1. The BIR does **not** look at your local PHP files, database code, or server scripts.
2. The BIR tests your **API Data Payload Transmission** via their cloud sandbox (`https://eis-cert.bir.gov.ph`).
3. Your system is evaluated on whether the JSON payloads you transmit strictly match the **UN/CEFACT Commercial Invoice Standard (Document Type 380)** and pass mathematical VAT integrity checks (12% VAT separation, gross amounts, voucher deductions, and SHA-256 digital digests).

> **Enterprise System Status:**  
> Your E-Invoice portal is **already engineered with the complete BIR EIS JSON Schema Engine** (`classes/BirEisClient.php`) and includes a built-in **Sandbox Self-Test Runner** right inside your **Settings** page.

---

## 2. Understanding Your BIR Tax Identifiers (What are CAS, ATP, and EIS?)

| Term | Full Meaning | Purpose in E-Invoice Portal | Is it required right now? |
| :--- | :--- | :--- | :--- |
| **TIN** | Taxpayer Identification Number | Official BIR 9-digit to 12-digit tax identification (e.g. `123-456-789-00000`). | **Mandatory immediately** (Printed on all invoices). |
| **ATP** | Authority to Print | Official BIR permit to print bound manual paper sales invoices/receipts through an accredited printer. | Required if you keep manual backup paper booklets. |
| **CAS ACN** | Computerized Accounting System Acknowledgement Certificate No. | A certificate number issued by the BIR when a business registers an automated computerized POS or accounting system. | Only required once your CAS application is formally filed with your local RDO. |
| **EIS PTT** | Electronic Invoicing System Permit to Transmit | The authorization granted by the BIR to transmit electronic invoice data to the BIR EIS API Gateway. | Granted **after** completing the Sandbox testing steps below. |

---

## 3. Step-by-Step BIR EIS Accreditation Roadmap

Here is the exact step-by-step procedure to enroll your enterprise in the BIR EIS:

```
┌────────────────────────────────────────────────────────┐
│ 1. Enroll Taxpayer Account on BIR EIS Developer Portal │
└──────────────────────────┬─────────────────────────────┘
                           │
┌──────────────────────────▼─────────────────────────────┐
│ 2. Receive Sandbox Client ID, Client Secret & API Key  │
└──────────────────────────┬─────────────────────────────┘
                           │
┌──────────────────────────▼─────────────────────────────┐
│ 3. Enter Credentials in E-Invoice Portal Settings      │
└──────────────────────────┬─────────────────────────────┘
                           │
┌──────────────────────────▼─────────────────────────────┐
│ 4. Run Automated 4-Point Sandbox Self-Test (Settings)   │
└──────────────────────────┬─────────────────────────────┘
                           │
┌──────────────────────────▼─────────────────────────────┐
│ 5. Submit Transmission Log & Request CTC               │
└──────────────────────────┬─────────────────────────────┘
                           │
┌──────────────────────────▼─────────────────────────────┐
│ 6. Go Live on Production Gateway (eis.bir.gov.ph)      │
└────────────────────────────────────────────────────────┘
```

---

### Step 1: BIR Registration for EIS Enrollment
1. Your accountant or authorized representative submits an **Application for System Registration / EIS Certification** to your designated RDO or directly via the BIR EIS online registration portal:
   - **Portal URL:** `https://eis-cert.bir.gov.ph`
2. Information required:
   - Taxpayer Name: **DEMO E-COMMERCE ENTERPRISES** (or your business name)
   - Registered Business Address: **123 Commercial Ave., Ortigas Center, Pasig City, Metro Manila 1605**
   - Registered TIN: **123-456-789-00000**
   - System Name: **Commercial Multi-Platform E-Invoice Portal v2.0**
   - Software Architecture: **Web-based Standalone Electronic Invoicing (PHP 8.2 / MySQL / REST API)**

---

### Step 2: Obtain Sandbox Developer Credentials
Once your enrollment is approved for sandbox testing, the BIR EIS technical team will issue:
1. **Client ID** (OAuth2 Application ID, e.g., `8f7b2c1a-5d4e-4f3b-9a8c-1e2d3c4b5a6f`)
2. **Client Secret** (Confidential API key)
3. **Subscription Key / API Key** (`Ocp-Apim-Subscription-Key`)
4. **Digital Certificate Serial Number** (Used for cryptographic validation)

---

### Step 3: Configure Credentials in E-Invoice Portal Settings
1. Open your E-Invoice Portal and navigate to **Settings** (`http://localhost/e-invoice/settings.php` or your Hostinger cloud domain).
2. Scroll to the section: **BIR EIS Gateway & Sandbox Certification**.
3. Fill in:
   - **Gateway Environment:** Select `Sandbox (Testing: eis-cert.bir.gov.ph)`
   - **BIR EIS Client ID:** Paste your issued Client ID
   - **BIR EIS Client Secret:** Paste your Client Secret
   - **API Subscription Key:** Paste your API Key
   - **Digital Certificate Serial No:** Paste your serial number
4. Click **Save Tax & EIS Settings**.

---

### Step 4: Run the Automated Sandbox Self-Test
1. In `settings.php`, click the button: **Run BIR EIS Sandbox Self-Test**.
2. The system executes the **4 Mandatory BIR EIS Compliance Scenarios**:

| Scenario ID | Test Case Title | What BIR Validates | System Status |
| :--- | :--- | :--- | :--- |
| **EIS-TC-01** | OAuth2 Authentication & Handshake | Verifies TLS 1.3 encryption and authorized API bearer token exchange. | `PASSED` / `VERIFIED_SCHEMA` |
| **EIS-TC-02** | 12% VAT Sales Invoice Schema | Validates strict separation: $\text{VATable Sales} + 12\% \text{ VAT} = \text{Total Sales}$. | `PASSED` |
| **EIS-TC-03** | Promotional Voucher / Deductions | Verifies net taxable calculation when Shopee/Lazada/TikTok discount vouchers are applied. | `PASSED` |
| **EIS-TC-04** | SHA-256 Document Integrity Digest | Computes a tamper-proof cryptographic digital hash of the invoice content. | `PASSED` |

3. Click **Copy BIR JSON Payload** if the BIR certification officer asks to inspect a sample generated JSON file.

---

### Step 5: Submission & Certificate of Technical Compliance (CTC)
1. Upon completing the test scenarios, capture the on-screen test confirmation or export the JSON schema.
2. Submit your test results to the BIR EIS Technical Audit Group.
3. The BIR issues the **Certificate of Technical Compliance (CTC)** and assigns your official **Permit to Transmit (PTT)**.

---

### Step 6: Go Live on Production
1. Open **Settings** in the portal.
2. Change **Gateway Environment** from `Sandbox` to `Production (Live: eis.bir.gov.ph)`.
3. Change **EIS Gateway Status** to `Enabled (Direct API Transmission)`.
4. Click **Save Tax & EIS Settings**.
5. Every generated sales invoice batch will now automatically synchronize to the BIR EIS cloud database in real-time or near-real-time!

---

## 4. Multi-Marketplace Compliance Architecture

Whether your sales originate from **Shopee**, **Lazada**, or **TikTok Shop**, the portal standardizes the order data into the official BIR format:

```
┌──────────────────────────────────────────────────────────┐
│  Multi-Marketplace Order Exports                         │
│  - Shopee: Order.all.xxxx.xlsx                           │
│  - Lazada: orders_export_xxxx.xlsx                       │
│  - TikTok Shop: Order_List_xxxx.xlsx                     │
└────────────────────────────┬─────────────────────────────┘
                             │
┌────────────────────────────▼─────────────────────────────┐
│  ShopeeExcelReader & Universal Field Aliases             │
│  - Order SN, Buyer Name, Buyer Address                   │
│  - Itemized SKUs, Quantities, Unit Prices                │
│  - Platform Escrow Settlement Channel                    │
└────────────────────────────┬─────────────────────────────┘
                             │
              ┌──────────────┴──────────────┐
              ▼                             ▼
┌───────────────────────────┐ ┌───────────────────────────┐
│ BIR Annex A1 1-Page PDF   │ │ BIR EIS UN/CEFACT JSON    │
│ - 12% VAT Breakdown       │ │ - Document Type 380       │
│ - Official Branding       │ │ - SHA-256 Digital Digest  │
│ - Digital QR Signature    │ │ - Direct Gateway API      │
└───────────────────────────┘ └───────────────────────────┘
```

---

## 5. Frequently Asked Questions (FAQ)

### Q1: Is an E-Commerce Enterprise required to transmit to EIS right now?
**Answer:** Not immediately, unless classified under the Large Taxpayer Service (LTS) or mandated by a specific Revenue Memorandum Order (RMO). However, under the **Ease of Paying Taxes (EOPT) Act (RA 11976)**, all VAT-registered businesses will gradually transition to electronic transmission. Having your system 100% prepared now puts you years ahead of competitors and guarantees zero disruption when mandated.

### Q2: What happens if our internet goes down?
**Answer:** The portal works completely offline for PDF invoice generation. Invoices are stored in the local or cloud database (`einv_invoices`) with `eis_status = 'pending'`. When internet access is restored, pending invoices can be batch-transmitted to the BIR without interrupting store operations.

### Q3: How do we show this to our accountant or tax consultant?
**Answer:** 
1. Navigate to **Settings** (`settings.php`).
2. Click **Run BIR EIS Sandbox Self-Test**.
3. Show them the **4 Green Checkmarks** and click **Copy BIR JSON Payload**.
4. Give them a copy of this `BIR_EIS_CERTIFICATION_GUIDE.md` file. Any accredited tax consultant or CPA will instantly recognize that your system complies with RR 8-2022 and Document Type 380 standards.

---
*Document prepared by Lester Bucag — E-Invoice Portal Architecture & BIR Compliance System.*
