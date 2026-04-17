export async function onRequest(context) {
    const { request, env } = context;
    const url = new URL(request.url);
    const path = url.pathname;

    const corsHeaders = {
        'Access-Control-Allow-Origin': '*',
        'Access-Control-Allow-Methods': 'GET, POST, PUT, DELETE, OPTIONS',
        'Access-Control-Allow-Headers': 'Content-Type',
    };

    if (request.method === 'OPTIONS') {
        return new Response(null, { headers: corsHeaders });
    }

    try {
        const db = env.DB;

        if (request.method === 'GET') {
            const { results } = await db.prepare('SELECT * FROM orders ORDER BY created_at DESC LIMIT 1000').all();
            return new Response(JSON.stringify({ success: true, orders: results }), {
                headers: { ...corsHeaders, 'Content-Type': 'application/json' }
            });
        }

        if (request.method === 'POST') {
            const body = await request.json();
            const { name, phone, country = 'IN', product = 'Libidex', clickid = '', utm_campaign = '', utm_source = '', utm_medium = '', utm_content = '' } = body;

            if (!name || !phone) {
                return new Response(JSON.stringify({ error: 'Name and phone required' }), {
                    status: 400,
                    headers: { ...corsHeaders, 'Content-Type': 'application/json' }
                });
            }

            const id = 'ord_' + Date.now().toString(36) + Math.random().toString(36).substr(2, 9);
            
            await db.prepare(`
                INSERT INTO orders (id, name, phone, country, product, clickid, utm_campaign, utm_source, utm_medium, utm_content)
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
            `).bind(id, name, phone, country, product, clickid, utm_campaign, utm_source, utm_medium, utm_content).run();

            const { results } = await db.prepare('SELECT * FROM orders WHERE id = ?').bind(id).all();

            return new Response(JSON.stringify({ success: true, order: results[0] }), {
                headers: { ...corsHeaders, 'Content-Type': 'application/json' }
            });
        }

        if (request.method === 'PUT') {
            const body = await request.json();
            const { id, status } = body;

            if (!id || !status) {
                return new Response(JSON.stringify({ error: 'ID and status required' }), {
                    status: 400,
                    headers: { ...corsHeaders, 'Content-Type': 'application/json' }
                });
            }

            await db.prepare('UPDATE orders SET status = ? WHERE id = ?').bind(status, id).run();

            return new Response(JSON.stringify({ success: true }), {
                headers: { ...corsHeaders, 'Content-Type': 'application/json' }
            });
        }

        if (request.method === 'DELETE') {
            const body = await request.json();
            const { id } = body;

            if (!id) {
                return new Response(JSON.stringify({ error: 'ID required' }), {
                    status: 400,
                    headers: { ...corsHeaders, 'Content-Type': 'application/json' }
                });
            }

            await db.prepare('DELETE FROM orders WHERE id = ?').bind(id).run();

            return new Response(JSON.stringify({ success: true }), {
                headers: { ...corsHeaders, 'Content-Type': 'application/json' }
            });
        }

        return new Response(JSON.stringify({ error: 'Method not allowed' }), {
            status: 405,
            headers: { ...corsHeaders, 'Content-Type': 'application/json' }
        });
    } catch (error) {
        return new Response(JSON.stringify({ error: error.message }), {
            status: 500,
            headers: { ...corsHeaders, 'Content-Type': 'application/json' }
        });
    }
}
