import React, { useState, useEffect } from 'react';

export default function AssignProductModal({ show, onClose, onAssign, item }) {
    const [products, setProducts] = useState([]);
    const [searchQuery, setSearchQuery] = useState('');
    const [loading, setLoading] = useState(false);
    const [selectedProductId, setSelectedProductId] = useState(null);
    const [submitting, setSubmitting] = useState(false);

    useEffect(() => {
        if (!show || !item) return;
        
        setLoading(true);
        fetch(`/api/product/by-project?projectId=${item.product.projectId}&q=${searchQuery}`)
            .then(res => res.json())
            .then(data => {
                setProducts(data);
                setLoading(false);
            })
            .catch(() => setLoading(false));
    }, [show, item, searchQuery]);

    const handleSearch = (e) => {
        setSearchQuery(e.target.value);
    };

    const handleSelect = (productId) => {
        setSelectedProductId(productId);
    };

    const handleSubmit = async () => {
        if (!selectedProductId) {
            alert('Выберите продукт');
            return;
        }
        setSubmitting(true);
        
        // Находим выбранный продукт
        const selectedProduct = products.find(p => p.id === selectedProductId);
        
        await onAssign(item.id, selectedProductId, selectedProduct?.description);
        setSubmitting(false);
    };

    if (!show || !item) return null;

    return (
        <div className="modal show d-block" style={{ backgroundColor: 'rgba(0,0,0,0.5)' }} tabIndex="-1">
            <div className="modal-dialog modal-md">
                <div className="modal-content rounded-4">
                    <div className="modal-header border-0 pt-4 px-4">
                        <h5 className="modal-title fw-semibold">Назначение выпуска</h5>
                        <button type="button" className="btn-close" onClick={onClose}></button>
                    </div>
                    <div className="modal-body px-4">
                        <div className="mb-3">
                            <label className="form-label small fw-semibold">Текущая позиция</label>
                            <div className="p-2 bg-light rounded-3">
                                <div className="fw-semibold">{item.description}</div>
                                <div className="small text-muted">Текущий продукт: {item.product.description}</div>
                            </div>
                        </div>
                        
                        <div className="mb-3">
                            <label className="form-label small fw-semibold">Поиск продукта</label>
                            <div className="position-relative">
                                <i className="bi bi-search position-absolute top-50 start-0 translate-middle-y ms-3 text-muted"></i>
                                <input
                                    type="text"
                                    className="form-control ps-5"
                                    style={{ borderRadius: '50px' }}
                                    placeholder="Поиск по наименованию..."
                                    value={searchQuery}
                                    onChange={handleSearch}
                                    autoFocus
                                />
                            </div>
                        </div>
                        
                        {loading && (
                            <div className="text-center py-3">
                                <div className="spinner-border spinner-border-sm text-primary"></div>
                            </div>
                        )}
                        
                        <div className="list-group" style={{ maxHeight: '300px', overflowY: 'auto' }}>
                            {products.map(product => (
                                <button
                                    key={product.id}
                                    type="button"
                                    className={`list-group-item list-group-item-action py-2 ${selectedProductId === product.id ? 'bg-light' : ''}`}
                                    onClick={() => handleSelect(product.id)}
                                >
                                    <div className="fw-semibold">{product.description}</div>
                                    <div className="small text-muted">
                                        {product.basic && <span className="badge bg-info me-2">Базовый</span>}
                                        Дата: {product.date}
                                    </div>
                                </button>
                            ))}
                            {!loading && products.length === 0 && (
                                <div className="text-center py-4 text-muted">
                                    <i className="bi bi-inbox fs-1 d-block mb-2"></i>
                                    <small>Продукты не найдены</small>
                                </div>
                            )}
                        </div>
                    </div>
                    <div className="modal-footer border-0 px-4 pb-4">
                        <button type="button" className="btn btn-light" onClick={onClose}>
                            Отмена
                        </button>
                        <button 
                            type="button" 
                            className="btn btn-primary"
                            onClick={handleSubmit}
                            disabled={submitting || !selectedProductId}
                        >
                            {submitting ? 'Сохранение...' : 'Назначить выпуск'}
                        </button>
                    </div>
                </div>
            </div>
        </div>
    );
}