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
                                <th className="ps-4 py-3 text-secondary small fw-semibold">Клиент</th>
                                <th className="py-3 text-secondary small fw-semibold">Контакты</th>
                                <th className="pe-4 py-3 text-secondary small fw-semibold text-end">Действия</th>
                            </tr>
                        </thead>
                        <tbody>
                            {items.map(customer => (
                                <tr key={customer.id}>
                                    <td className="ps-4">
                                        <div>
                                            <a href={`/customer/${customer.id}`} className="fw-bold text-dark text-decoration-none d-block">
                                                {customer.name}
                                            </a>
                                            <span className="text-muted small">ID: {customer.id}</span>
                                        </div>
                                    </td>
                                    <td>
                                        {customer.email && (
                                            <div className="small">
                                                <i className="bi bi-envelope me-2 text-muted"></i>{customer.email}
                                            </div>
                                        )}
                                        {customer.phone && (
                                            <div className="small">
                                                <i className="bi bi-telephone me-2 text-muted"></i>{customer.phone}
                                            </div>
                                        )}
                                    </td>
                                    <td className="pe-4 text-end">
                                        <div className="d-flex justify-content-end gap-2">
                                            <a href={`/customer/${customer.id}`} className="btn btn-sm btn-light text-primary border-0" title="Профиль">
                                                <i className="bi bi-person-vcard"></i>
                                            </a>
                                            <a href={`/customer/${customer.id}/edit`} className="btn btn-sm btn-light text-secondary border-0" title="Редактировать">
                                                <i className="bi bi-pencil"></i>
                                            </a>
                                            <form method="post" action={`/customer/${customer.id}`} onSubmit={(e) => {
                                                if (!confirm('Вы уверены?')) e.preventDefault();
                                            }} className="d-inline">
                                                <input type="hidden" name="_token" value={document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || ''} />
                                                <button type="submit" className="btn btn-sm btn-light text-danger border-0" title="Удалить">
                                                    <i className="bi bi-trash3"></i>
                                                </button>
                                            </form>
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