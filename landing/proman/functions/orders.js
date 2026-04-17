const ACCOUNT_ID = 'b3287bd86214fb07b212a90d22ab79fb';
const DATABASE_ID = 'ad67f907-3e85-45f1-a924-80db1e2117cd';
const API_TOKEN = process.env.CLOUDFLARE_API_TOKEN;

const corsHeaders = {
    'Access-Control-Allow-Origin': '*',
    'Access-Control-Allow-Methods': 'GET, POST, PUT, DELETE, OPTIONS',
    'Access-Control-Allow-Headers': 'Content-Type',
};

async function queryD1(sql, params = []) {
    const response = await fetch(`https://api.cloudflare.com/client/v4/accounts/${ACCOUNT_ID}/d1/database/${DATABASE_ID}/query`, {
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
        throw new Error(data.errors?.[0]?.message || 'Query failed');
    }
    return data.result[0]?.results || [];
}

exports.handler = async (event, context) => {
    if (event.httpMethod === 'OPTIONS') {
        return { statusCode: 200, headers: corsHeaders, body: '' };
    }

    const path = event.path.replace('/.netlify/functions/orders', '').replace('/api/orders', '');

    try {
        if (event.httpMethod === 'GET') {
            const orders = await queryD1('SELECT * FROM orders ORDER BY created_at DESC LIMIT 1000');
            return {
                statusCode: 200,
                headers: { ...corsHeaders, 'Content-Type': 'application/json' },
                body: JSON.stringify({ success: true, orders })
            };
        }

        if (event.httpMethod === 'POST') {
            const body = JSON.parse(event.body);
            const { name, phone, country = 'IN', product = 'Libidex', clickid = '', utm_campaign = '', utm_source = '' } = body;

            if (!name || !phone) {
                return {
                    statusCode: 400,
                    headers: { ...corsHeaders, 'Content-Type': 'application/json' },
                    body: JSON.stringify({ error: 'Name and phone required' })
                };
            }

            const id = 'ord_' + Date.now().toString(36) + Math.random().toString(36).substr(2, 9);
            
            await queryD1(
                `INSERT INTO orders (id, name, phone, country, product, clickid, utm_campaign, utm_source, status)
                 VALUES (?, ?, ?, ?, ?, ?, ?, ?, 'pending')`,
                [id, name, phone, country, product, clickid, utm_campaign, utm_source]
            );

            const orders = await queryD1('SELECT * FROM orders WHERE id = ?', [id]);

            return {
                statusCode: 200,
                headers: { ...corsHeaders, 'Content-Type': 'application/json' },
                body: JSON.stringify({ success: true, order: orders[0] })
            };
        }

        if (event.httpMethod === 'PUT') {
            const body = JSON.parse(event.body);
            const { id, status } = body;

            if (!id || !status) {
                return {
                    statusCode: 400,
                    headers: { ...corsHeaders, 'Content-Type': 'application/json' },
                    body: JSON.stringify({ error: 'ID and status required' })
                };
            }

            await queryD1('UPDATE orders SET status = ? WHERE id = ?', [status, id]);

            return {
                statusCode: 200,
                headers: { ...corsHeaders, 'Content-Type': 'application/json' },
                body: JSON.stringify({ success: true })
            };
        }

        if (event.httpMethod === 'DELETE') {
            const body = JSON.parse(event.body);
            const { id } = body;

            if (!id) {
                return {
                    statusCode: 400,
                    headers: { ...corsHeaders, 'Content-Type': 'application/json' },
                    body: JSON.stringify({ error: 'ID required' })
                };
            }

            await queryD1('DELETE FROM orders WHERE id = ?', [id]);

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
