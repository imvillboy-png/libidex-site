let orders = [];

export default function handler(req, res) {
    res.setHeader('Access-Control-Allow-Origin', '*');
    res.setHeader('Access-Control-Allow-Methods', 'GET, POST, PUT, DELETE, OPTIONS');
    res.setHeader('Access-Control-Allow-Headers', 'Content-Type');

    if (req.method === 'OPTIONS') {
        return res.status(200).end();
    }

    if (req.method === 'GET') {
        return res.status(200).json({ success: true, orders: orders.slice(0, 1000) });
    }

    if (req.method === 'POST') {
        const { name, phone, country = 'IN', product = 'Libidex', clickid = '', utm_campaign = '', utm_source = '' } = req.body;

        if (!name || !phone) {
            return res.status(400).json({ error: 'Name and phone required' });
        }

        const order = {
            id: 'ord_' + Date.now().toString(36) + Math.random().toString(36).substr(2, 9),
            name, phone, country, product, clickid, utm_campaign, utm_source,
            status: 'pending',
            created_at: new Date().toISOString()
        };

        orders.unshift(order);
        return res.status(200).json({ success: true, order });
    }

    if (req.method === 'PUT') {
        const { id, status } = req.body;
        const orderIndex = orders.findIndex(o => o.id === id);
        if (orderIndex !== -1) {
            orders[orderIndex].status = status;
        }
        return res.status(200).json({ success: true });
    }

    if (req.method === 'DELETE') {
        const { id } = req.body;
        orders = orders.filter(o => o.id !== id);
        return res.status(200).json({ success: true });
    }

    return res.status(405).json({ error: 'Method not allowed' });
}