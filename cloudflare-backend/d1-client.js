// Cloudflare D1 REST API Client
// Uses Cloudflare API directly to query D1 database

const ACCOUNT_ID = 'b3287bd86214fb07b212a90d22ab79fb';
const DATABASE_ID = 'ad67f907-3e85-45f1-a924-80db1e2117cd';
const API_TOKEN = 'UomdabbnM-IzlXcvXTZseuKfT3AsVZE6iancgvdA7H0.1ovVPBYK0-PcIMy7gYXtiGtYzP1RptjiZ1IV1rsgp1M';

class D1Client {
    constructor() {
        this.baseUrl = `https://api.cloudflare.com/client/v4/accounts/${ACCOUNT_ID}/d1/database/${DATABASE_ID}`;
    }

    async query(sql, params = []) {
        const response = await fetch(`${this.baseUrl}/query`, {
            method: 'POST',
            headers: {
                'Authorization': `Bearer ${API_TOKEN}`,
                'Content-Type': 'application/json'
            },
            body: JSON.stringify({
                sql: sql,
                params: params
            })
        });
        
        const data = await response.json();
        if (!data.success) {
            console.error('D1 Error:', data.errors);
            throw new Error(data.errors?.[0]?.message || 'Query failed');
        }
        return data.result;
    }

    async getOrders() {
        const result = await this.query('SELECT * FROM orders ORDER BY created_at DESC LIMIT 1000');
        return result[0]?.results || [];
    }

    async createOrder(order) {
        const sql = `INSERT INTO orders (id, name, phone, country, product, clickid, utm_campaign, utm_source, status)
                     VALUES (?, ?, ?, ?, ?, ?, ?, ?, 'pending')`;
        const id = 'ord_' + Date.now().toString(36) + Math.random().toString(36).substr(2, 9);
        await this.query(sql, [id, order.name, order.phone, order.country || 'IN', order.product || 'Libidex', order.clickid || '', order.utm_campaign || '', order.utm_source || '']);
        return id;
    }

    async updateOrderStatus(id, status) {
        await this.query('UPDATE orders SET status = ? WHERE id = ?', [status, id]);
    }

    async deleteOrder(id) {
        await this.query('DELETE FROM orders WHERE id = ?', [id]);
    }

    async getProducts() {
        const result = await this.query("SELECT * FROM products WHERE status = 'active'");
        return result[0]?.results || [];
    }

    async updateProduct(id, updates) {
        const fields = [];
        const values = [];
        
        for (const [key, value] of Object.entries(updates)) {
            if (value !== undefined && key !== 'id') {
                fields.push(`${key} = ?`);
                values.push(value);
            }
        }
        
        if (fields.length > 0) {
            values.push(id);
            await this.query(`UPDATE products SET ${fields.join(', ')} WHERE id = ?`, values);
        }
    }

    async getReviews() {
        const result = await this.query("SELECT * FROM reviews WHERE status = 'active' ORDER BY id ASC");
        return result[0]?.results || [];
    }
}

// Create global instance
const d1 = new D1Client();

// API Functions for frontend
async function getOrders() {
    return await d1.getOrders();
}

async function createOrder(orderData) {
    return await d1.createOrder(orderData);
}

async function updateOrder(id, status) {
    return await d1.updateOrderStatus(id, status);
}

async function deleteOrder(id) {
    return await d1.deleteOrder(id);
}

async function getProducts() {
    return await d1.getProducts();
}

async function updateProduct(id, updates) {
    return await d1.updateProduct(id, updates);
}

async function getReviews() {
    return await d1.getReviews();
}

async function healthCheck() {
    try {
        await d1.query('SELECT 1');
        return { status: 'ok', timestamp: new Date().toISOString() };
    } catch (e) {
        return { status: 'error', message: e.message };
    }
}

// Export for use
if (typeof module !== 'undefined' && module.exports) {
    module.exports = { D1Client, getOrders, createOrder, updateOrder, deleteOrder, getProducts, updateProduct, getReviews, healthCheck };
}
