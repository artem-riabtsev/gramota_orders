import React from 'react';

export default function OrdersTable({ items, emptyMessage }) {
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
            case 2: return 'Частично';
            case 3: return 'Переплата';
            case 4: return 'Оплачен';
            default: return 'Пустой';
        }
    };

    if (!items || items.length === 0) {
        return (
            <div className="card shadow-sm border-0 rounded-3">
                <div className="card-body p-5 text-center text-muted">
                    <i className="bi bi-cart fs-1 d-block mb-3"></i>
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
                                <th className="ps-4 py-3 text-secondary small fw-semibold">Номер</th>
                                <th className="py-3 text-secondary small fw-semibold">Дата</th>
                                <th className="py-3 text-secondary small fw-semibold">Клиент</th>
                                <th className="py-3 text-secondary small fw-semibold text-end" style={{minWidth: '90px'}}>Сумма</th>
                                <th className="py-3 text-secondary small fw-semibold text-end" style={{minWidth: '90px'}}>Оплачено</th>
                                <th className="py-3 text-secondary small fw-semibold" style={{minWidth: '100px'}}>Статус</th>
                                <th className="pe-4 py-3 text-secondary small fw-semibold text-end" style={{minWidth: '70px'}}></th>
                            </tr>
                        </thead>
                        <tbody>
                            {items.map(order => (
                                <tr key={order.id}>
                                    <td className="ps-4">
                                        <a href={`/order/${order.id}`} className="fw-semibold text-dark text-decoration-none">
                                            {order.id}
                                        </a>
                                    </td>
                                    <td>{order.date}</td>
                                    <td>
                                        <a href={`/customer/${order.customer.id}`} className="text-dark text-decoration-none">
                                            {order.customer.name}
                                        </a>
                                    </td>
                                    <td className="text-end fw-semibold" style={{whiteSpace: 'nowrap'}}>{order.orderTotal} ₽</td>
                                    <td className="text-end text-muted" style={{whiteSpace: 'nowrap'}}>{order.totalPaid} ₽</td>
                                    <td>
                                        <span className={getStatusClass(order.status.value)} style={{whiteSpace: 'nowrap'}}>
                                            {getStatusLabel(order.status.value)}
                                        </span>
                                    </td>
                                    <td className="pe-4 text-end">
                                        <div className="d-flex justify-content-end gap-2" style={{whiteSpace: 'nowrap'}}>
                                            <a href={`/order/${order.id}`} className="btn btn-sm btn-light text-primary border-0" title="Просмотр">
                                                <i className="bi bi-eye"></i>
                                            </a>
                                            <form method="post" action={`/order/${order.id}`} onSubmit={(e) => {
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