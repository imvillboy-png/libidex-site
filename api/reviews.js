const reviews = [
    { id: 1, name: 'राजेश, 42', age: '42', review_text: '3 महीने से ज्यादा समय से ले रहा हूं। परिणाम शानदार हैं!', image: 'live-1.jpg', status: 'active' },
    { id: 2, name: 'अमित, 38', age: '38', review_text: '6 हफ्ते में बहुत सुधार हुआ। मेरी पत्नी भी खुश है।', image: 'live-1.jpg', status: 'active' },
    { id: 3, name: 'संजय, 45', age: '45', review_text: 'बेहतर ऊर्जा और आत्मविश्वास। Highly recommended!', image: 'live-1.jpg', status: 'active' }
];

export default function handler(req, res) {
    res.setHeader('Access-Control-Allow-Origin', '*');
    res.setHeader('Access-Control-Allow-Methods', 'GET, OPTIONS');
    res.setHeader('Access-Control-Allow-Headers', 'Content-Type');

    if (req.method === 'OPTIONS') {
        return res.status(200).end();
    }

    if (req.method === 'GET') {
        const activeReviews = reviews.filter(r => r.status === 'active');
        return res.status(200).json({ success: true, reviews: activeReviews });
    }

    return res.status(405).json({ error: 'Method not allowed' });
}