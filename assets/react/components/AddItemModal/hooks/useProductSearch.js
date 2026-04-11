import { useState, useEffect, useCallback } from 'react';

export function useProductSearch() {
    const [search, setSearch] = useState('');
    const [products, setProducts] = useState([]);
    const [loading, setLoading] = useState(false);
    const [showDropdown, setShowDropdown] = useState(false);

    const loadProducts = useCallback(async (query = '') => {
        setLoading(true);
        const url = query && query.length >= 2 
            ? `/api/product/search?q=${encodeURIComponent(query)}&limit=50`
            : '/api/product/search?limit=50';
        
        try {
            const res = await fetch(url);
            const data = await res.json();
            setProducts(data);
            setShowDropdown(true);
        } catch (err) {
            console.error(err);
        } finally {
            setLoading(false);
        }
    }, []);

    useEffect(() => {
        const timer = setTimeout(() => {
            loadProducts(search);
        }, 300);
        return () => clearTimeout(timer);
    }, [search, loadProducts]);

    const reset = useCallback(() => {
        setSearch('');
        setProducts([]);
        setShowDropdown(false);
        setLoading(false);
    }, []);

    return { search, setSearch, products, loading, showDropdown, setShowDropdown, reset };
}