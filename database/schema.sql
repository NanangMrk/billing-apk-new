-- ISP / RT-RW Net Management System Database Schema (SQLite)
PRAGMA foreign_keys = ON;

-- 1. AUTH & ROLES
CREATE TABLE IF NOT EXISTS roles (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    name TEXT NOT NULL UNIQUE,
    display_name TEXT NOT NULL,
    description TEXT,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE IF NOT EXISTS permissions (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    name TEXT NOT NULL UNIQUE,
    category TEXT NOT NULL,
    description TEXT,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE IF NOT EXISTS role_permissions (
    role_id INTEGER NOT NULL,
    permission_id INTEGER NOT NULL,
    PRIMARY KEY (role_id, permission_id),
    FOREIGN KEY (role_id) REFERENCES roles(id) ON DELETE CASCADE,
    FOREIGN KEY (permission_id) REFERENCES permissions(id) ON DELETE CASCADE
);

CREATE TABLE IF NOT EXISTS users (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    role_id INTEGER NOT NULL,
    name TEXT NOT NULL,
    username TEXT NOT NULL UNIQUE,
    email TEXT NOT NULL UNIQUE,
    phone TEXT,
    password TEXT NOT NULL,
    status TEXT NOT NULL DEFAULT 'active', -- active, inactive, suspended
    avatar TEXT,
    last_login DATETIME,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (role_id) REFERENCES roles(id)
);

-- 2. MASTER DATA (PACKAGES, LOCATIONS, PIC, SUPPLIERS, WAREHOUSES)
CREATE TABLE IF NOT EXISTS internet_packages (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    name TEXT NOT NULL,
    download_speed TEXT NOT NULL,
    upload_speed TEXT NOT NULL,
    price INTEGER NOT NULL DEFAULT 0, -- Stored in Rupiah as integer
    tax_percent INTEGER NOT NULL DEFAULT 0,
    installation_fee INTEGER NOT NULL DEFAULT 0,
    billing_cycle TEXT NOT NULL DEFAULT 'monthly', -- monthly, quarterly, yearly
    description TEXT,
    status TEXT NOT NULL DEFAULT 'active', -- active, inactive
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE IF NOT EXISTS locations (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    area_name TEXT NOT NULL,
    district TEXT,
    city TEXT,
    pop_name TEXT,
    odp_name TEXT,
    latitude REAL,
    longitude REAL,
    coverage_status TEXT NOT NULL DEFAULT 'covered', -- covered, planned, congested
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE IF NOT EXISTS customer_pics (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    name TEXT NOT NULL,
    phone TEXT NOT NULL,
    whatsapp TEXT,
    email TEXT,
    position TEXT,
    company TEXT,
    notes TEXT,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE IF NOT EXISTS suppliers (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    name TEXT NOT NULL,
    company TEXT,
    contact_person TEXT,
    phone TEXT,
    email TEXT,
    address TEXT,
    status TEXT NOT NULL DEFAULT 'active',
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE IF NOT EXISTS warehouses (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    name TEXT NOT NULL,
    location TEXT,
    pic_name TEXT,
    notes TEXT,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP
);

-- 3. CUSTOMER MANAGEMENT
CREATE TABLE IF NOT EXISTS billing_cycles (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    name TEXT NOT NULL,
    generate_day INTEGER NOT NULL DEFAULT 1, -- e.g. 1st of month
    due_day INTEGER NOT NULL DEFAULT 10,     -- e.g. 10th of month
    grace_period_days INTEGER NOT NULL DEFAULT 3,
    auto_suspend_days INTEGER NOT NULL DEFAULT 5,
    description TEXT,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE IF NOT EXISTS customers (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    customer_no TEXT NOT NULL UNIQUE,
    name TEXT NOT NULL,
    phone TEXT NOT NULL,
    whatsapp TEXT,
    email TEXT,
    pic_id INTEGER,
    package_id INTEGER NOT NULL,
    location_id INTEGER,
    billing_cycle_id INTEGER NOT NULL,
    full_address TEXT NOT NULL,
    latitude REAL,
    longitude REAL,
    odp_point TEXT,
    pop_point TEXT,
    ip_address TEXT,
    pppoe_username TEXT,
    pppoe_password TEXT,
    installation_date DATE,
    activation_date DATE,
    billing_start_date DATE,
    status TEXT NOT NULL DEFAULT 'active', -- prospect, installation, active, suspended, inactive, terminated
    notes TEXT,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (pic_id) REFERENCES customer_pics(id) ON DELETE SET NULL,
    FOREIGN KEY (package_id) REFERENCES internet_packages(id),
    FOREIGN KEY (location_id) REFERENCES locations(id) ON DELETE SET NULL,
    FOREIGN KEY (billing_cycle_id) REFERENCES billing_cycles(id)
);

-- 4. BILLING & INVOICES
CREATE TABLE IF NOT EXISTS invoices (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    invoice_no TEXT NOT NULL UNIQUE,
    customer_id INTEGER NOT NULL,
    billing_period TEXT NOT NULL, -- e.g. '2026-08'
    package_name_snapshot TEXT NOT NULL,
    subtotal INTEGER NOT NULL DEFAULT 0,
    discount INTEGER NOT NULL DEFAULT 0,
    tax INTEGER NOT NULL DEFAULT 0,
    additional_fee INTEGER NOT NULL DEFAULT 0,
    previous_balance INTEGER NOT NULL DEFAULT 0,
    grand_total INTEGER NOT NULL DEFAULT 0,
    paid_amount INTEGER NOT NULL DEFAULT 0,
    balance_due INTEGER NOT NULL DEFAULT 0,
    issue_date DATE NOT NULL,
    due_date DATE NOT NULL,
    payment_status TEXT NOT NULL DEFAULT 'unpaid', -- draft, unpaid, partially_paid, paid, overdue, cancelled, void
    payment_date DATETIME,
    payment_method TEXT,
    notes TEXT,
    created_by INTEGER,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (customer_id) REFERENCES customers(id) ON DELETE CASCADE,
    FOREIGN KEY (created_by) REFERENCES users(id) ON DELETE SET NULL
);

CREATE TABLE IF NOT EXISTS invoice_items (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    invoice_id INTEGER NOT NULL,
    item_name TEXT NOT NULL,
    quantity INTEGER NOT NULL DEFAULT 1,
    unit_price INTEGER NOT NULL DEFAULT 0,
    subtotal INTEGER NOT NULL DEFAULT 0,
    notes TEXT,
    FOREIGN KEY (invoice_id) REFERENCES invoices(id) ON DELETE CASCADE
);

-- 5. FINANCE MODULE
CREATE TABLE IF NOT EXISTS finance_accounts (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    account_name TEXT NOT NULL,
    account_type TEXT NOT NULL DEFAULT 'bank', -- cash, bank, qris, gateway
    bank_name TEXT,
    account_number TEXT,
    opening_balance INTEGER NOT NULL DEFAULT 0,
    current_balance INTEGER NOT NULL DEFAULT 0,
    status TEXT NOT NULL DEFAULT 'active',
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE IF NOT EXISTS finance_categories (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    name TEXT NOT NULL,
    type TEXT NOT NULL, -- income, expense
    description TEXT,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE IF NOT EXISTS finance_transactions (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    transaction_no TEXT NOT NULL UNIQUE,
    transaction_date DATE NOT NULL,
    account_id INTEGER NOT NULL,
    category_id INTEGER NOT NULL,
    type TEXT NOT NULL, -- income, expense, transfer, adjustment
    amount INTEGER NOT NULL DEFAULT 0,
    description TEXT NOT NULL,
    reference_type TEXT, -- invoice, payroll, rab, inventory, manual
    reference_id INTEGER,
    attachment TEXT,
    created_by INTEGER,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (account_id) REFERENCES finance_accounts(id),
    FOREIGN KEY (category_id) REFERENCES finance_categories(id),
    FOREIGN KEY (created_by) REFERENCES users(id) ON DELETE SET NULL
);

CREATE TABLE IF NOT EXISTS payments (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    payment_no TEXT NOT NULL UNIQUE,
    invoice_id INTEGER NOT NULL,
    customer_id INTEGER NOT NULL,
    account_id INTEGER NOT NULL,
    amount INTEGER NOT NULL,
    payment_date DATE NOT NULL,
    payment_method TEXT NOT NULL, -- Cash, BCA, Mandiri, QRIS, etc.
    reference_no TEXT,
    proof_file TEXT,
    notes TEXT,
    received_by INTEGER,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (invoice_id) REFERENCES invoices(id) ON DELETE CASCADE,
    FOREIGN KEY (customer_id) REFERENCES customers(id),
    FOREIGN KEY (account_id) REFERENCES finance_accounts(id),
    FOREIGN KEY (received_by) REFERENCES users(id) ON DELETE SET NULL
);

CREATE TABLE IF NOT EXISTS budgets (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    period_year INTEGER NOT NULL,
    period_month INTEGER NOT NULL,
    category_id INTEGER NOT NULL,
    budget_amount INTEGER NOT NULL DEFAULT 0,
    actual_amount INTEGER NOT NULL DEFAULT 0,
    notes TEXT,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (category_id) REFERENCES finance_categories(id)
);

-- 6. PAYROLL MODULE
CREATE TABLE IF NOT EXISTS departments (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    name TEXT NOT NULL,
    description TEXT,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE IF NOT EXISTS employees (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    employee_no TEXT NOT NULL UNIQUE,
    name TEXT NOT NULL,
    department_id INTEGER,
    position TEXT NOT NULL,
    phone TEXT,
    email TEXT,
    basic_salary INTEGER NOT NULL DEFAULT 0,
    bank_name TEXT,
    bank_account TEXT,
    employment_status TEXT NOT NULL DEFAULT 'permanent', -- permanent, contract, intern, technician
    join_date DATE,
    status TEXT NOT NULL DEFAULT 'active',
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (department_id) REFERENCES departments(id) ON DELETE SET NULL
);

CREATE TABLE IF NOT EXISTS payroll_runs (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    payroll_no TEXT NOT NULL UNIQUE,
    period_month TEXT NOT NULL, -- '2026-08'
    total_amount INTEGER NOT NULL DEFAULT 0,
    status TEXT NOT NULL DEFAULT 'draft', -- draft, calculated, reviewed, approved, paid
    paid_date DATE,
    created_by INTEGER,
    approved_by INTEGER,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (created_by) REFERENCES users(id) ON DELETE SET NULL,
    FOREIGN KEY (approved_by) REFERENCES users(id) ON DELETE SET NULL
);

CREATE TABLE IF NOT EXISTS payroll_items (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    payroll_run_id INTEGER NOT NULL,
    employee_id INTEGER NOT NULL,
    basic_salary INTEGER NOT NULL DEFAULT 0,
    allowances INTEGER NOT NULL DEFAULT 0,
    overtime_bonus INTEGER NOT NULL DEFAULT 0,
    deductions INTEGER NOT NULL DEFAULT 0,
    net_salary INTEGER NOT NULL DEFAULT 0,
    payment_status TEXT NOT NULL DEFAULT 'unpaid', -- unpaid, paid
    notes TEXT,
    FOREIGN KEY (payroll_run_id) REFERENCES payroll_runs(id) ON DELETE CASCADE,
    FOREIGN KEY (employee_id) REFERENCES employees(id)
);

-- 7. RAB (RENCANA ANGGARAN BIAYA)
CREATE TABLE IF NOT EXISTS rab_categories (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    name TEXT NOT NULL,
    description TEXT
);

CREATE TABLE IF NOT EXISTS rabs (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    rab_no TEXT NOT NULL UNIQUE,
    project_name TEXT NOT NULL,
    category_id INTEGER,
    location TEXT,
    pic_name TEXT,
    customer_id INTEGER,
    start_date DATE,
    end_date DATE,
    budget_total INTEGER NOT NULL DEFAULT 0,
    realized_total INTEGER NOT NULL DEFAULT 0,
    status TEXT NOT NULL DEFAULT 'draft', -- draft, submitted, approved, rejected, in_progress, completed, cancelled
    description TEXT,
    created_by INTEGER,
    approved_by INTEGER,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (category_id) REFERENCES rab_categories(id) ON DELETE SET NULL,
    FOREIGN KEY (customer_id) REFERENCES customers(id) ON DELETE SET NULL,
    FOREIGN KEY (created_by) REFERENCES users(id) ON DELETE SET NULL,
    FOREIGN KEY (approved_by) REFERENCES users(id) ON DELETE SET NULL
);

CREATE TABLE IF NOT EXISTS rab_items (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    rab_id INTEGER NOT NULL,
    item_name TEXT NOT NULL,
    category TEXT,
    quantity INTEGER NOT NULL DEFAULT 1,
    unit TEXT NOT NULL DEFAULT 'pcs',
    unit_price INTEGER NOT NULL DEFAULT 0,
    subtotal INTEGER NOT NULL DEFAULT 0,
    realized_subtotal INTEGER NOT NULL DEFAULT 0,
    notes TEXT,
    FOREIGN KEY (rab_id) REFERENCES rabs(id) ON DELETE CASCADE
);

-- 8. INVENTORY & ASSET MANAGEMENT
CREATE TABLE IF NOT EXISTS inventory_categories (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    name TEXT NOT NULL,
    description TEXT
);

CREATE TABLE IF NOT EXISTS inventory_items (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    sku TEXT NOT NULL UNIQUE,
    name TEXT NOT NULL,
    category_id INTEGER,
    brand TEXT,
    model TEXT,
    unit TEXT NOT NULL DEFAULT 'pcs',
    purchase_price INTEGER NOT NULL DEFAULT 0,
    selling_price INTEGER NOT NULL DEFAULT 0,
    min_stock INTEGER NOT NULL DEFAULT 5,
    current_stock INTEGER NOT NULL DEFAULT 0,
    warehouse_id INTEGER,
    supplier_id INTEGER,
    image TEXT,
    description TEXT,
    status TEXT NOT NULL DEFAULT 'active',
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (category_id) REFERENCES inventory_categories(id) ON DELETE SET NULL,
    FOREIGN KEY (warehouse_id) REFERENCES warehouses(id) ON DELETE SET NULL,
    FOREIGN KEY (supplier_id) REFERENCES suppliers(id) ON DELETE SET NULL
);

CREATE TABLE IF NOT EXISTS inventory_transactions (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    transaction_no TEXT NOT NULL UNIQUE,
    transaction_date DATE NOT NULL,
    type TEXT NOT NULL, -- in, out, transfer, adjustment, opname
    item_id INTEGER NOT NULL,
    warehouse_id INTEGER,
    quantity INTEGER NOT NULL,
    unit_price INTEGER NOT NULL DEFAULT 0,
    total_amount INTEGER NOT NULL DEFAULT 0,
    destination_type TEXT, -- customer, technician, project, department, damaged, lost
    customer_id INTEGER,
    reference_no TEXT,
    notes TEXT,
    recipient_name TEXT,
    photo TEXT,
    created_by INTEGER,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (item_id) REFERENCES inventory_items(id),
    FOREIGN KEY (warehouse_id) REFERENCES warehouses(id) ON DELETE SET NULL,
    FOREIGN KEY (customer_id) REFERENCES customers(id) ON DELETE SET NULL,
    FOREIGN KEY (created_by) REFERENCES users(id) ON DELETE SET NULL
);

CREATE TABLE IF NOT EXISTS assets (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    asset_no TEXT NOT NULL UNIQUE,
    item_id INTEGER,
    name TEXT NOT NULL,
    serial_number TEXT,
    mac_address TEXT,
    purchase_date DATE,
    purchase_price INTEGER NOT NULL DEFAULT 0,
    current_value INTEGER NOT NULL DEFAULT 0,
    condition TEXT NOT NULL DEFAULT 'good', -- good, fair, poor, damaged
    location TEXT,
    customer_id INTEGER,
    pic_name TEXT,
    status TEXT NOT NULL DEFAULT 'available', -- available, in_use, assigned_customer, maintenance, damaged, lost, disposed
    notes TEXT,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (item_id) REFERENCES inventory_items(id) ON DELETE SET NULL,
    FOREIGN KEY (customer_id) REFERENCES customers(id) ON DELETE SET NULL
);

CREATE TABLE IF NOT EXISTS stock_opnames (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    opname_no TEXT NOT NULL UNIQUE,
    opname_date DATE NOT NULL,
    warehouse_id INTEGER,
    status TEXT NOT NULL DEFAULT 'draft', -- draft, completed, adjusted
    notes TEXT,
    created_by INTEGER,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (warehouse_id) REFERENCES warehouses(id) ON DELETE SET NULL,
    FOREIGN KEY (created_by) REFERENCES users(id) ON DELETE SET NULL
);

-- 9. TICKETING & OPERATIONS
CREATE TABLE IF NOT EXISTS tickets (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    ticket_no TEXT NOT NULL UNIQUE,
    customer_id INTEGER NOT NULL,
    title TEXT NOT NULL,
    description TEXT NOT NULL,
    category TEXT NOT NULL DEFAULT 'connection_down', -- connection_down, slow_speed, cable_cut, device_fault, billing_inquiry, relocation
    priority TEXT NOT NULL DEFAULT 'medium', -- low, medium, high, urgent
    technician_id INTEGER,
    status TEXT NOT NULL DEFAULT 'open', -- open, assigned, in_progress, waiting, resolved, closed, cancelled
    reported_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    resolved_at DATETIME,
    resolution_notes TEXT,
    created_by INTEGER,
    FOREIGN KEY (customer_id) REFERENCES customers(id) ON DELETE CASCADE,
    FOREIGN KEY (technician_id) REFERENCES users(id) ON DELETE SET NULL,
    FOREIGN KEY (created_by) REFERENCES users(id) ON DELETE SET NULL
);

-- 10. AI ASSISTANT & SYSTEM
CREATE TABLE IF NOT EXISTS ai_providers (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    name TEXT NOT NULL,
    provider_type TEXT NOT NULL, -- openai, gemini, anthropic, openrouter, custom
    api_key TEXT,
    base_url TEXT,
    model TEXT NOT NULL DEFAULT 'gpt-4o-mini',
    temperature REAL DEFAULT 0.7,
    max_tokens INTEGER DEFAULT 2048,
    is_active INTEGER NOT NULL DEFAULT 1,
    is_default INTEGER NOT NULL DEFAULT 1,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE IF NOT EXISTS ai_conversations (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    user_id INTEGER NOT NULL,
    title TEXT NOT NULL DEFAULT 'Percakapan Baru',
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);

CREATE TABLE IF NOT EXISTS ai_messages (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    conversation_id INTEGER NOT NULL,
    role TEXT NOT NULL, -- user, assistant, system
    content TEXT NOT NULL,
    meta_json TEXT, -- tool calls, calculation snapshots
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (conversation_id) REFERENCES ai_conversations(id) ON DELETE CASCADE
);

CREATE TABLE IF NOT EXISTS company_profile (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    company_name TEXT NOT NULL DEFAULT 'PT Nusantara Net Mandiri',
    brand_name TEXT NOT NULL DEFAULT 'NusantaraNet ISP',
    logo TEXT,
    address TEXT DEFAULT 'Jl. Fiber Optik No. 88, Cyber City, Jakarta',
    phone TEXT DEFAULT '021-88997700',
    whatsapp TEXT DEFAULT '081234567890',
    email TEXT DEFAULT 'info@nusantaranet.id',
    website TEXT DEFAULT 'https://nusantaranet.id',
    tax_number TEXT DEFAULT '01.234.567.8-901.000',
    bank_account_info TEXT DEFAULT 'BCA: 1234567890 a/n PT Nusantara Net Mandiri',
    invoice_footer TEXT DEFAULT 'Terima kasih telah menggunakan layanan internet berkecepatan tinggi kami.',
    updated_at DATETIME DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE IF NOT EXISTS activity_logs (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    user_id INTEGER,
    module TEXT NOT NULL,
    action TEXT NOT NULL,
    record_id TEXT,
    old_value TEXT,
    new_value TEXT,
    ip_address TEXT,
    user_agent TEXT,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE SET NULL
);

-- INDEXES FOR MAXIMUM QUERY PERFORMANCE
CREATE INDEX IF NOT EXISTS idx_customers_status ON customers(status);
CREATE INDEX IF NOT EXISTS idx_customers_package ON customers(package_id);
CREATE INDEX IF NOT EXISTS idx_invoices_status ON invoices(payment_status);
CREATE INDEX IF NOT EXISTS idx_invoices_due ON invoices(due_date);
CREATE INDEX IF NOT EXISTS idx_invoices_customer ON invoices(customer_id);
CREATE INDEX IF NOT EXISTS idx_finance_trans_date ON finance_transactions(transaction_date);
CREATE INDEX IF NOT EXISTS idx_finance_trans_acc ON finance_transactions(account_id);
CREATE INDEX IF NOT EXISTS idx_inventory_sku ON inventory_items(sku);
CREATE INDEX IF NOT EXISTS idx_tickets_status ON tickets(status);
CREATE INDEX IF NOT EXISTS idx_logs_module ON activity_logs(module);
