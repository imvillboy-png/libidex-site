const corsHeaders = {
    'Access-Control-Allow-Origin': '*',
    'Access-Control-Allow-Methods': 'GET, POST, PUT, DELETE, OPTIONS',
    'Access-Control-Allow-Headers': 'Content-Type',
};

let orders = [];

exports.handler = async (event, context) => {
    if (event.httpMethod === 'OPTIONS') {
        return { statusCode: 200, headers: corsHeaders, body: '' };
    }

    try {
        if (event.httpMethod === 'GET') {
            return {
                statusCode: 200,
                headers: { ...corsHeaders, 'Content-Type': 'application/json' },
                body: JSON.stringify({ success: true, orders: orders.slice(0, 1000) })
            };
        }

        if (event.httpMethod === 'POST') {
            let body;
            try {
                body = JSON.parse(event.body || '{}');
            } catch (e) {
                body = {};
            }
            
            const name = body.name || '';
            const phone = body.phone || '';
            const country = body.country || 'IN';
            const product = body.product || 'Libidex';
            const clickid = body.clickid || '';
            const utm_campaign = body.utm_campaign || '';
            const utm_source = body.utm_source || '';

            if (!name || !phone) {
                return {
                    statusCode: 400,
                    headers: { ...corsHeaders, 'Content-Type': 'application/json' },
                    body: JSON.stringify({ error: 'Name and phone required' })
                };
            }

            const id = 'ord_' + Date.now().toString(36) + Math.random().toString(36).substr(2, 9);
            const order = { id, name, phone, country, product, clickid, utm_campaign, utm_source, status: 'pending', created_at: new Date().toISOString() };
            
            orders.unshift(order);

            return {
                statusCode: 200,
                headers: { ...corsHeaders, 'Content-Type': 'application/json' },
                body: JSON.stringify({ success: true, order })
            };
        }

        if (event.httpMethod === 'PUT') {
            let body;
            try {
                body = JSON.parse(event.body || '{}');
            } catch (e) {
                body = {};
            }
            
            const { id, status } = body;

            if (!id || !status) {
                return {
                    statusCode: 400,
                    headers: { ...corsHeaders, 'Content-Type': 'application/json' },
                    body: JSON.stringify({ error: 'ID and status required' })
                };
            }

            const orderIndex = orders.findIndex(o => o.id === id);
            if (orderIndex !== -1) {
                orders[orderIndex].status = status;
            }

            return {
                statusCode: 200,
                headers: { ...corsHeaders, 'Content-Type': 'application/json' },
                body: JSON.stringify({ success: true })
            };
        }

        if (event.httpMethod === 'DELETE') {
            let body;
            try {
                body = JSON.parse(event.body || '{}');
            } catch (e) {
                body = {};
            }
            
            const { id } = body;

            if (!id) {
                return {
                    statusCode: 400,
                    headers: { ...corsHeaders, 'Content-Type': 'application/json' },
                    body: JSON.stringify({ error: 'ID required' })
                };
            }

            orders = orders.filter(o => o.id !== id);

            return {
                statusCode: 200,
                headers: { ...corsHeaders, 'Content-Type': 'application/json' },
                body: JSON.stringify({ success: true })
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
            body: JSON.stringify({ error: error.message })
        };
    }
};