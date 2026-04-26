import React, { useState, useEffect, useRef, useCallback } from 'react';

export default function LiveSearchTable({ 
    apiUrl,
    TableComponent,
    emptyMessage = 'Ничего не найдено',
    placeholder = 'Поиск...',
    showCreateButton = false,
    createButtonUrl = '',
    createButtonText = 'Создать',
    showExportButton = false,
    getExportHeaders = null,
    getExportRows = null,
    itemsPerPage = 10,
    filters = null,
    onFiltersChange = null
}) {
    const [query, setQuery] = useState('');
    const [items, setItems] = useState([]);
    const [page, setPage] = useState(1);
    const [hasMore, setHasMore] = useState(true);
    const [loading, setLoading] = useState(false);
    const [totalItems, setTotalItems] = useState(0);
    const [isLoadingMore, setIsLoadingMore] = useState(false);
    
    const [filterValues, setFilterValues] = useState(filters || {});
    
    const observerRef = useRef(null);
    const lastItemRef = useRef(null);
    const isFirstLoad = useRef(true);
    const activeRequestRef = useRef(null);
    const currentQueryRef = useRef('');
    const currentPageRef = useRef(1);
    const currentFiltersRef = useRef({});

    const buildUrl = useCallback((searchQuery = '', currentPage = 1, reset = true) => {
        const params = new URLSearchParams();
        params.set('page', reset ? 1 : currentPage);
        params.set('limit', itemsPerPage);
        
        if (searchQuery) {
            params.set('q', searchQuery);
        }
        
        Object.keys(filterValues).forEach(key => {
            const value = filterValues[key];
            if (value !== null && value !== '' && value !== undefined) {
                params.set(key, value);
            }
        });
        
        return `${apiUrl}?${params.toString()}`;
    }, [apiUrl, itemsPerPage, filterValues]);

    const loadData = useCallback(async (searchQuery = '', reset = true, isLoadMore = false) => {
        if (activeRequestRef.current) {
            activeRequestRef.current.abort();
        }
        
        if (loading && !isLoadMore) return;
        if (isLoadingMore) return;
        
        const currentPage = reset ? 1 : page;
        
        if (isLoadMore) {
            setIsLoadingMore(true);
        } else {
            setLoading(true);
        }
        
        try {
            const controller = new AbortController();
            activeRequestRef.current = controller;
            
            const url = buildUrl(searchQuery, currentPage, reset);
            const response = await fetch(url, { signal: controller.signal });
            const result = await response.json();
            
            if (reset) {
                setItems(result.data);
                setPage(2);
                currentPageRef.current = 2;
            } else {
                setItems(prev => [...prev, ...result.data]);
                setPage(prev => prev + 1);
                currentPageRef.current = page + 1;
            }
            
            setHasMore(result.data.length === itemsPerPage);
            setTotalItems(result.total || 0);
            currentQueryRef.current = searchQuery;
            currentFiltersRef.current = filterValues;
        } catch (err) {
            if (err.name !== 'AbortError') {
                console.error('Search error:', err);
            }
        } finally {
            setLoading(false);
            setIsLoadingMore(false);
            if (activeRequestRef.current) {
                activeRequestRef.current = null;
            }
        }
    }, [apiUrl, page, itemsPerPage, loading, isLoadingMore, filterValues, buildUrl]);

    useEffect(() => {
        if (!isFirstLoad.current) {
            loadData(query, true);
        }
    }, [filterValues]);

    useEffect(() => {
        if (isFirstLoad.current) {
            isFirstLoad.current = false;
            loadData('', true);
        }
    }, []);

    useEffect(() => {
        if (isFirstLoad.current) return;
        
        const timer = setTimeout(() => {
            if (currentQueryRef.current !== query) {
                loadData(query, true);
            }
        }, 300);
        
        return () => clearTimeout(timer);
    }, [query, loadData]);

    useEffect(() => {
        if (!lastItemRef.current || isLoadingMore || !hasMore || loading) return;
        
        if (observerRef.current) {
            observerRef.current.disconnect();
        }
        
        const observer = new IntersectionObserver(
            (entries) => {
                const firstEntry = entries[0];
                if (firstEntry.isIntersecting && hasMore && !isLoadingMore && !loading) {
                    loadData(query, false, true);
                }
            },
            { threshold: 0.1, rootMargin: '100px' }
        );
        
        observer.observe(lastItemRef.current);
        observerRef.current = observer;
        
        return () => {
            if (observerRef.current) {
                observerRef.current.disconnect();
            }
        };
    }, [lastItemRef.current, isLoadingMore, hasMore, loading, query, loadData]);

    const handleClear = () => {
        setQuery('');
        loadData('', true);
    };
    
    const handleFilterChange = (key, value) => {
        const newFilters = { ...filterValues, [key]: value };
        setFilterValues(newFilters);
        if (onFiltersChange) {
            onFiltersChange(newFilters);
        }
    };
    
    const handleResetFilters = () => {
        setFilterValues({});
        if (onFiltersChange) {
            onFiltersChange({});
        }
    };

    const renderFilters = () => {
        if (!filters) return null;
        
        return (
            <div className="mb-4 p-3 bg-light rounded-3">
                <div className="row g-3 align-items-end">
                    {Object.keys(filters).includes('dateFrom') && (
                        <>
                            <div className="col-md-3">
                                <label className="form-label small fw-semibold">Дата от</label>
                                <input
                                    type="date"
                                    className="form-control"
                                    value={filterValues.dateFrom || ''}
                                    onChange={(e) => handleFilterChange('dateFrom', e.target.value)}
                                />
                            </div>
                            <div className="col-md-3">
                                <label className="form-label small fw-semibold">Дата до</label>
                                <input
                                    type="date"
                                    className="form-control"
                                    value={filterValues.dateTo || ''}
                                    onChange={(e) => handleFilterChange('dateTo', e.target.value)}
                                />
                            </div>
                        </>
                    )}
                    
                    {Object.keys(filters).includes('status') && (
                        <div className="col-md-2">
                            <label className="form-label small fw-semibold">Статус заказа</label>
                            <select
                                className="form-select"
                                value={filterValues.status ?? ''}
                                onChange={(e) => handleFilterChange('status', e.target.value)}
                            >
                                <option value="">Все</option>
                                <option value="1">Не оплачен</option>
                                <option value="2">Частично оплачен</option>
                                <option value="3">Переплата</option>
                                <option value="4">Оплачен</option>
                            </select>
                        </div>
                    )}
                    
                    {Object.keys(filters).includes('basic') && (
                        <div className="col-md-2">
                            <label className="form-label small fw-semibold">Продукты</label>
                            <select
                                className="form-select"
                                value={filterValues.basic ?? ''}
                                onChange={(e) => handleFilterChange('basic', e.target.value)}
                            >
                                <option value="">Все</option>
                                <option value="1">Только базовые</option>
                            </select>
                        </div>
                    )}
                    
                    <div className="col-md-2">
                        <button
                            type="button"
                            className="btn btn-outline-secondary w-100"
                            onClick={handleResetFilters}
                        >
                            <i className="bi bi-eraser me-1"></i> Сбросить
                        </button>
                    </div>
                </div>
            </div>
        );
    };

    return (
        <div>
            {renderFilters()}

            <div className="mb-4">
                <div className="d-flex flex-column flex-md-row justify-content-between gap-3">
                    <div className="flex-grow-1" style={{ maxWidth: '500px' }}>
                        <div className="card shadow-sm border-0 p-1" style={{ borderRadius: '60px', background: 'white' }}>
                            <div className="position-relative">
                                <i className="bi bi-search position-absolute top-50 start-0 translate-middle-y ms-3 text-muted" style={{ fontSize: '1rem', zIndex: 5 }}></i>
                                <input
                                    type="text"
                                    className="form-control border-0 ps-5"
                                    style={{ 
                                        borderRadius: '60px',
                                        backgroundColor: 'transparent',
                                        boxShadow: 'none',
                                        fontSize: '0.95rem'
                                    }}
                                    placeholder={placeholder}
                                    value={query}
                                    onChange={(e) => setQuery(e.target.value)}
                                />
                                {query && (
                                    <button
                                        type="button"
                                        className="position-absolute top-50 end-0 translate-middle-y btn btn-link text-muted p-0 me-3"
                                        onClick={handleClear}
                                        style={{ textDecoration: 'none', zIndex: 5 }}
                                    >
                                        <i className="bi bi-x-circle-fill"></i>
                                    </button>
                                )}
                            </div>
                        </div>
                    </div>
                    
                    {showCreateButton && createButtonUrl && (
                        <div className="flex-shrink-0">
                            <a href={createButtonUrl} className="btn btn-primary shadow-sm" style={{ borderRadius: '60px', padding: '0.6rem 1.5rem' }}>
                                <i className="bi bi-plus-lg me-2"></i>
                                {createButtonText}
                            </a>
                        </div>
                    )}
                    {showExportButton && (
                        <div className="flex-shrink-0 ms-2">
                            <ExportButton 
                                apiUrl={apiUrl} 
                                filters={filterValues} 
                                searchQuery={query}
                                getHeaders={getExportHeaders}
                                getRows={getExportRows}
                            />
                        </div>
                    )}
                </div>
            </div>

            {items.length > 0 && totalItems > 0 && (
                <div className="d-flex justify-content-between align-items-center mb-3">
                    <div className="text-muted small">
                        <i className="bi bi-info-circle me-1"></i>
                        Найдено: <span className="fw-semibold text-dark">{totalItems}</span> записей
                    </div>
                    {loading && !isLoadingMore && (
                        <div className="small text-muted">
                            <span className="spinner-border spinner-border-sm me-1" style={{ width: '0.8rem', height: '0.8rem' }}></span>
                            Загрузка...
                        </div>
                    )}
                </div>
            )}

            <TableComponent items={items} emptyMessage={emptyMessage} />

            {loading && !isLoadingMore && items.length === 0 && (
                <div className="text-center py-5">
                    <div className="spinner-border text-primary" role="status">
                        <span className="visually-hidden">Загрузка...</span>
                    </div>
                </div>
            )}

            {!loading && hasMore && items.length > 0 && (
                <div ref={lastItemRef} className="text-center py-3 text-muted small">
                    {isLoadingMore ? (
                        <div className="d-inline-flex align-items-center gap-2">
                            <div className="spinner-border spinner-border-sm text-primary" />
                            <span>Загрузка ещё...</span>
                        </div>
                    ) : (
                        <div className="d-inline-flex align-items-center gap-1">
                            <i className="bi bi-arrow-down-short"></i>
                            <span>Прокрутите для загрузки ещё</span>
                        </div>
                    )}
                </div>
            )}

            {!hasMore && items.length > 0 && (
                <div className="text-center py-3 text-muted small border-top pt-3">
                    <i className="bi bi-check-circle me-1"></i>
                    Все записи загружены (всего: {totalItems})
                </div>
            )}
        </div>
    );
}