import React, { useState, useEffect } from 'react';

export default function EditItemModal({ show, onClose, onUpdate, item }) {
    const [formData, setFormData] = useState({
        id: null,
        description: '',
        productId: '',
        productName: '',
        quantity: 1,
        price: 0
    });
    const [products, setProducts] = useState([]);
    const [loadingProducts, setLoadingProducts] = useState(false);
    const [productSearch, setProductSearch] = useState('');
    const [showProductDropdown, setShowProductDropdown] = useState(false);
    const [submitting, setSubmitting] = useState(false);

    // Загрузка всех продуктов при открытии
    useEffect(() => {
        if (!show) return;
        
        if (item) {
            setFormData({
                id: item.id,
                description: item.description,
                productId: item.product.id,
                productName: item.product.description,
                quantity: item.quantity,
                price: item.price
            });
            setProductSearch(item.product.description);
        }
        
        setLoadingProducts(true);
        fetch('/api/product/search?limit=50')
            .then(res => res.json())
            .then(data => {
                setProducts(data);
                setLoadingProducts(false);
            })
            .catch(err => {
                console.error(err);
                setLoadingProducts(false);
            });
    }, [show, item]);

    // Фильтрация продуктов
    useEffect(() => {
        if (!show) return;
        
        if (!productSearch || productSearch.length < 2) {
            setLoadingProducts(true);
            fetch('/api/product/search?limit=50')
                .then(res => res.json())
                .then(data => {
                    setProducts(data);
                    setLoadingProducts(false);
                    setShowProductDropdown(true);
                })
                .catch(err => {
                    console.error(err);
                    setLoadingProducts(false);
                });
            return;
        }
        
        const timer = setTimeout(() => {
            setLoadingProducts(true);
            fetch(`/api/product/search?q=${encodeURIComponent(productSearch)}&limit=50`)
                .then(res => res.json())
                .then(data => {
                    setProducts(data);
                    setLoadingProducts(false);
                    setShowProductDropdown(true);
                })
                .catch(err => {
                    console.error(err);
                    setLoadingProducts(false);
                });
        }, 300);
        
        return () => clearTimeout(timer);
    }, [productSearch, show]);

    const handleSelectProduct = (product) => {
        setFormData(prev => ({
            ...prev,
            productId: product.id,
            productName: product.description
        }));
        setProductSearch(product.description);
        setShowProductDropdown(false);
    };

    const updateFormField = (field, value) => {
        setFormData(prev => ({ ...prev, [field]: value }));
    };

    const handleSubmit = async () => {
        if (!formData.productId) {
            alert('Выберите продукт');
            return;
        }
        
        if (formData.quantity < 1) {
            alert('Количество должно быть больше 0');
            return;
        }
        
        if (formData.price <= 0) {
            alert('Цена должна быть больше 0');
            return;
        }
        
        setSubmitting(true);
        
        try {
            const response = await fetch(`/api/order-item/${formData.id}`, {
                method: 'PUT',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({
                    description: formData.description,
                    productId: formData.productId,
                    quantity: formData.quantity,
                    price: formData.price
                })
            });
            
            const data = await response.json();
            
            if (data.success) {
                onUpdate();
                handleClose();
            } else {
                alert(data.error || 'Ошибка обновления позиции');
            }
        } catch (err) {
            console.error(err);
            alert('Ошибка соединения');
        } finally {
            setSubmitting(false);
        }
    };

    const handleClose = () => {
        setFormData({
            id: null,
            description: '',
            productId: '',
            productName: '',
            quantity: 1,
            price: 0
        });
        setProductSearch('');
        setShowProductDropdown(false);
        onClose();
    };

    if (!show || !item) return null;

    return (
        <div className="modal show d-block" style={{ backgroundColor: 'rgba(0,0,0,0.5)' }} tabIndex="-1">
            <div className="modal-dialog modal-md">
                <div className="modal-content rounded-4">
                    <div className="modal-header border-0 pt-4 px-4">
                        <h5 className="modal-title fw-semibold">Редактирование позиции</h5>
                        <button type="button" className="btn-close" onClick={handleClose}></button>
                    </div>
                    <div className="modal-body px-4">
                        {/* Наименование */}
                        <div className="mb-3">
                            <label className="form-label small fw-semibold">Наименование</label>
                            <input
                                type="text"
                                className="form-control"
                                value={formData.description}
                                onChange={(e) => updateFormField('description', e.target.value)}
                                placeholder="Введите наименование"
                            />
                        </div>
                        
                        {/* Продукт */}
                        <div className="mb-3">
                            <label className="form-label small fw-semibold">Продукт</label>
                            
                            {formData.productId ? (
                                <div className="d-flex justify-content-between align-items-center p-2 bg-light rounded-3">
                                    <span>{formData.productName}</span>
                                    <button 
                                        type="button"
                                        className="btn btn-sm btn-outline-secondary"
                                        onClick={() => {
                                            updateFormField('productId', '');
                                            updateFormField('productName', '');
                                            setProductSearch('');
                                            setShowProductDropdown(true);
                                        }}
                                    >
                                        <i className="bi bi-pencil"></i> Изменить
                                    </button>
                                </div>
                            ) : (
                                <div className="position-relative">
                                    <input
                                        type="text"
                                        className="form-control"
                                        placeholder="Введите название продукта..."
                                        value={productSearch}
                                        onChange={(e) => setProductSearch(e.target.value)}
                                        onFocus={() => setShowProductDropdown(true)}
                                        autoFocus
                                    />
                                    {showProductDropdown && (
                                        <div className="position-absolute top-100 start-0 end-0 mt-1 border rounded-3 bg-white shadow-sm" style={{ zIndex: 1000, maxHeight: '250px', overflowY: 'auto' }}>
                                            {loadingProducts ? (
                                                <div className="text-center py-3">
                                                    <div className="spinner-border spinner-border-sm"></div>
                                                </div>
                                            ) : (
                                                products.map(product => (
                                                    <button
                                                        key={product.id}
                                                        type="button"
                                                        className="dropdown-item py-2 my-1"
                                                        style={{ 
                                                            borderRadius: '8px',
                                                            marginBottom: '4px',
                                                            cursor: 'pointer',
                                                            transition: 'background-color 0.2s'
                                                        }}
                                                        onClick={() => handleSelectProduct(product)}
                                                        onMouseEnter={(e) => e.currentTarget.style.backgroundColor = '#f8f9fa'}
                                                        onMouseLeave={(e) => e.currentTarget.style.backgroundColor = 'transparent'}
                                                    >
                                                        {product.description}
                                                    </button>
                                                ))
                                            )}
                                            {!loadingProducts && products.length === 0 && (
                                                <div className="text-center py-3 text-muted small">
                                                    Ничего не найдено
                                                </div>
                                            )}
                                        </div>
                                    )}
                                </div>
                            )}
                        </div>
                        
                        {/* Количество и Цена */}
                        <div className="row">
                            <div className="col-md-6 mb-3">
                                <label className="form-label small fw-semibold">Количество</label>
                                <input
                                    type="number"
                                    className="form-control"
                                    value={formData.quantity}
                                    onChange={(e) => updateFormField('quantity', parseInt(e.target.value) || 1)}
                                    min="1"
                                    step="1"
                                />
                            </div>
                            <div className="col-md-6 mb-3">
                                <label className="form-label small fw-semibold">Цена</label>
                                <input
                                    type="number"
                                    className="form-control"
                                    value={formData.price}
                                    onChange={(e) => updateFormField('price', parseFloat(e.target.value) || 0)}
                                    min="0"
                                    step="0.01"
                                />
                            </div>
                        </div>
                        
                        {/* Итого */}
                        <div className="alert alert-light bg-light border-0 rounded-3">
                            <div className="d-flex justify-content-between">
                                <span>Итого:</span>
                                <span className="fw-bold fs-5">
                                    {(formData.quantity * formData.price).toFixed(2)} ₽
                                </span>
                            </div>
                        </div>
                    </div>
                    <div className="modal-footer border-0 px-4 pb-4">
                        <button type="button" className="btn btn-light" onClick={handleClose}>
                            Отмена
                        </button>
                        <button 
                            type="button" 
                            className="btn btn-primary"
                            onClick={handleSubmit}
                            disabled={submitting || !formData.productId || formData.price <= 0}
                        >
                            {submitting ? (
                                <>
                                    <span className="spinner-border spinner-border-sm me-2"></span>
                                    Сохранение...
                                </>
                            ) : (
                                'Сохранить изменения'
                            )}
                        </button>
                    </div>
                </div>
            </div>
        </div>
    );
}