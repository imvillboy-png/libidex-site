const { getStore } = require('@netlify/blobs');

const store = getStore('reviews-data');

const corsHeaders = {
    'Access-Control-Allow-Origin': '*',
    'Access-Control-Allow-Methods': 'GET, OPTIONS',
    'Access-Control-Allow-Headers': 'Content-Type',
};

const defaultReviews = [
    { id: 1, name: 'राजेश, 42', age: '42', review_text: '3 महीने से ज्यादा समय से ले रहा हूं। परिणाम शानदार हैं!', image: 'live-1.jpg', status: 'active' },
    { id: 2, name: 'अमित, 38', age: '38', review_text: '6 हफ्ते में बहुत सुधार हुआ। मेरी पत्नी भी खुश है।', image: 'live-1.jpg', status: 'active' },
    { id: 3, name: 'संजय, 45', age: '45', review_text: 'बेहतर ऊर्जा और आत्मविश्वास। Highly recommended!', image: 'live-1.jpg', status: 'active' }
];

exports.handler = async (event, context) => {
    if (event.httpMethod === 'OPTIONS') {
        return { statusCode: 200, headers: corsHeaders, body: '' };
    }

    try {
        if (event.httpMethod === 'GET') {
            const reviewsJson = await store.get('reviews', { type: 'json' });
            const reviews = reviewsJson || defaultReviews;
            const activeReviews = reviews.filter(r => r.status === 'active');
            return {
                statusCode: 200,
                headers: { ...corsHeaders, 'Content-Type': 'application/json' },
                body: JSON.stringify({ success: true, reviews: activeReviews })
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