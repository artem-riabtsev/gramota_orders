import React from 'react';

export default function CustomersTable({ items, emptyMessage }) {
    if (!items || items.length === 0) {
        return (
            <div className="card shadow-sm border-0 rounded-3">
                <div className="card-body p-5 text-center text-muted">
                    <i className="bi bi-people fs-1 d-block mb-3"></i>
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
                                <th className="py-3 text-secondary small fw-semibold">ФИО</th>
                                <th className="py-3 text-secondary small fw-semibold">Email</th>
                                <th className="py-3 text-secondary small fw-semibold">Телефон</th>
                                <th className="pe-4 py-3 text-secondary small fw-semibold text-end">Действия</th>
                            </tr>
                        </thead>
                        <tbody>
                            {items.map(customer => (
                                <tr key={customer.id}>
                                    <td className="ps-4">
                                        <span className="text-muted small">#{customer.id}</span>
                                    </td>
                                    <td>
                                        <a href={`/customer/${customer.id}`} className="fw-semibold text-dark text-decoration-none">
                                            {customer.name}
                                        </a>
                                    </td>
                                    <td>
                                        {customer.email ? (
                                            <a href={`mailto:${customer.email}`} className="text-muted text-decoration-none small">
                                                {customer.email}
                                            </a>
                                        ) : (
                                            <span className="text-muted small">—</span>
                                        )}
                                    </td>
                                    <td>
                                        {customer.phone ? (
                                            <a href={`tel:${customer.phone}`} className="text-muted text-decoration-none small">
                                                {customer.phone}
                                            </a>
                                        ) : (
                                            <span className="text-muted small">—</span>
                                        )}
                                    </td>
                                    <td className="pe-4 text-end">
                                        <div className="d-flex justify-content-end gap-2">
                                            <a href={`/customer/${customer.id}`} className="btn btn-sm btn-light text-primary border-0" title="Просмотр">
                                                <i className="bi bi-eye"></i>
                                            </a>
                                            <a href={`/customer/${customer.id}/edit`} className="btn btn-sm btn-light text-secondary border-0" title="Редактировать">
                                                <i className="bi bi-pencil"></i>
                                            </a>
                                            <button 
                                                onClick={async () => {
                                                    if (!confirm('Вы уверены?')) return;
                                                    try {
                                                        const response = await fetch(`/api/customer/${customer.id}/delete`, {
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