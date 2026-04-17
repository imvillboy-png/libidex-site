const express = require('express');
const cors = require('cors');
const fs = require('fs');
const path = require('path');

const app = express();
const PORT = process.env.PORT || 3000;
const DATA_FILE = path.join(__dirname, 'data.json');

app.use(cors());
app.use(express.json());

let data = { orders: [], products: [], reviews: [] };

function loadData() {
    try {
        if (fs.existsSync(DATA_FILE)) {
            const fileData = fs.readFileSync(DATA_FILE, 'utf8');
            data = JSON.parse(fileData);
        }
    } catch (e) {
        console.log('Error loading data:', e);
    }
}

function saveData() {
    fs.writeFileSync(DATA_FILE, JSON.stringify(data, null, 2));
}

loadData();

// Orders API
app.get('/api/orders', (req, res) => {
    res.json({ success: true, orders: data.orders });
});

app.post('/api/orders', (req, res) => {
    const order = req.body;
    order.id = 'ord_' + Date.now().toString(36) + Math.random().toString(36).substr(2, 9);
    order.status = 'pending';
    order.created_at = new Date().toISOString();
    data.orders.unshift(order);
    saveData();
    res.json({ success: true, order });
});

app.put('/api/orders', (req, res) => {
    const { id, status } = req.body;
    const order = data.orders.find(o => o.id === id);
    if (order) {
        order.status = status;
        saveData();
    }
    res.json({ success: true });
});

app.delete('/api/orders', (req, res) => {
    const { id } = req.body;
    data.orders = data.orders.filter(o => o.id !== id);
    saveData();
    res.json({ success: true });
});

// Products API
app.get('/api/products', (req, res) => {
    res.json({ success: true, products: data.products });
});

app.put('/api/products', (req, res) => {
    const updates = req.body;
    const product = data.products.find(p => p.id === updates.id);
    if (product) {
        Object.assign(product, updates);
        saveData();
    }
    res.json({ success: true, product });
});

// Reviews API
app.get('/api/reviews', (req, res) => {
    res.json({ success: true, reviews: data.reviews });
});

// Health check
app.get('/api/health', (req, res) => {
    res.json({ status: 'ok', timestamp: new Date().toISOString() });
});

app.listen(PORT, () => {
    console.log(`Server running on port ${PORT}`);
});