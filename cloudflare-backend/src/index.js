export default {
    async fetch(request, env) {
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
            if (path === '/api/orders' || path === '/api/orders/') {
                return await handleOrders(request, env, corsHeaders);
            }
            if (path === '/api/products' || path === '/api/products/') {
                return await handleProducts(request, env, corsHeaders);
            }
            if (path === '/api/reviews' || path === '/api/reviews/') {
                return await handleReviews(request, env, corsHeaders);
            }
            if (path === '/api/health' || path === '/api/health/') {
                return new Response(JSON.stringify({ status: 'ok', timestamp: new Date().toISOString() }), {
                    headers: { ...corsHeaders, 'Content-Type': 'application/json' }
                });
            }

            return new Response(JSON.stringify({ error: 'Not found' }), {
                status: 404,
                headers: { ...corsHeaders, 'Content-Type': 'application/json' }
            });
        } catch (error) {
            return new Response(JSON.stringify({ error: error.message }), {
                status: 500,
                headers: { ...corsHeaders, 'Content-Type': 'application/json' }
            });
        }
    }
};

async function handleOrders(request, env, corsHeaders) {
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

        const id = generateId();
        const stmt = db.prepare(`
            INSERT INTO orders (id, name, phone, country, product, clickid, utm_campaign, utm_source, utm_medium, utm_content)
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
        `);

        await stmt.bind(id, name, phone, country, product, clickid, utm_campaign, utm_source, utm_medium, utm_content).run();

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
}

async function handleProducts(request, env, corsHeaders) {
    const db = env.DB;

    if (request.method === 'GET') {
        const { results } = await db.prepare('SELECT * FROM products WHERE status = ?').bind('active').all();
        return new Response(JSON.stringify({ success: true, products: results }), {
            headers: { ...corsHeaders, 'Content-Type': 'application/json' }
        });
    }

    if (request.method === 'PUT') {
        const body = await request.json();
        const { id, name, name_hindi, tagline, description, price, old_price, image, status } = body;

        if (!id) {
            return new Response(JSON.stringify({ error: 'ID required' }), {
                status: 400,
                headers: { ...corsHeaders, 'Content-Type': 'application/json' }
            });
        }

        const updates = [];
        const values = [];

        if (name !== undefined) { updates.push('name = ?'); values.push(name); }
        if (name_hindi !== undefined) { updates.push('name_hindi = ?'); values.push(name_hindi); }
        if (tagline !== undefined) { updates.push('tagline = ?'); values.push(tagline); }
        if (description !== undefined) { updates.push('description = ?'); values.push(description); }
        if (price !== undefined) { updates.push('price = ?'); values.push(price); }
        if (old_price !== undefined) { updates.push('old_price = ?'); values.push(old_price); }
        if (image !== undefined) { updates.push('image = ?'); values.push(image); }
        if (status !== undefined) { updates.push('status = ?'); values.push(status); }

        if (updates.length > 0) {
            updates.push("updated_at = datetime('now', '+5 hours 30 minutes')");
            values.push(id);
            await db.prepare(`UPDATE products SET ${updates.join(', ')} WHERE id = ?`).bind(...values).run();
        }

        const { results } = await db.prepare('SELECT * FROM products WHERE id = ?').bind(id).all();

        return new Response(JSON.stringify({ success: true, product: results[0] }), {
            headers: { ...corsHeaders, 'Content-Type': 'application/json' }
        });
    }

    return new Response(JSON.stringify({ error: 'Method not allowed' }), {
        status: 405,
        headers: { ...corsHeaders, 'Content-Type': 'application/json' }
    });
}

async function handleReviews(request, env, corsHeaders) {
    const db = env.DB;

    if (request.method === 'GET') {
        const { results } = await db.prepare('SELECT * FROM reviews WHERE status = ? ORDER BY id ASC').bind('active').all();
        return new Response(JSON.stringify({ success: true, reviews: results }), {
            headers: { ...corsHeaders, 'Content-Type': 'application/json' }
        });
    }

    return new Response(JSON.stringify({ error: 'Method not allowed' }), {
        status: 405,
        headers: { ...corsHeaders, 'Content-Type': 'application/json' }
    });
}

function generateId() {
    return 'ord_' + Date.now().toString(36) + Math.random().toString(36).substr(2, 9);
}
