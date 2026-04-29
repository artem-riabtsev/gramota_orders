import React, { useState, useEffect } from 'react';
import AddItemModal from './AddItemModal';
import EditItemModal from './EditItemModal';

export default function OrderShow({ orderId }) {
    const [order, setOrder] = useState(null);
    const [loading, setLoading] = useState(true);
    const [showAddModal, setShowAddModal] = useState(false);
    const [showEditModal, setShowEditModal] = useState(false);
    const [editingItem, setEditingItem] = useState(null);
    const [editingDate, setEditingDate] = useState(false);
    const [newDate, setNewDate] = useState('');

    const loadOrder = async () => {
        try {
            const response = await fetch(`/api/order/${orderId}`);
            const data = await response.json();
            setOrder(data);
            if (data.date) {
                const parts = data.date.split('.');
                if (parts.length === 3) {
                    setNewDate(`${parts[2]}-${parts[1]}-${parts[0]}`);
                }
            }
        } catch (err) {
            console.error(err);
        } finally {
            setLoading(false);
        }
    };

    useEffect(() => {
        loadOrder();
    }, [orderId]);

    const handleAddItem = async () => {
        await loadOrder();
    };

    const handleEditItem = (item) => {
        setEditingItem(item);
        setShowEditModal(true);
    };

    const handleUpdateItem = async () => {
        await loadOrder();
        setShowEditModal(false);
        setEditingItem(null);
    };

    const deleteItem = async (itemId) => {
        if (!confirm('Вы уверены?')) return;
        try {
            const response = await fetch(`/api/order-item/${itemId}`, {
                method: 'DELETE'
            });
            const data = await response.json();
            if (data.success) {
                await loadOrder();
            }
        } catch (err) {
            console.error(err);
            alert('Ошибка удаления');
        }
    };

    const handleSaveDate = async () => {
        try {
            const response = await fetch(`/api/order/${orderId}/date`, {
                method: 'PUT',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ date: newDate })
            });
            const data = await response.json();
            if (data.success) {
                setEditingDate(false);
                await loadOrder();
            }
        } catch (err) {
            console.error(err);
            alert('Ошибка сохранения даты');
        }
    };

    const getStatusClass = (statusValue) => {
        switch(statusValue) {
            case 0: return 'status-empty';
            case 1: return 'status-unpaid';
            case 2: return 'status-partial';
            case 3: return 'status-overpaid';
            case 4: return 'status-paid';
            default: return 'status-empty';
        }
    };

    const getStatusLabel = (statusValue) => {
        switch(statusValue) {
            case 0: return 'Пустой';
            case 1: return 'Не оплачен';
            case 2: return 'Частично оплачен';
            case 3: return 'Переплата';
            case 4: return 'Оплачен';
            default: return 'Пустой';
        }
    };

    if (loading) {
        return (
            <div className="text-center py-5">
                <div className="spinner-border text-primary" role="status">
                    <span className="visually-hidden">Загрузка...</span>
                </div>
            </div>
        );
    }

    if (!order) {
        return (
            <div className="alert alert-warning" role="alert">
                Заказ не найден
            </div>
        );
    }

    return (
        <div>
            {/* Оптимизированная карточка заказа */}
            <div className="card shadow-sm border-0 rounded-3 mb-4">
                <div className="card-body py-3">
                    {/* Первая строка: статус и дата */}
                    <div className="d-flex flex-wrap align-items-center gap-3 mb-2">
                        <span className={`status-badge ${getStatusClass(order.status.value)}`}>
                            {getStatusLabel(order.status.value)}
                        </span>
                        
                        {editingDate ? (
                            <div className="d-flex align-items-center gap-2">
                                <input
                                    type="date"
                                    className="form-control form-control-sm"
                                    value={newDate}
                                    onChange={(e) => setNewDate(e.target.value)}
                                    style={{ width: '150px' }}
                                />
                                <button className="btn btn-sm btn-success" onClick={handleSaveDate}>
                                    <i className="bi bi-check"></i>
                                </button>
                                <button className="btn btn-sm btn-secondary" onClick={() => setEditingDate(false)}>
                                    <i className="bi bi-x"></i>
                                </button>
                            </div>
                        ) : (
                            <div className="d-flex align-items-center gap-2">
                                <span className="text-muted small">
                                    <i className="bi bi-calendar3 me-1"></i>
                                    {order.date}
                                </span>
                                <button 
                                    className="btn btn-sm btn-link text-secondary p-0"
                                    onClick={() => setEditingDate(true)}
                                    title="Изменить дату"
                                >
                                    <i className="bi bi-pencil small"></i>
                                </button>
                            </div>
                        )}
                    </div>

                    {/* Вторая строка: клиент */}
                    <div className="d-flex flex-wrap align-items-center gap-3 mb-2 pb-1 border-bottom">
                        <div className="d-flex align-items-center gap-2">
                            <i className="bi bi-person text-muted small"></i>
                            <span className="small fw-semibold">{order.customer.name}</span>
                        </div>
                        {order.customer.email && (
                            <div className="d-flex align-items-center gap-2">
                                <i className="bi bi-envelope text-muted small"></i>
                                <a href={`mailto:${order.customer.email}`} className="small text-decoration-none">
                                    {order.customer.email}
                                </a>
                            </div>
                        )}
                        {order.customer.phone && (
                            <div className="d-flex align-items-center gap-2">
                                <i className="bi bi-telephone text-muted small"></i>
                                <a href={`tel:${order.customer.phone}`} className="small text-decoration-none">
                                    {order.customer.phone}
                                </a>
                            </div>
                        )}
                        <a 
                            href={`/customer/${order.customer.id}/edit`}
                            className="btn btn-sm btn-link text-secondary p-0 ms-auto"
                            title="Редактировать заказчика"
                        >
                            <i className="bi bi-pencil small"></i>
                        </a>
                    </div>

                    {/* Третья строка: суммы и кнопка PDF */}
                    <div className="d-flex flex-wrap align-items-center justify-content-between gap-3 mt-2">
                        <div className="d-flex flex-wrap gap-4">
                            <div>
                                <span className="text-muted small">Сумма:</span>
                                <span className="fw-semibold ms-1">{order.orderTotal} ₽</span>
                            </div>
                            <div>
                                <span className="text-muted small">Оплачено:</span>
                                <span className="fw-semibold ms-1">{order.totalPaid} ₽</span>
                            </div>
                            <div>
                                <span className="text-muted small">Остаток:</span>
                                <span className="fw-semibold text-primary ms-1">
                                    {(order.orderTotal - order.totalPaid).toFixed(2)} ₽
                                </span>
                            </div>
                        </div>
                        
                        <button 
                            className={`btn btn-sm ${order.items?.length === 0 ? 'btn-secondary' : 'btn-outline-danger'}`}
                            onClick={async () => {
                                const response = await fetch(`/api/order/${orderId}/payment-document`);
                                const blob = await response.blob();
                                const url = window.URL.createObjectURL(blob);
                                const a = document.createElement('a');
                                a.href = url;
                                a.download = `payment_document_${orderId}.pdf`;
                                document.body.appendChild(a);
                                a.click();
                                document.body.removeChild(a);
                                window.URL.revokeObjectURL(url);
                            }}
                            disabled={order.items?.length === 0}
                            title="Скачать платёжный документ"
                        >
                            <i className="bi bi-file-pdf me-1"></i> 
                            <span className="d-none d-sm-inline">Платёжный документ</span>
                            <span className="d-inline d-sm-none">PDF</span>
                        </button>
                    </div>
                </div>
            </div>

            {/* Таблица позиций */}
            <div className="card shadow-sm border-0 rounded-3">
                <div className="card-header bg-white d-flex justify-content-between align-items-center py-2">
                    <h6 className="mb-0 fw-semibold">Позиции заказа</h6>
                    <button className="btn btn-primary btn-sm" onClick={() => setShowAddModal(true)}>
                        <i className="bi bi-plus-lg me-1"></i> Добавить позицию
                    </button>
                </div>
                <div className="card-body p-0">
                    <div className="table-responsive">
                        <table className="table table-hover align-middle mb-0">
                            <thead className="table-light">
                                <tr>
                                    <th className="ps-4 py-2 small">Наименование</th>
                                    <th className="py-2 small">Продукт</th>
                                    <th className="py-2 small text-end" style={{width: '80px'}}>Кол-во</th>
                                    <th className="py-2 small text-end" style={{width: '90px'}}>Цена</th>
                                    <th className="py-2 small text-end" style={{width: '100px'}}>Сумма</th>
                                    <th className="pe-4 py-2" style={{width: '70px'}}></th>
                                </tr>
                            </thead>
                            <tbody>
                                {order.items && order.items.map(item => (
                                    <tr key={item.id}>
                                        <td className="ps-4 small">{item.description}</td>
                                        <td className="small">{item.product.description}</td>
                                        <td className="small text-end">{item.quantity}</td>
                                        <td className="small text-end">{item.price} ₽</td>
                                        <td className="small text-end fw-semibold">{item.lineTotal} ₽</td>
                                        <td className="pe-4 text-end">
                                            <div className="d-flex gap-1 justify-content-end">
                                                <button 
                                                    className="btn btn-sm btn-link text-primary p-0"
                                                    onClick={() => handleEditItem(item)}
                                                    title="Редактировать"
                                                >
                                                    <i className="bi bi-pencil small"></i>
                                                </button>
                                                <button 
                                                    className="btn btn-sm btn-link text-danger p-0"
                                                    onClick={() => deleteItem(item.id)}
                                                    title="Удалить"
                                                >
                                                    <i className="bi bi-trash3 small"></i>
                                                </button>
                                            </div>
                                        </td>
                                    </tr>
                                ))}
                                {(!order.items || order.items.length === 0) && (
                                    <tr>
                                        <td colSpan="6" className="text-center py-4 text-muted small">
                                            Нет позиций в заказе
                                        </td>
                                    </tr>
                                )}
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            <div className="mt-3">
                <button 
                    onClick={() => {
                        if (window.history.length > 1) {
                            window.history.back();
                        } else {
                            window.location.href = '/order';
                        }
                    }}
                    className="btn btn-secondary btn-sm"
                >
                    <i className="bi bi-arrow-left me-1"></i> Назад
                </button>
            </div>

            <AddItemModal
                show={showAddModal}
                onClose={() => setShowAddModal(false)}
                onAdd={handleAddItem}
                orderId={orderId}
            />

            <EditItemModal
                show={showEditModal}
                onClose={() => {
                    setShowEditModal(false);
                    setEditingItem(null);
                }}
                onUpdate={handleUpdateItem}
                item={editingItem}
            />
        </div>
    );
}