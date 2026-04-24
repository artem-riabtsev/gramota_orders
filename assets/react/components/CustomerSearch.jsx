import React, { useState, useEffect } from 'react';

export default function CustomerSearch({ onSelectCustomer }) {
    const [query, setQuery] = useState('');
    const [customers, setCustomers] = useState([]);
    const [loading, setLoading] = useState(false);
    const [selected, setSelected] = useState(null);

    useEffect(() => {
        if (query.length < 2) {
            setCustomers([]);
            return;
        }

        setLoading(true);
        const controller = new AbortController();

        fetch(`/api/customer/search?q=${encodeURIComponent(query)}`, {
            signal: controller.signal
        })
            .then(res => res.json())
            .then(data => {
                setCustomers(data);
                setLoading(false);
            })
            .catch(err => {
                if (err.name !== 'AbortError') {
                    console.error(err);
                    setLoading(false);
                }
            });

        return () => controller.abort();
    }, [query]);

    const handleSelect = (customer) => {
        setSelected(customer);
        setQuery('');
        setCustomers([]);
        onSelectCustomer(customer);
    };

    return (
        <div>
            {!selected ? (
                <>
                    <div className="mb-3">
                        <label className="form-label fw-bold">Поиск заказчика</label>
                        <input
                            type="text"
                            className="form-control form-control-lg"
                            placeholder="Введите ФИО или email (минимум 2 символа)..."
                            value={query}
                            onChange={(e) => setQuery(e.target.value)}
                            autoFocus
                        />
                        {loading && (
                            <div className="mt-2 text-muted">
                                <div className="spinner-border spinner-border-sm me-2"></div>
                                    Поиск...
                            </div>
                        )}
                    </div>

                    {customers.length > 0 && (
                        <div className="list-group mb-3" style={{ maxHeight: '300px', overflowY: 'auto' }}>
                            {customers.map(customer => (
                                <button
                                    key={customer.id}
                                    type="button"
                                    className="list-group-item list-group-item-action d-flex justify-content-between align-items-center"
                                    onClick={() => handleSelect(customer)}
                                >
                                    <div>
                                        <strong>{customer.name}</strong>
                                        <br />
                                        <small className="text-muted">{customer.email}</small>
                                    </div>
                                    <span className="badge bg-secondary rounded-pill">
                                        {customer.phone}
                                    </span>
                                </button>
                            ))}
                        </div>
                    )}

                    {query.length >= 2 && customers.length === 0 && !loading && (
                        <div className="alert alert-info">
                            Заказчики не найдены. 
                            <a href="/customer/new?from=order" className="alert-link ms-2">
                                Создать нового?
                            </a>
                        </div>
                    )}
                </>
            ) : (
                <div className="alert alert-success d-flex justify-content-between align-items-center">
                    <div>
                        <strong>Выбран заказчик:</strong> {selected.name}<br />
                        <small>{selected.email} | {selected.phone}</small>
                    </div>
                    <button 
                        className="btn btn-sm btn-outline-secondary"
                        onClick={() => {
                            setSelected(null);
                            onSelectCustomer(null);
                        }}
                    >
                        Изменить
                    </button>
                </div>
            )}
        </div>
    );
}