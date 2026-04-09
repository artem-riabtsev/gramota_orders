import React from 'react';

export default function ProductsTable({ items, emptyMessage }) {
    if (!items || items.length === 0) {
        return (
            <div className="card shadow-sm border-0 rounded-3">
                <div className="card-body p-5 text-center text-muted">
                    <i className="bi bi-box fs-1 d-block mb-3"></i>
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
                                <th className="py-3 text-secondary small fw-semibold">Проект</th>
                                <th className="py-3 text-secondary small fw-semibold">Дата</th>
                                <th className="pe-4 py-3 text-secondary small fw-semibold text-end">Действия</th>
                            </tr>
                        </thead>
                        <tbody>
                            {items.map(product => (
                                <tr key={product.id}>
                                    <td className="ps-4">
                                        <span className="text-muted small">#{product.id}</span>
                                    </td>
                                    <td>
                                        <span className="fw-medium">{product.description}</span>
                                        {product.basic && (
                                            <span className="badge bg-info ms-2 small">Базовый</span>
                                        )}
                                    </td>
                                    <td>{product.project}</td>
                                    <td>{product.date}</td>
                                    <td className="pe-4 text-end">
                                        <div className="d-flex justify-content-end gap-2">
                                            <a href={`/product/${product.id}/edit`} className="btn btn-sm btn-light text-secondary border-0" title="Редактировать">
                                                <i className="bi bi-pencil"></i>
                                            </a>
                                            <button 
                                                onClick={async () => {
                                                    if (!confirm('Вы уверены?')) return;
                                                    try {
                                                        const response = await fetch(`/api/product/${product.id}/delete`, {
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