const ACCOUNT_ID = 'b3287bd86214fb07b212a90d22ab79fb';
const DATABASE_ID = 'ad67f907-3e85-45f1-a924-80db1e2117cd';
const API_TOKEN = process.env.CLOUDFLARE_API_TOKEN;

const corsHeaders = {
    'Access-Control-Allow-Origin': '*',
    'Access-Control-Allow-Methods': 'GET, PUT, OPTIONS',
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

    try {
        if (event.httpMethod === 'GET') {
            const products = await queryD1("SELECT * FROM products WHERE status = 'active'");
            return {
                statusCode: 200,
                headers: { ...corsHeaders, 'Content-Type': 'application/json' },
                body: JSON.stringify({ success: true, products })
            };
        }

        if (event.httpMethod === 'PUT') {
            const body = JSON.parse(event.body);
            const { id, name, name_hindi, tagline, description, price, old_price, image, status } = body;

            if (!id) {
                return {
                    statusCode: 400,
                    headers: { ...corsHeaders, 'Content-Type': 'application/json' },
                    body: JSON.stringify({ error: 'ID required' })
                };
            }

            const fields = [];
            const values = [];
            
            if (name !== undefined) { fields.push('name = ?'); values.push(name); }
            if (name_hindi !== undefined) { fields.push('name_hindi = ?'); values.push(name_hindi); }
            if (tagline !== undefined) { fields.push('tagline = ?'); values.push(tagline); }
            if (description !== undefined) { fields.push('description = ?'); values.push(description); }
            if (price !== undefined) { fields.push('price = ?'); values.push(price); }
            if (old_price !== undefined) { fields.push('old_price = ?'); values.push(old_price); }
            if (image !== undefined) { fields.push('image = ?'); values.push(image); }
            if (status !== undefined) { fields.push('status = ?'); values.push(status); }

            if (fields.length > 0) {
                values.push(id);
                await queryD1(`UPDATE products SET ${fields.join(', ')} WHERE id = ?`, values);
            }

            const products = await queryD1('SELECT * FROM products WHERE id = ?', [id]);

            return {
                statusCode: 200,
                headers: { ...corsHeaders, 'Content-Type': 'application/json' },
                body: JSON.stringify({ success: true, product: products[0] })
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
