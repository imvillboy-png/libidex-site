const { getStore } = require('@netlify/blobs');

const store = getStore('products-data');

const corsHeaders = {
    'Access-Control-Allow-Origin': '*',
    'Access-Control-Allow-Methods': 'GET, PUT, OPTIONS',
    'Access-Control-Allow-Headers': 'Content-Type',
};

const defaultProducts = [
    { id: 1, name: 'Libidex', name_hindi: 'पुरुषों की शक्ति बढ़ाने का कैप्सूल', price: 2490, old_price: 4980, image: 'product-1.png', status: 'active' },
    { id: 2, name: 'ProMan', name_hindi: 'पुरुषों के लिए एनर्जी कैप्सूल', price: 1990, old_price: 3990, image: 'product-1.png', status: 'active' }
];

exports.handler = async (event, context) => {
    if (event.httpMethod === 'OPTIONS') {
        return { statusCode: 200, headers: corsHeaders, body: '' };
    }

    try {
        if (event.httpMethod === 'GET') {
            const productsJson = await store.get('products', { type: 'json' });
            const products = productsJson || defaultProducts;
            const activeProducts = products.filter(p => p.status === 'active');
            return {
                statusCode: 200,
                headers: { ...corsHeaders, 'Content-Type': 'application/json' },
                body: JSON.stringify({ success: true, products: activeProducts })
            };
        }

        if (event.httpMethod === 'PUT') {
            let body;
            try {
                body = JSON.parse(event.body || '{}');
            } catch (e) {
                body = {};
            }

            const { id, name, name_hindi, tagline, description, price, old_price, image, status } = body;

            if (!id) {
                return {
                    statusCode: 400,
                    headers: { ...corsHeaders, 'Content-Type': 'application/json' },
                    body: JSON.stringify({ error: 'ID required' })
                };
            }

            const productsJson = await store.get('products', { type: 'json' });
            let products = productsJson || defaultProducts;
            
            const productIndex = products.findIndex(p => p.id === id);
            if (productIndex !== -1) {
                if (name !== undefined) products[productIndex].name = name;
                if (name_hindi !== undefined) products[productIndex].name_hindi = name_hindi;
                if (tagline !== undefined) products[productIndex].tagline = tagline;
                if (description !== undefined) products[productIndex].description = description;
                if (price !== undefined) products[productIndex].price = price;
                if (old_price !== undefined) products[productIndex].old_price = old_price;
                if (image !== undefined) products[productIndex].image = image;
                if (status !== undefined) products[productIndex].status = status;
                await store.set('products', JSON.stringify(products));
            }

            return {
                statusCode: 200,
                headers: { ...corsHeaders, 'Content-Type': 'application/json' },
                body: JSON.stringify({ success: true, product: products[productIndex] })
            };
        }

        return {
            statusCode: 405,
            headers: { ...corsHeaders, 'Content-Type': 'application/json' },
            body: JSON.stringify({ error: 'Method not allowed' })
        };
    } catch (error) {
        return {
            statusCode: 500,
            headers: { ...corsHeaders, 'Content-Type': 'application/json' },
            body: JSON.stringify({ error: error.message, stack: error.stack })
        };
    }
};