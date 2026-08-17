# AGENT.md

## PROJECT NAME

ISP / RT-RW Net Management System

## PROJECT PURPOSE

Build a complete web-based management application for ISP / RT-RW Net operations using:

- PHP Native
- SQLite
- PDO
- HTML5
- Custom CSS
- Vanilla JavaScript
- Font Awesome
- Chart.js

The application must combine:

- customer management
- billing
- payments
- finance
- payroll
- RAB
- inventory
- asset management
- profit and loss
- AI assistant
- company settings
- users
- role and permission management
- invoice templates
- audit logs

The application should feel like a modern SaaS dashboard, not like an old Bootstrap admin template.

---

# CORE DEVELOPMENT RULES

## 1. TECHNOLOGY

Use:

- PHP 8.2 or newer
- PHP Native
- PDO
- SQLite
- HTML5
- CSS3
- Vanilla JavaScript
- Font Awesome
- Chart.js

Do not use:

- Laravel
- CodeIgniter
- Symfony
- React
- Vue
- Angular
- Node.js backend
- emoji for UI icons

Use Font Awesome for all interface icons.

---

# 2. DATABASE

Primary database:

```text
SQLite
```

Use PDO for every database operation.

Database location:

```text
/database/isp.sqlite
```

Enable:

```sql
PRAGMA foreign_keys = ON;
PRAGMA journal_mode = WAL;
```

Use database transactions for operations involving multiple related records.

Never use `FLOAT` for money.

Store Indonesian Rupiah as integer.

Example:

```text
Rp150.000
```

stored as:

```text
150000
```

Use foreign keys wherever relationships exist.

Use indexes for frequently searched columns.

Avoid hardcoded reference data if the data can be managed from the application.

---

# 3. DATABASE MIGRATION READINESS

Although the initial database is SQLite, application architecture must allow migration to MySQL or MariaDB later.

Avoid SQLite-specific SQL unless absolutely necessary.

Keep database access inside models, repositories, or service classes.

Do not scatter raw SQL across view files.

---

# 4. APPLICATION ARCHITECTURE

Use a clean modular MVC-like PHP Native structure.

Recommended structure:

```text
isp-app/
│
├── public/
│   ├── index.php
│   └── assets/
│       ├── css/
│       ├── js/
│       ├── images/
│       └── fonts/
│
├── app/
│   ├── Controllers/
│   ├── Models/
│   ├── Services/
│   ├── Middleware/
│   ├── Helpers/
│   └── Views/
│
├── config/
│   ├── app.php
│   ├── database.php
│   └── constants.php
│
├── database/
│   ├── isp.sqlite
│   ├── migrations/
│   └── seeds/
│
├── routes/
│   └── web.php
│
├── storage/
│   ├── attachments/
│   ├── invoices/
│   ├── exports/
│   ├── backups/
│   └── logs/
│
└── .env
```

Do not create one giant PHP file.

Separate responsibilities clearly.

---

# 5. UI / UX DIRECTION

Create a modern professional SaaS-style dashboard.

The interface must feel:

- modern
- clean
- minimal
- professional
- fast
- operational
- business-focused
- easy to scan
- responsive

Do not make it look like an old Bootstrap admin dashboard.

Use generous spacing and a strong information hierarchy.

---

# 6. DESIGN SYSTEM

Use a restrained business color palette.

Recommended visual direction:

```text
Background:
soft gray / off-white

Cards:
white

Primary text:
dark charcoal

Secondary text:
muted gray

Primary accent:
blue / indigo

Success:
green

Warning:
amber

Danger:
red

Info:
blue
```

Use subtle borders.

Use subtle shadows.

Avoid excessive gradients.

Avoid neon colors.

Avoid excessive glassmorphism.

Use rounded corners approximately:

```text
10px - 14px
```

---

# 7. TYPOGRAPHY

Use modern system typography or Inter-style typography.

Create clear hierarchy for:

- page title
- section title
- card title
- KPI number
- table text
- label
- helper text
- status badge

Typography must remain readable on desktop and mobile.

---

# 8. ICON SYSTEM

DO NOT USE EMOJI.

Use Font Awesome.

Examples:

```text
Dashboard
fa-gauge-high

Customers
fa-users

Billing
fa-file-invoice-dollar

Finance
fa-wallet

Payroll
fa-money-check-dollar

RAB
fa-calculator

Inventory
fa-boxes-stacked

Assets
fa-laptop

Profit & Loss
fa-chart-line

AI Assistant
fa-robot

Settings
fa-gear

Notifications
fa-bell

Search
fa-magnifying-glass

Import
fa-file-import

Export
fa-file-export

Print
fa-print

Add
fa-plus

Edit
fa-pen

Delete
fa-trash

View
fa-eye
```

---

# 9. RESPONSIVE LAYOUT

Desktop:

```text
Left Sidebar
Top Navigation
Main Content
```

Tablet:

```text
Collapsible Sidebar
```

Mobile:

```text
Drawer Sidebar
Responsive Cards
Scrollable Tables
Mobile-friendly Forms
```

Do not simply shrink desktop layout.

Optimize interactions for smaller screens.

---

# MAIN APPLICATION MENU

Create the main navigation:

```text
Dashboard

Pelanggan

Billing

Keuangan

Payroll

RAB

Inventory & Asset

Laba Rugi

AI Assistant

Pengaturan

Keluar
```

Additional operational modules may be added:

```text
Ticketing
Network / Infrastruktur
Procurement
Notifications
Audit Log
```

These modules should remain integrated into the overall data architecture.

---

# AUTHENTICATION

Implement secure PHP session authentication.

Features:

- login
- logout
- session persistence
- session timeout
- password hashing
- remember login optional
- CSRF protection

Use:

```php
password_hash()
password_verify()
```

Never store plaintext passwords.

---

# USERS

Create dynamic user management.

Fields:

```text
ID
Name
Email
Username
Phone
Password
Role
Status
Last Login
Created At
Updated At
```

Status:

```text
Active
Inactive
Suspended
```

---

# ROLE & PERMISSION

Implement dynamic role-based access control.

Do not hardcode roles into application logic.

Create:

```text
roles
permissions
role_permissions
user_roles
```

Possible roles:

```text
Super Admin
Owner
Admin
Finance
Billing
Supervisor
Technician
Staff
```

Permission examples:

```text
customers.view
customers.create
customers.edit
customers.delete

billing.view
billing.create
billing.edit
billing.payment

finance.view
finance.create
finance.edit

payroll.view
payroll.manage

inventory.view
inventory.manage

rab.view
rab.manage

profit_loss.view

ai.use

settings.manage

users.manage
```

Buttons and pages must respect permissions.

Backend must also validate permissions.

Never rely only on hiding UI buttons.

---

# DASHBOARD

Create a sophisticated executive dashboard.

Main KPI cards should include:

```text
Total Customers
Active Customers
Suspended Customers
Inactive Customers

Monthly Billing
Paid Billing
Unpaid Billing
Overdue Billing

Monthly Revenue
Monthly Expenses
Net Profit

Cash Balance

Accounts Receivable

Inventory Value
Asset Value

Low Stock Items

Open Tickets
```

Add useful charts:

```text
Revenue vs Expense
Monthly Profit
Customer Growth
Billing Collection Rate
Cashflow
Invoice Status
```

Add recent activity.

Add reminders and alerts.

---

# CUSTOMER MODULE

Menu:

```text
Pelanggan
├── Data Pelanggan
├── PIC
├── Paket
└── Lokasi
```

All data must be dynamic.

---

# CUSTOMER STATUS

Support:

```text
Prospect
Installation
Active
Suspended
Inactive
Terminated
Archived
```

---

# CUSTOMER DATA

Fields should include:

```text
Customer ID
Customer Number
Name
Phone
WhatsApp
Email
PIC
Internet Package
Package Price
Location
Full Address
Latitude
Longitude
ODP
POP
Installation Date
Activation Date
Billing Start Date
Billing Cycle
Status
Notes
Created At
Updated At
```

---

# CUSTOMER DASHBOARD

Display:

```text
Total Customers
Active
Suspended
Inactive
New Customers
```

Provide:

```text
Add Customer
Import CSV
Export CSV
Export PDF
Export to Google Sheets
Search
Filter
Bulk Actions
```

---

# IMPORT CUSTOMER

Support CSV import.

Provide:

- file upload
- field mapping
- preview
- validation
- duplicate checking
- import result report

Do not blindly insert invalid rows.

---

# EXPORT CUSTOMER

Support:

```text
CSV
PDF
Google Sheets
```

Google Sheets must be treated as an API integration, not as a file format.

---

# PIC MODULE

Create dynamic PIC master data.

Fields:

```text
Name
Phone
WhatsApp
Email
Position
Company
Notes
```

One PIC may be linked to multiple customers.

---

# INTERNET PACKAGE MODULE

Create dynamic internet packages.

Fields:

```text
Package Name
Download Speed
Upload Speed
Price
Tax
Installation Fee
Billing Cycle
Description
Status
```

Package pricing must not retroactively alter old invoice values.

Invoices must store snapshot values when generated.

---

# LOCATION MODULE

Create dynamic location data.

Possible hierarchy:

```text
Area
Village
District
City
Region
POP
ODP
```

Support coordinates.

---

# BILLING MODULE

Menu:

```text
Billing
├── Invoice
├── Payment
├── Billing Cycle
├── Collection / Receivable
└── Billing History
```

---

# BILLING DASHBOARD

Display:

```text
Total Billing This Month
Paid
Unpaid
Partially Paid
Overdue
Collection Rate
Accounts Receivable
```

---

# BILLING CYCLE

Create dynamic billing cycles.

Example:

```text
Cycle A
Generate Date: 1
Due Date: 10

Cycle B
Generate Date: 5
Due Date: 15

Cycle C
Generate Date: 20
Due Date: 30
```

Each customer may be assigned to a different billing cycle.

Support:

```text
Automatic invoice generation
Grace period
Overdue rule
Suspend rule
Reminder schedule
```

---

# INVOICE

Invoice fields:

```text
Invoice Number
Customer
Billing Period
Package
Subtotal
Discount
Tax
Additional Charges
Previous Balance
Grand Total
Issue Date
Due Date
Payment Status
Payment Date
Payment Method
Notes
Created By
Created At
```

Invoice statuses:

```text
Draft
Unpaid
Partially Paid
Paid
Overdue
Cancelled
Void
```

---

# INVOICE ITEMS

Invoices must support multiple line items.

Examples:

```text
Internet Package
Installation Fee
Additional IP
Equipment Rental
Penalty
Discount
Manual Adjustment
Other Service
```

---

# PAYMENT

Payment records must be separate from invoices.

A payment can include:

```text
Payment Number
Invoice
Customer
Amount
Date
Payment Method
Bank / Cash Account
Reference Number
Proof
Notes
Received By
```

Support partial payment.

---

# BILLING AND FINANCE RELATION

Never treat an invoice as cash income before payment is received.

Correct flow:

```text
Invoice Created
     ↓
Accounts Receivable
     ↓
Payment Received
     ↓
Finance Income Transaction
```

---

# COLLECTION / ACCOUNTS RECEIVABLE

Create aging buckets:

```text
1-7 Days
8-30 Days
31-60 Days
61-90 Days
Over 90 Days
```

Display:

```text
Outstanding Invoice Count
Outstanding Amount
Collection Rate
Overdue Customers
```

---

# FINANCE MODULE

Menu:

```text
Keuangan
├── Pemasukan
├── Pengeluaran
├── Kategori
├── Kas & Bank
├── Transfer
├── Budget
└── Cashflow
```

---

# FINANCE ACCOUNTS

Support multiple accounts:

```text
Cash
BCA
Mandiri
BRI
QRIS
Payment Gateway
Other Bank
```

Fields:

```text
Account Name
Account Type
Bank
Account Number
Opening Balance
Current Balance
Status
```

---

# FINANCE TRANSACTIONS

Transaction types:

```text
Income
Expense
Transfer
Adjustment
```

Fields:

```text
Transaction Number
Date
Account
Category
Type
Amount
Description
Reference
Attachment
Created By
```

---

# FINANCE CATEGORY

Create dynamic categories.

Examples:

```text
Internet Revenue
Installation Revenue
Equipment Revenue

Bandwidth
Electricity
Office Rent
Vehicle
Fuel
Maintenance
Marketing
Payroll
Project
Inventory Purchase
Other
```

---

# BUDGET

Create monthly or yearly budgets.

Support:

```text
Budget Category
Budget Amount
Actual Amount
Variance
Percentage Used
```

Display:

```text
Budget vs Actual
```

---

# CASHFLOW

Provide:

```text
Opening Cash
Cash In
Cash Out
Closing Cash
```

Support date range filtering.

---

# PAYROLL MODULE

Create a flexible payroll system.

Menu:

```text
Payroll
├── Employees
├── Departments
├── Payroll Period
├── Bonuses
├── Deductions
└── Payroll History
```

---

# EMPLOYEE

Fields:

```text
Employee ID
Name
Department
Position
Phone
Email
Basic Salary
Bank
Bank Account
Employment Status
Join Date
Notes
```

---

# PAYROLL COMPONENT

Support:

```text
Basic Salary
Allowance
Bonus
Overtime
Commission
Deduction
Penalty
Other Components
```

Components must be dynamic.

---

# PAYROLL PROCESS

Workflow:

```text
Draft
Calculated
Reviewed
Approved
Paid
```

Payroll payment should create finance expense records.

---

# RAB MODULE

Menu:

```text
RAB
├── Daftar RAB
├── Buat RAB
├── Kategori
├── Approval
└── Realisasi
```

---

# RAB USE CASE

Examples:

```text
ODP Construction
POP Construction
Backbone Installation
Office Installation
CCTV Project
Customer Project
Network Upgrade
Equipment Procurement
```

---

# RAB HEADER

Fields:

```text
RAB Number
Project Name
Location
PIC
Customer
Start Date
End Date
Description
Status
Created By
Approved By
```

Statuses:

```text
Draft
Submitted
Approved
Rejected
In Progress
Completed
Cancelled
```

---

# RAB ITEMS

Fields:

```text
Item
Category
Description
Qty
Unit
Unit Price
Subtotal
Notes
```

Support:

```text
Discount
Tax
Additional Cost
Contingency
Grand Total
```

---

# RAB REALIZATION

Track:

```text
Budget
Actual Cost
Difference
Variance Percentage
```

RAB should connect with inventory and finance.

---

# INVENTORY & ASSET MODULE

Menu:

```text
Inventory & Asset
├── Dashboard
├── Katalog Barang
├── Barang Masuk
├── Barang Keluar
├── Aset
├── Mutasi / Transfer
├── Stock Opname
├── Rusak / Hilang
├── Supplier
└── Kategori
```

---

# INVENTORY DASHBOARD

Display:

```text
Total Product Catalog
Total Stock Units
Physical Stock
Inventory Value
Asset Value
Low Stock
Out of Stock
Incoming Goods
Outgoing Goods
```

---

# INVENTORY ITEM

Fields:

```text
SKU
Product Name
Category
Brand
Model
Unit
Purchase Price
Estimated Value
Minimum Stock
Warehouse
Supplier
Image
Description
Status
```

---

# STOCK VS ASSET

Do not treat inventory stock and company assets as the same thing.

Example:

```text
ONU Stock
= Inventory

Fusion Splicer with Serial Number
= Asset
```

Goods may leave a warehouse and still remain company assets.

Example:

```text
ONU
Warehouse Stock -1
Assigned to Customer
Still Owned by Company
```

---

# GOODS IN

Fields:

```text
Transaction Number
Date
Supplier
Warehouse
Purchase Reference
Invoice Number
Attachment
Notes
Created By
```

Each transaction supports multiple items.

Fields per item:

```text
Item
Qty
Unit Price
Subtotal
Serial Number Optional
MAC Address Optional
```

Stock increases automatically after confirmation.

---

# GOODS OUT

Fields:

```text
Transaction Number
Date
Destination Type
Customer
Technician
Project
Department
Warehouse
PIC
Proof Photo
Notes
Created By
```

Destination types:

```text
Customer
Technician
Project
Department
Internal Use
Damaged
Lost
Other
```

Stock decreases automatically.

---

# ASSET MANAGEMENT

Fields:

```text
Asset ID
Item
Asset Name
Serial Number
MAC Address
Purchase Date
Purchase Price
Current Value
Condition
Location
Department
PIC
Customer
Status
Notes
```

Asset status:

```text
Available
In Use
Loaned
Assigned to Customer
Maintenance
Damaged
Lost
Sold
Disposed
```

Track full asset history.

---

# STOCK OPNAME

Support physical stock counting.

Workflow:

```text
Create Opname
Count Physical Stock
Compare System Stock
Show Variance
Approval
Adjustment
```

Adjustments must be logged.

---

# LOW STOCK

Each inventory item can define:

```text
Minimum Stock
```

System should automatically mark:

```text
Low Stock
Out of Stock
```

---

# SUPPLIER

Fields:

```text
Supplier Name
Company
Contact Person
Phone
WhatsApp
Email
Address
Tax ID Optional
Notes
Status
```

---

# PROCUREMENT

Recommended module:

```text
Purchase Request
Purchase Order
Supplier
Goods Receipt
Finance Payment
```

Flow:

```text
Purchase Request
      ↓
Approval
      ↓
Purchase Order
      ↓
Goods In
      ↓
Inventory
      ↓
Supplier Payment
      ↓
Finance
```

---

# PROFIT & LOSS MODULE

Create automatic profit and loss reporting.

Main components:

```text
Revenue
Cost of Goods / Direct Costs
Gross Profit
Operating Expenses
Payroll
Bandwidth
Other Expenses
Net Profit
```

Provide filters:

```text
Daily
Monthly
Quarterly
Yearly
Custom Range
```

Provide comparison:

```text
Current Month vs Previous Month
Current Year vs Previous Year
```

---

# IMPORTANT ACCOUNTING RULE

Profit & Loss must be based on reliable financial transaction data.

Do not calculate profit from random dashboard totals.

All sources must be traceable.

---

# AI ASSISTANT

Create a flexible AI Assistant module.

The AI must use application data as its business knowledge source.

Do not build a simple generic chatbot.

AI should be able to answer business questions such as:

```text
Who has not paid this month?

How many customers are overdue?

What is the total receivable?

How much did we spend this month?

What is our current profit?

Which inventory items are almost out?

How much inventory value do we have?

Can we afford to buy an OLT this month?

Why did profit decrease?

What is the projected cash position next month?
```

---

# AI PROVIDERS

Support flexible providers.

Examples:

```text
OpenAI
Google Gemini
Anthropic
OpenRouter
Custom OpenAI-Compatible API
```

Provider settings:

```text
Provider Name
API Key
Base URL
Model
Temperature
Max Tokens
Status
Default Provider
Fallback Provider
```

---

# AI API KEY SECURITY

Never expose AI API keys in the browser.

Correct flow:

```text
Browser
   ↓
PHP Backend
   ↓
AI Service
   ↓
AI Provider
```

Store API keys encrypted.

---

# AI ARCHITECTURE

Recommended:

```text
app/Services/AI/
├── AIManager.php
├── AIProviderInterface.php
├── OpenAIProvider.php
├── GeminiProvider.php
├── OpenRouterProvider.php
└── CustomProvider.php
```

---

# AI DATA ACCESS

Never allow AI unrestricted raw SQL execution.

Create safe internal data tools.

Examples:

```text
getCustomerSummary()
getUnpaidInvoices()
getOverdueCustomers()
getMonthlyRevenue()
getMonthlyExpenses()
getCashBalance()
getProfitLoss()
getInventorySummary()
getLowStockItems()
getAssetValue()
getPayrollCost()
getRABSummary()
getCashProjection()
```

AI chooses appropriate tools based on user questions.

---

# AI PERMISSIONS

AI data access must respect logged-in user permissions.

Example:

```text
Owner
Can access:
Finance
Payroll
Profit & Loss
Customers
Inventory

Technician
Can access:
Assigned Customers
Inventory
Tickets

Cannot access:
Company Profit
Payroll
Sensitive Finance
```

Backend determines access.

AI cannot bypass permission rules.

---

# AI FINANCIAL ADVISOR

For financial questions, application logic should calculate data first.

AI should:

```text
Explain
Interpret
Compare
Summarize
Recommend
```

AI should not invent numbers.

Example:

Question:

```text
Can I buy an OLT worth Rp35,000,000 this month?
```

Application should calculate:

```text
Cash Balance
Expected Collections
Projected Revenue
Accounts Payable
Payroll
Bandwidth
Operating Expenses
Approved RAB
Cash Reserve
```

Then AI explains the result.

---

# FINANCIAL HEALTH ENGINE

Create reusable metrics such as:

```text
Cash Available
Accounts Receivable
Current Liability
Projected Cash
Monthly Burn Rate
Cash Reserve Months
Profit Margin
Collection Rate
ARPU
Customer Churn
```

---

# FORECAST

Support simple business forecasting.

Use historical application data.

Forecast examples:

```text
Revenue
Expense
Profit
Cash Balance
Customer Growth
Inventory Consumption
```

Predictions must include:

```text
Estimated Range
Confidence Level
Reason
Data Period Used
```

Never present uncertain forecasts as guaranteed facts.

---

# EXPLAIN YOUR NUMBERS

AI answers containing financial values should provide a source breakdown.

Example:

```text
Monthly Expenses
Rp73,450,000

Breakdown:

Payroll
Rp18,500,000

Bandwidth
Rp25,000,000

Operations
Rp17,250,000

RAB
Rp8,700,000

Other
Rp4,000,000
```

Users should be able to inspect source records.

---

# TICKETING MODULE

Recommended operational module.

Menu:

```text
Ticketing
├── Open Tickets
├── Assigned
├── In Progress
├── Resolved
└── Closed
```

Ticket fields:

```text
Ticket Number
Customer
Complaint
Category
Priority
Technician
Reported At
Started At
Resolved At
Status
Cause
Action
Before Photo
After Photo
Notes
```

Statuses:

```text
Open
Assigned
In Progress
Waiting
Resolved
Closed
Cancelled
```

---

# NETWORK / INFRASTRUCTURE MODULE

Create network infrastructure records separately from inventory.

Inventory answers:

```text
What equipment do we own?
```

Network answers:

```text
Where is that equipment installed?
```

Structure may include:

```text
POP
OLT
PON Port
ODC
ODP
Router
Switch
Access Point
IP Address
VLAN
Customer Connection
```

Example:

```text
OLT
└── PON 1
    └── ODP-017
        ├── Customer A
        ├── Customer B
        └── Customer C
```

---

# NOTIFICATION CENTER

Create centralized notifications.

Examples:

```text
Invoice Due Today
Overdue Invoice
Low Stock
Out of Stock
RAB Approval
Payroll Approval
Ticket Assigned
Asset Maintenance
Backup Failed
```

---

# WHATSAPP NOTIFICATION

Prepare architecture for WhatsApp integration.

Use template variables:

```text
{{customer_name}}
{{invoice_number}}
{{amount}}
{{due_date}}
{{payment_link}}
```

Potential triggers:

```text
Invoice Generated
H-3 Due Date
Due Date Today
Overdue
Payment Received
Ticket Closed
```

---

# MIKROTIK / RADIUS INTEGRATION

Prepare optional integration architecture.

Potential actions:

```text
Read PPPoE User
Read Online Status
Read IP
Read Uptime
Read Profile
Suspend Customer
Activate Customer
```

Do not directly suspend users solely because a billing job ran.

Use:

```text
Grace Period
Exception List
Audit Log
Optional Approval
Retry Mechanism
```

---

# APPROVAL WORKFLOW

Create reusable approval workflow.

Applicable to:

```text
RAB
Purchase Request
Purchase Order
Large Expense
Payroll
Invoice Adjustment
Discount
Asset Disposal
Stock Adjustment
Write-off
```

Possible statuses:

```text
Draft
Pending Approval
Approved
Rejected
Cancelled
```

---

# AUDIT LOG

Audit log is mandatory.

Track:

```text
User
Action
Module
Record ID
Old Value
New Value
IP Address
User Agent
Timestamp
```

Examples:

```text
Changed Package Price
Marked Invoice Paid
Deleted Customer
Adjusted Stock
Approved RAB
Changed User Permission
Changed AI Provider
```

Sensitive transactions should not be permanently deleted without an audit record.

---

# SOFT DELETE

Use soft-delete or void status for critical records.

Examples:

```text
deleted_at
cancelled_at
voided_at
```

Avoid permanent deletion for:

```text
Invoice
Payment
Finance Transaction
Payroll
RAB
Inventory Transaction
Asset
```

---

# ATTACHMENTS

Support attachments for:

```text
Customer Documents
Payment Proof
Goods In
Goods Out
Supplier Invoice
RAB
Ticket
Asset
Payroll
Finance
```

Store files safely outside executable code directories where possible.

Generate unique filenames.

Validate extensions and MIME types.

---

# SEARCH

Provide global search.

Search placeholder:

```text
Search customer, invoice, phone, serial number, MAC, transaction...
```

Global search should search relevant modules.

---

# FILTERS

Major data tables should support:

```text
Search
Status
Date Range
Category
Location
PIC
Customer
Supplier
Role
Payment Status
```

---

# TABLES

Tables should support:

```text
Pagination
Sorting
Search
Filter
Bulk Selection
Column Visibility
Export
```

Keep table UI modern and readable.

---

# MODAL AND FORM RULES

Use modals only for simple forms or confirmations.

Use dedicated pages for complex forms.

Every form must have:

```text
Labels
Validation
Helpful Error Messages
Loading State
Success Feedback
Cancel Action
```

Do not rely only on browser validation.

Validate everything in PHP backend.

---

# SECURITY

Implement:

```text
Prepared Statements
CSRF Protection
Session Security
Output Escaping
XSS Prevention
File Upload Validation
Permission Validation
Rate Limiting Where Needed
Login Attempt Protection
Secure Password Hashing
```

Never trust user input.

Escape output with:

```php
htmlspecialchars()
```

where applicable.

---

# SETTINGS

Menu:

```text
Pengaturan
├── Profil Perusahaan
├── Pengguna
├── Role & Permission
├── Template Invoice
├── AI Provider
├── Integrasi
├── Notification
├── Backup
└── Pengaturan Sistem
```

---

# COMPANY PROFILE

Fields:

```text
Company Name
Brand Name
Logo
Address
Phone
WhatsApp
Email
Website
Tax Number
Bank Accounts
Invoice Footer
Company Notes
```

---

# INVOICE TEMPLATE

Allow configuration of:

```text
Logo
Company Header
Invoice Prefix
Number Format
Colors
Footer
Bank Account
Payment Instructions
Terms and Conditions
Signature
```

Invoice should be printable.

Support PDF generation.

---

# BACKUP

Create backup tools.

Support:

```text
Manual Database Backup
Automatic Backup
Attachment Backup
Backup History
Restore
Download Backup
```

Display:

```text
Last Successful Backup
Backup Size
Backup Status
```

---

# ACTIVITY CENTER

Provide owner/admin alerts.

Examples:

```text
17 invoices due today
6 products are out of stock
12 tickets remain open
3 RAB waiting approval
4 overdue invoices above 60 days
1 backup failed
```

---

# EXECUTIVE KPI

Recommended KPI:

```text
MRR
Revenue
Expenses
Net Profit
Profit Margin
ARPU
Customer Growth
Customer Churn
Collection Rate
Accounts Receivable
Cash Balance
Inventory Value
Asset Value
Ticket SLA
```

---

# NUMBER FORMATTING

Use Indonesian number formatting.

Currency:

```text
Rp 1.250.000
```

Date:

```text
17 Agustus 2026
```

or compact:

```text
17/08/2026
```

Use consistent formatting throughout the application.

---

# STATUS BADGES

Use clean semantic badges.

Example:

```text
Active
Paid
Completed
```

Use success styling.

```text
Pending
Overdue
Low Stock
```

Use warning styling.

```text
Suspended
Rejected
Out of Stock
Cancelled
```

Use danger styling.

---

# EMPTY STATES

Every empty page must provide useful guidance.

Example:

```text
Belum ada pelanggan.

Tambahkan pelanggan pertama untuk mulai mengelola layanan internet.
```

Provide relevant action button.

---

# ERROR HANDLING

Do not expose raw PHP errors to end users in production.

Create:

```text
403
404
419 / CSRF Error
500
```

pages.

Write technical errors to logs.

---

# LOGGING

Store application logs in:

```text
/storage/logs/
```

Log important errors.

Do not log:

```text
Plain Password
Full API Key
Sensitive Authentication Tokens
```

---

# PERFORMANCE

Avoid N+1 queries.

Use pagination.

Do not load thousands of records into a single table.

Add indexes where appropriate.

Cache only where useful.

Keep JavaScript lightweight.

---

# SQLITE SAFETY

SQLite requires directory write permission.

Application deployment must ensure both:

```text
/database/
```

and:

```text
/database/isp.sqlite
```

are writable by the PHP/web server user.

SQLite may create:

```text
isp.sqlite-wal
isp.sqlite-shm
isp.sqlite-journal
```

Never assume only the `.sqlite` file requires write permission.

---

# DATABASE TABLE GROUPS

Recommended table groups:

```text
AUTH

users
roles
permissions
role_permissions
user_roles
sessions
```

```text
CUSTOMER

customers
customer_pics
internet_packages
locations
customer_documents
customer_status_histories
```

```text
BILLING

billing_cycles
invoices
invoice_items
payments
payment_allocations
billing_logs
```

```text
FINANCE

finance_accounts
finance_categories
finance_transactions
budgets
budget_items
```

```text
PAYROLL

employees
departments
payroll_periods
payroll_runs
payroll_items
payroll_components
employee_bonus_records
employee_deductions
```

```text
RAB

rabs
rab_categories
rab_items
rab_realizations
rab_realization_items
```

```text
INVENTORY

inventory_categories
inventory_items
warehouses
inventory_transactions
inventory_transaction_items
suppliers
stock_opnames
stock_opname_items
```

```text
ASSETS

assets
asset_histories
asset_assignments
asset_maintenance
```

```text
AI

ai_providers
ai_conversations
ai_messages
ai_tool_logs
ai_usage_logs
```

```text
OPERATIONS

tickets
ticket_histories
network_pops
network_olts
network_pon_ports
network_odps
network_devices
customer_network_connections
```

```text
SYSTEM

company_profile
invoice_templates
app_settings
notifications
activity_logs
approval_requests
attachments
```

---

# DATA INTEGRITY RULES

All critical operations must maintain data integrity.

Examples:

```text
Payment
→ update invoice balance
→ update invoice status
→ create finance transaction
→ create audit log
```

These actions must run inside a database transaction.

Another example:

```text
Goods Out
→ validate stock
→ create transaction
→ reduce stock
→ optionally create asset assignment
→ create audit log
```

Run as one transaction.

If one step fails, rollback all steps.

---

# AUTOMATIC DOCUMENT NUMBERING

Create configurable document numbering.

Examples:

```text
Customer
CUST-000001

Invoice
INV-202608-000001

Payment
PAY-202608-000001

Finance
TRX-202608-000001

RAB
RAB-202608-000001

Goods In
GIN-202608-000001

Goods Out
GOUT-202608-000001

Asset
AST-000001

Ticket
TKT-202608-000001
```

Numbering must not create duplicates.

---

# DASHBOARD UX

Dashboard must prioritize useful information rather than decorative cards.

Example layout:

```text
Header

KPI Row

Revenue / Expense Chart

Billing Collection Chart

Cashflow

Customer Growth

Receivable Aging

Low Stock

Open Tickets

Recent Activity

Alerts
```

---

# SIDEBAR

Recommended sidebar:

```text
Dashboard

CUSTOMER
Pelanggan

BILLING
Billing

OPERATIONS
Ticketing
Network

FINANCE
Keuangan
Payroll
RAB
Laba Rugi

ASSET & STOCK
Inventory & Asset
Procurement

INTELLIGENCE
AI Assistant
Analytics

SYSTEM
Notifikasi
Audit Log
Pengaturan
```

Keep sidebar section labels subtle.

---

# DEVELOPMENT PHASES

Do not attempt to complete all complexity at once.

## PHASE 1

Build:

```text
Application Shell
Authentication
Users
Role & Permission
Company Profile
Settings
Dashboard Base
Database Migration System
```

---

## PHASE 2

Build:

```text
Customers
PIC
Packages
Locations
Customer Import
Customer Export
```

---

## PHASE 3

Build:

```text
Billing Cycle
Invoice
Payments
Accounts Receivable
Finance
Cash & Bank
```

This phase must be stable before advanced AI development.

---

## PHASE 4

Build:

```text
Inventory
Assets
Goods In
Goods Out
Stock Opname
Suppliers
RAB
```

---

## PHASE 5

Build:

```text
Payroll
Budget
Profit & Loss
Executive Analytics
```

---

## PHASE 6

Build:

```text
AI Assistant
AI Providers
AI Data Tools
Financial Advisor
Forecast
```

---

## PHASE 7

Build:

```text
Ticketing
Network Infrastructure
Procurement
WhatsApp
MikroTik / RADIUS
Payment Gateway
Advanced Automation
```

---

# CODING QUALITY

Code must be:

```text
Readable
Modular
Maintainable
Secure
Consistent
Documented where necessary
```

Avoid unnecessary abstraction.

Do not create overly complex enterprise architecture for simple functionality.

However, do not mix database, business logic, HTML, and JavaScript inside one giant file.

---

# PHP NAMING

Use consistent class naming.

Examples:

```text
CustomerController
CustomerModel
BillingService
InvoiceService
InventoryService
AIManager
PermissionMiddleware
```

Use meaningful method names.

Avoid names like:

```text
doStuff()
processData()
func1()
```

---

# FRONTEND JAVASCRIPT

Use Vanilla JavaScript.

Use it for:

```text
Dropdown
Modal
Sidebar
Filters
AJAX
Dynamic Forms
Toast
Confirmation
Charts
Autocomplete
```

Do not place all JavaScript in a single giant inline `<script>`.

Create modular files.

---

# AJAX

AJAX may be used for user experience improvements.

Use JSON responses consistently.

Example:

```json
{
  "success": true,
  "message": "Data berhasil disimpan.",
  "data": {}
}
```

Error:

```json
{
  "success": false,
  "message": "Data gagal disimpan.",
  "errors": {}
}
```

---

# CONFIRMATION

Critical actions require confirmation.

Examples:

```text
Delete
Cancel Invoice
Void Payment
Stock Adjustment
Approve Payroll
Dispose Asset
Suspend Customer
```

Explain the impact clearly.

---

# AI ANSWER PRINCIPLE

The AI must follow:

```text
DATABASE
= source of truth

APPLICATION ENGINE
= calculates

AI
= searches
  analyzes
  explains
  compares
  summarizes
  recommends
```

Never allow:

```text
AI
= invents business numbers
```

---

# BUSINESS DECISION AI

For questions such as:

```text
Can we afford an OLT this month?
```

AI must consider:

```text
Cash
Receivables
Expected Collections
Expected Revenue
Payroll
Bandwidth
Operational Expenses
Supplier Debt
Approved RAB
Upcoming Obligations
Minimum Cash Reserve
```

Provide:

```text
Current Position
Projected Position
Scenario
Risk
Recommendation
Assumptions
```

Do not answer only:

```text
Yes
```

or:

```text
No
```

---

# FINAL PRODUCT STANDARD

The final application should function as a compact ISP / RT-RW Net ERP.

It should integrate:

```text
Customers
      ↓
Billing
      ↓
Payments
      ↓
Finance
   ↙   ↓   ↘
Payroll RAB Operations
          ↓
      Inventory
          ↓
        Assets
          ↓
      Profit & Loss
          ↓
     AI Assistant
```

Every important number must be traceable.

Every critical change must be auditable.

Every role must access only permitted data.

The UI must remain modern, responsive, clean, and professional.

The system must not use emoji for navigation or actions.

Use Font Awesome consistently.

SQLite is the initial database, but the architecture must remain migration-friendly for future MySQL/MariaDB deployment.