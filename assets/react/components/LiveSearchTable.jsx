import React, { useState, useEffect } from 'react';

export default function LiveSearchTable({ 
    apiUrl,
    TableComponent,
    emptyMessage = 'Ничего не найдено',
    placeholder = 'Поиск...',
    showCreateButton = false,
    createButtonUrl = '',
    createButtonText = 'Создать'
}) {
    const [query, setQuery] = useState('');
    const [data, setData] = useState([]);
    const [loading, setLoading] = useState(true);

    const loadData = (searchQuery = '') => {
        const url = searchQuery ? `${apiUrl}?q=${encodeURIComponent(searchQuery)}` : apiUrl;
        setLoading(true);
        
        fetch(url)
            .then(res => res.json())
            .then(result => {
                setData(result);
                setLoading(false);
            })
            .catch(err => {
                console.error('Search error:', err);
                setLoading(false);
            });
    };

    useEffect(() => {
        loadData();
    }, [apiUrl]);

    useEffect(() => {
        const timer = setTimeout(() => {
            if (query) loadData(query);
            else loadData();
        }, 300);
        return () => clearTimeout(timer);
    }, [query]);

    const handleClear = () => {
        setQuery('');
        loadData('');
    };

    return (
        <div>
            <div className="mb-4 d-flex flex-column flex-md-row justify-content-between gap-3">
                <div className="flex-grow-1 me-md-3">
                    <div className="row g-3">
                        <div className="col-12 col-md-8 d-flex gap-2">
                            <div className="input-group flex-grow-1">
                                <input
                                    type="text"
                                    className="form-control border-end-0"
                                    placeholder={placeholder}
                                    value={query}
                                    onChange={(e) => setQuery(e.target.value)}
                                />
                                <button className="btn btn-secondary" type="button" onClick={() => loadData(query)}>
                                    <i className="bi bi-search"></i>
                                </button>
                            </div>
                            {query && (
                                <button
                                    type="button"
                                    className="btn btn-outline-secondary"
                                    onClick={handleClear}
                                >
                                    <i className="bi bi-x-lg"></i>
                                </button>
                            )}
                        </div>
                    </div>
                </div>
                
                {showCreateButton && createButtonUrl && (
                    <div className="flex-shrink-0">
                        <a href={createButtonUrl} className="btn btn-primary">
                            {createButtonText}
                        </a>
                    </div>
                )}
            </div>

            {loading && (
                <div className="text-center py-3">
                    <div className="spinner-border text-primary" role="status">
                        <span className="visually-hidden">Загрузка...</span>
                    </div>
                </div>
            )}

            {!loading && <TableComponent items={data} emptyMessage={emptyMessage} />}
        </div>
    );
}