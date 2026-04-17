export async function onRequest(context) {
    const { request, env } = context;

    const corsHeaders = {
        'Access-Control-Allow-Origin': '*',
        'Access-Control-Allow-Methods': 'GET, PUT, OPTIONS',
        'Access-Control-Allow-Headers': 'Content-Type',
    };

    if (request.method === 'OPTIONS') {
        return new Response(null, { headers: corsHeaders });
    }

    try {
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
    } catch (error) {
        return new Response(JSON.stringify({ error: error.message }), {
            status: 500,
            headers: { ...corsHeaders, 'Content-Type': 'application/json' }
        });
    }
}
