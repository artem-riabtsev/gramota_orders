import React, { useState, useEffect } from 'react';

export default function PaymentNew() {
    const [searchQuery, setSearchQuery] = useState('');
    const [orders, setOrders] = useState([]);
    const [loading, setLoading] = useState(false);
    const [selectedOrder, setSelectedOrder] = useState(null);
    const [submitting, setSubmitting] = useState(false);

    // Поиск заказов
    useEffect(() => {
        if (searchQuery.length < 2) {
            setOrders([]);
            return;
        }

        setLoading(true);
        const controller = new AbortController();

        fetch(`/api/order/search?q=${encodeURIComponent(searchQuery)}`, {
            signal: controller.signal
        })
            .then(res => res.json())
            .then(data => {
                setOrders(data);
                setLoading(false);
            })
            .catch(err => {
                if (err.name !== 'AbortError') {
                    console.error(err);
                    setLoading(false);
                }
            });

        return () => controller.abort();
    }, [searchQuery]);

    const handleSelectOrder = (order) => {
        setSelectedOrder(order);
        setSearchQuery('');
        setOrders([]);
    };

    const handleSubmit = async (e) => {
        e.preventDefault();

        if (!selectedOrder) {
            alert('Выберите заказ');
            return;
        }

        setSubmitting(true);

        try {
            const response = await fetch('/api/payment/create', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ orderId: selectedOrder.id })
            });

            const data = await response.json();

            if (data.success) {
                window.location.href = `/payment/${data.paymentId}/edit`;
            } else {
                alert(data.error || 'Ошибка создания платежа');
            }
        } catch (err) {
            console.error(err);
            alert('Ошибка соединения');
        } finally {
            setSubmitting(false);
        }
    };

    return (
        <div className="container mt-4">
            <div className="card shadow-sm border-0 rounded-3">
                <div className="card-header bg-white">
                    <h4 className="mb-0 fw-semibold">Добавление платежа</h4>
                </div>
                <div className="card-body">
                    <form onSubmit={handleSubmit}>
                        {!selectedOrder ? (
                            <>
                                <div className="mb-3">
                                    <label className="form-label fw-semibold">Поиск заказа</label>
                                    <input
                                        type="text"
                                        className="form-control form-control-lg"
                                        placeholder="Введите номер заказа или имя клиента..."
                                        value={searchQuery}
                                        onChange={(e) => setSearchQuery(e.target.value)}
                                        autoFocus
                                    />
                                    {loading && (
                                        <div className="mt-2 text-muted">
                                            <div className="spinner-border spinner-border-sm me-2"></div>
                                            Поиск...
                                        </div>
                                    )}
                                </div>

                                {orders.length > 0 && (
                                    <div className="list-group mb-3" style={{ maxHeight: '300px', overflowY: 'auto' }}>
                                        {orders.map(order => (
                                            <button
                                                key={order.id}
                                                type="button"
                                                className="list-group-item list-group-item-action d-flex justify-content-between align-items-center"
                                                onClick={() => handleSelectOrder(order)}
                                            >
                                                <div>
                                                    <strong>Заказ #{order.id}</strong>
                                                    <br />
                                                    <small className="text-muted">{order.customer.name}</small>
                                                </div>
                                                <div className="text-end">
                                                    <div>Сумма: {order.orderTotal} ₽</div>
                                                    <div className="small text-muted">Оплачено: {order.totalPaid} ₽</div>
                                                </div>
                                            </button>
                                        ))}
                                    </div>
                                )}

                                {searchQuery.length >= 2 && orders.length === 0 && !loading && (
                                    <div className="alert alert-info">
                                        Заказы не найдены. 
                                        <a href="/order/new" className="alert-link ms-2">Создать новый заказ?</a>
                                    </div>
                                )}
                            </>
                        ) : (
                            <div className="alert alert-success d-flex justify-content-between align-items-center">
                                <div>
                                    <strong>Выбран заказ:</strong> #{selectedOrder.id}<br />
                                    <small>{selectedOrder.customer.name} | Сумма: {selectedOrder.orderTotal} ₽ | Оплачено: {selectedOrder.totalPaid} ₽</small>
                                </div>
                                <button 
                                    type="button"
                                    className="btn btn-sm btn-outline-secondary"
                                    onClick={() => {
                                        setSelectedOrder(null);
                                        setSearchQuery('');
                                    }}
                                >
                                    Изменить
                                </button>
                            </div>
                        )}

                        <hr className="my-4" />

                        <div className="d-flex gap-2">
                            <button 
                                type="submit" 
                                className="btn btn-primary"
                                disabled={!selectedOrder || submitting}
                            >
                                {submitting ? (
                                    <>
                                        <span className="spinner-border spinner-border-sm me-2"></span>
                                        Создание...
                                    </>
                                ) : (
                                    'Перейти к заполнению платежа'
                                )}
                            </button>

                            <a href="/payment" className="btn btn-secondary">
                                Отмена
                            </a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    );
}