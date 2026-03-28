import React, { useState } from 'react';
import CustomerSearch from './CustomerSearch';

export default function OrderNew() {
    const [selectedCustomer, setSelectedCustomer] = useState(null);
    const [submitting, setSubmitting] = useState(false);

    const handleSubmit = async (e) => {
        e.preventDefault();
        
        if (!selectedCustomer) {
            alert('Выберите заказчика');
            return;
        }

        setSubmitting(true);
        
        try {
            const response = await fetch('/api/order/create', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest'
                },
                body: JSON.stringify({ customerId: selectedCustomer.id })
            });
            
            const data = await response.json();
            
            if (data.success) {
                window.location.href = `/order/${data.orderId}`;
            } else {
                alert(data.error || 'Ошибка создания заказа');
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
            <div className="card shadow-sm">
                <div className="card-header bg-white">
                    <h4 className="mb-0">Создание нового заказа</h4>
                </div>
                <div className="card-body">
                    <form onSubmit={handleSubmit}>
                        <CustomerSearch onSelectCustomer={setSelectedCustomer} />
                        
                        <hr className="my-4" />
                        
                        <div className="d-flex gap-2">
                            <button 
                                type="submit" 
                                className="btn btn-primary"
                                disabled={!selectedCustomer || submitting}
                            >
                                {submitting ? (
                                    <>
                                        <span className="spinner-border spinner-border-sm me-2"></span>
                                        Создание...
                                    </>
                                ) : (
                                    'Создать заказ'
                                )}
                            </button>
                            
                            <a href="/order" className="btn btn-secondary">
                                Отмена
                            </a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    );
}