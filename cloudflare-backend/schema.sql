-- Orders Table
CREATE TABLE IF NOT EXISTS orders (
    id TEXT PRIMARY KEY,
    name TEXT NOT NULL,
    phone TEXT NOT NULL,
    country TEXT DEFAULT 'IN',
    product TEXT DEFAULT 'Libidex',
    clickid TEXT DEFAULT '',
    utm_campaign TEXT DEFAULT '',
    utm_source TEXT DEFAULT '',
    utm_medium TEXT DEFAULT '',
    utm_content TEXT DEFAULT '',
    status TEXT DEFAULT 'pending',
    created_at TEXT DEFAULT (datetime('now', '+5 hours 30 minutes'))
);

-- Products Table
CREATE TABLE IF NOT EXISTS products (
    id INTEGER PRIMARY KEY,
    name TEXT DEFAULT 'Libidex',
    name_hindi TEXT DEFAULT '',
    tagline TEXT DEFAULT '',
    description TEXT DEFAULT '',
    price REAL DEFAULT 2490,
    old_price REAL DEFAULT 4980,
    image TEXT DEFAULT 'product-1.png',
    status TEXT DEFAULT 'active',
    updated_at TEXT DEFAULT (datetime('now', '+5 hours 30 minutes'))
);

-- Reviews Table
CREATE TABLE IF NOT EXISTS reviews (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    name TEXT NOT NULL,
    age TEXT DEFAULT '',
    review_text TEXT NOT NULL,
    image TEXT DEFAULT 'live-1.jpg',
    status TEXT DEFAULT 'active',
    created_at TEXT DEFAULT (datetime('now', '+5 hours 30 minutes'))
);

-- Insert default product if not exists
INSERT OR IGNORE INTO products (id, name, price, old_price) VALUES (1, 'Libidex', 2490, 4980);
INSERT OR IGNORE INTO products (id, name, price, old_price) VALUES (2, 'ProMan', 1990, 3990);

-- Insert sample reviews
INSERT OR IGNORE INTO reviews (id, name, review_text) VALUES 
(1, 'राजेश, 42', 'मैं 3 महीने से ज्यादा समय से Libidex ले रहा हूं। परिणाम शानदार हैं!'),
(2, 'अमित, 38', '6 हफ्ते में बहुत सुधार हुआ। मेरी पत्नी भी खुश है।'),
(3, 'संजय, 45', 'बेहतर ऊर्जा और आत्मविश्वास। Recommended!');

-- Create indexes
CREATE INDEX IF NOT EXISTS idx_orders_created ON orders(created_at DESC);
CREATE INDEX IF NOT EXISTS idx_orders_product ON orders(product);
CREATE INDEX IF NOT EXISTS idx_orders_status ON orders(status);
