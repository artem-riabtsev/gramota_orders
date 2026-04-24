import { useState, useEffect, useCallback } from 'react';

export function usePriceSearch() {
    const [searchQuery, setSearchQuery] = useState('');
    const [prices, setPrices] = useState([]);
    const [loading, setLoading] = useState(false);

    const loadPrices = useCallback((query = '') => {
        setLoading(true);
        const url = query 
            ? `/api/price/list?q=${encodeURIComponent(query)}&limit=50`
            : '/api/price/list?limit=50';
        
        fetch(url)
            .then(res => res.json())
            .then(data => {
                setPrices(data.data || []);
                setLoading(false);
            })
            .catch(() => setLoading(false));
    }, []);

    useEffect(() => {
        loadPrices('');
    }, [loadPrices]);

    useEffect(() => {
        if (searchQuery.length >= 2) {
            loadPrices(searchQuery);
        } else if (searchQuery.length === 0) {
            loadPrices('');
        }
    }, [searchQuery, loadPrices]);

    const reset = useCallback(() => {
        setSearchQuery('');
        loadPrices('');
    }, [loadPrices]);

    return { searchQuery, setSearchQuery, prices, loading, reset };
}