import React, { useState } from 'react';
import AssignProductModal from './AssignProductModal';

export default function ReportOrderItemsTable({ items, emptyMessage, onItemsUpdate }) {
    const [showModal, setShowModal] = useState(false);
    const [selectedItem, setSelectedItem] = useState(null);
    const [localItems, setLocalItems] = useState(items);

    React.useEffect(() => {
        setLocalItems(items);
    }, [items]);

    const handleAssignClick = (item) => {
        setSelectedItem(item);
        setShowModal(true);
    };

    const handleProductAssigned = async (itemId, newProductId, newProductDescription) => {
        try {
            const response = await fetch(`/api/order-item/${itemId}`, {
                method: 'PUT',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ productId: newProductId })
            });
            const data = await response.json();
            if (data.success) {
                const updatedItems = localItems.map(item => {
                    if (item.id === itemId) {
                        return {
                            ...item,
                            product: {
                                ...item.product,
                                id: newProductId,
                                description: newProductDescription
                            }
                        };
                    }
                    return item;
                });
                setLocalItems(updatedItems);
                if (onItemsUpdate) {
                    onItemsUpdate(updatedItems);
                }
                setShowModal(false);
            } else {
                alert(data.error || 'Ошибка обновления продукта');
            }
        } catch (err) {
            alert('Ошибка соединения');
        }
    };

    const getStatusClass = (statusValue) => {
        switch(statusValue) {
            case 0: return 'status-badge status-empty';
            case 1: return 'status-badge status-unpaid';
            case 2: return 'status-badge status-partial';
            case 3: return 'status-badge status-overpaid';
            case 4: return 'status-badge status-paid';
            default: return 'status-badge status-empty';
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

    if (!localItems || localItems.length === 0) {
        return (
            <div className="card shadow-sm border-0 rounded-3">
                <div className="card-body p-5 text-center text-muted">
                    <i className="bi bi-receipt fs-1 d-block mb-3"></i>
                    <p className="mb-0">{emptyMessage}</p>
                </div>
            </div>
        );
    }

    return (
        <>
            <div className="card shadow-sm border-0 rounded-3">
                <div className="card-body p-0">
                    <div className="table-responsive">
                        <table className="table table-hover align-middle mb-0">
                            <thead className="table-light">
                                <tr>
                                    <th className="ps-4 py-3 text-secondary small fw-semibold">Описание</th>
                                    <th className="py-3 text-secondary small fw-semibold text-end">Сумма</th>
                                    <th className="py-3 text-secondary small fw-semibold">Заказ</th>
                                    <th className="py-3 text-secondary small fw-semibold">Дата заказа</th>
                                    <th className="py-3 text-secondary small fw-semibold">Клиент</th>
                                    <th className="py-3 text-secondary small fw-semibold">Статус заказа</th>
                                    <th className="pe-4 py-3 text-secondary small fw-semibold text-end">Продукт</th>
                                </tr>
                            </thead>
                            <tbody>
                                {localItems.map(item => (
                                    <tr key={item.id}>
                                        <td className="ps-4">
                                            <span className="fw-medium">{item.description}</span>
                                        </td>
                                        <td className="text-end fw-semibold">{item.lineTotal} ₽</td>
                                        <td>
                                            <a href={`/order/${item.orderId}`} className="text-dark text-decoration-none fw-semibold">
                                                #{item.orderId}
                                            </a>
                                        </td>
                                        <td>{item.orderDate}</td>
                                        <td>{item.customerName}</td>
                                        <td>
                                            <span className={getStatusClass(item.orderStatus.value)}>
                                                {getStatusLabel(item.orderStatus.value)}
                                            </span>
                                        </td>
                                        <td className="pe-4 text-end">
                                            <div className="d-flex align-items-center justify-content-end gap-2">
                                                <span className="text-muted small">{item.product ? item.product.description : '—'}</span>
                                                <button 
                                                    className="btn btn-sm btn-outline-primary"
                                                    onClick={() => handleAssignClick(item)}
                                                    title="Назначить выпуск"
                                                    style={{ borderRadius: '8px', padding: '4px 10px', fontSize: '0.75rem' }}
                                                >
                                                    <i className="bi bi-box-arrow-up-right me-1"></i> 
                                                    Назначить
                                                </button>
                                            </div>
                                        </td>
                                    </tr>
                                ))}
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            <AssignProductModal
                show={showModal}
                onClose={() => setShowModal(false)}
                onAssign={handleProductAssigned}
                item={selectedItem}
            />
        </>
    );
}