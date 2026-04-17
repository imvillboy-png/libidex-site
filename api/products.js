const products = [
    { id: 1, name: 'Libidex', name_hindi: 'पुरुषों की शक्ति बढ़ाने का कैप्सूल', price: 2490, old_price: 4980, image: 'product-1.png', status: 'active' },
    { id: 2, name: 'ProMan', name_hindi: 'पुरुषों के लिए एनर्जी कैप्सूल', price: 1990, old_price: 3990, image: 'product-1.png', status: 'active' }
];

export default function handler(req, res) {
    res.setHeader('Access-Control-Allow-Origin', '*');
    res.setHeader('Access-Control-Allow-Methods', 'GET, PUT, OPTIONS');
    res.setHeader('Access-Control-Allow-Headers', 'Content-Type');

    if (req.method === 'OPTIONS') {
        return res.status(200).end();
    }

    if (req.method === 'GET') {
        const activeProducts = products.filter(p => p.status === 'active');
        return res.status(200).json({ success: true, products: activeProducts });
    }

    if (req.method === 'PUT') {
        const { id, ...updates } = req.body;
        const productIndex = products.findIndex(p => p.id === id);
        if (productIndex !== -1) {
            Object.assign(products[productIndex], updates);
        }
        return res.status(200).json({ success: true, product: products[productIndex] });
    }

    return res.status(405).json({ error: 'Method not allowed' });
}