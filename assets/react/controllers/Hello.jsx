import React from 'react';

export default function Hello({ name }) {
    return (
        <div className="alert alert-info shadow-sm d-flex align-items-center" role="alert">
            <i className="bi bi-rocket-takeoff-fill me-3 fs-3"></i>
            <div>
                <strong>Система готова!</strong> Привет, {name}. React успешно подключен к Gramota Orders.
            </div>
        </div>
    );
}