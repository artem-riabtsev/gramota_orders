import React from 'react';

export default function PricesTable({ items, emptyMessage }) {
    if (!items || items.length === 0) {
        return (
            <div className="card shadow-sm border-0 rounded-3">
                <div className="card-body p-5 text-center text-muted">
                    <i className="bi bi-tag fs-1 d-block mb-3"></i>
                    <p className="mb-0">{emptyMessage}</p>
                </div>
            </div>
        );
    }

    return (
        <div className="card shadow-sm border-0 rounded-3">
            <div className="card-body p-0">
                <div className="table-responsive">
                    <table className="table table-hover align-middle mb-0">
                        <thead className="table-light">
                            <tr>
                                <th className="ps-4 py-3 text-secondary small fw-semibold">ID</th>
                                <th className="py-3 text-secondary small fw-semibold">Наименование</th>
                                <th className="py-3 text-secondary small fw-semibold">Продукт</th>
                                <th className="py-3 text-secondary small fw-semibold text-end">Цена</th>
                                <th className="pe-4 py-3 text-secondary small fw-semibold text-end">Действия</th>
                            </tr>
                        </thead>
                        <tbody>
                            {items.map(price => (
                                <tr key={price.id}>
                                    <td className="ps-4">
                                        <span className="text-muted small">#{price.id}</span>
                                    </td>
                                    <td>
                                        <span className="fw-medium">{price.description}</span>
                                    </td>
                                    <td>
                                        <span className="text-muted small">{price.product.description}</span>
                                    </td>
                                    <td className="text-end">
                                        <span className="fw-semibold">{price.price} ₽</span>
                                    </td>
                                    <td className="pe-4 text-end">
                                        <div className="d-flex justify-content-end gap-2">
                                            <a href={`/price/${price.id}/edit`} className="btn btn-sm btn-light text-secondary border-0" title="Редактировать">
                                                <i className="bi bi-pencil"></i>
                                            </a>
                                            <button 
                                                onClick={async () => {
                                                    if (!confirm('Вы уверены?')) return;
                                                    try {
                                                        const response = await fetch(`/api/price/${price.id}/delete`, {
                                                            method: 'DELETE',
                                                            headers: { 'Content-Type': 'application/json' }
                                                        });
                                                        const data = await response.json();
                                                        if (data.success) {
                                                            window.location.reload();
                                                        } else {
                                                            alert(data.error || 'Ошибка удаления');
                                                        }
                                                    } catch (err) {
                                                        alert('Ошибка соединения');
                                                    }
                                                }}
                                                className="btn btn-sm btn-light text-danger border-0"
                                                title="Удалить"
                                            >
                                                <i className="bi bi-trash3"></i>
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
    );
}