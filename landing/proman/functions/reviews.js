const ACCOUNT_ID = 'b3287bd86214fb07b212a90d22ab79fb';
const DATABASE_ID = 'ad67f907-3e85-45f1-a924-80db1e2117cd';

const corsHeaders = {
    'Access-Control-Allow-Origin': '*',
    'Access-Control-Allow-Methods': 'GET, OPTIONS',
    'Access-Control-Allow-Headers': 'Content-Type',
};

async function queryD1(sql, params = []) {
    const response = await fetch(`https://api.cloudflare.com/client/v4/accounts/${ACCOUNT_ID}/d1/database/${DATABASE_ID}/query`, {
        method: 'POST',
        headers: {
            'Authorization': `Bearer ${process.env.CLOUDFLARE_API_TOKEN}`,
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
            const reviews = await queryD1("SELECT * FROM reviews WHERE status = 'active' ORDER BY id ASC");
            return {
                statusCode: 200,
                headers: { ...corsHeaders, 'Content-Type': 'application/json' },
                body: JSON.stringify({ success: true, reviews })
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
