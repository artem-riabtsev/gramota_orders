import React, { useState, useEffect } from 'react';

export default function AddItemModal({ show, onClose, onAdd, orderId }) {
    const [step, setStep] = useState(true);
    
    // Шаг 1: выбор из прайса
    const [searchQuery, setSearchQuery] = useState('');
    const [prices, setPrices] = useState([]);
    const [loadingPrices, setLoadingPrices] = useState(false);
    const [selectedPrice, setSelectedPrice] = useState(null);
    
    // Шаг 2: форма позиции
    const [formData, setFormData] = useState({
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

    // Загрузка всех продуктов при открытии шага 2
    useEffect(() => {
        if (step || !show) return;
        
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
    }, [step, show]);

    // Фильтрация продуктов при вводе поиска
    useEffect(() => {
        if (step || !show) return;
        
        if (!productSearch || productSearch.length < 2) {
            // Если поиск пустой или меньше 2 символов - показываем все продукты
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
    }, [productSearch, step, show]);

    // Поиск в прайсе
    useEffect(() => {
        if (!step || !show) return;
        
        const timer = setTimeout(() => {
            if (searchQuery.length < 2 && searchQuery !== '') {
                setPrices([]);
                return;
            }
            
            setLoadingPrices(true);
            fetch(`/api/price/list?q=${encodeURIComponent(searchQuery)}&limit=20`)
                .then(res => res.json())
                .then(data => {
                    setPrices(data.data || []);
                    setLoadingPrices(false);
                })
                .catch(err => {
                    console.error(err);
                    setLoadingPrices(false);
                });
        }, 300);
        
        return () => clearTimeout(timer);
    }, [searchQuery, step, show]);

    // Выбор позиции из прайса
    const handleSelectPrice = (price) => {
        setSelectedPrice(price);
        setFormData({
            description: price.description,
            productId: price.product.id,
            productName: price.product.description,
            quantity: 1,
            price: parseFloat(price.price)
        });
        setProductSearch(price.product.description);
        setStep(false);
    };

    // Выбор "Не использовать прайс"
    const handleSkipPrice = () => {
        setSelectedPrice(null);
        setFormData({
            description: '',
            productId: '',
            productName: '',
            quantity: 1,
            price: 0
        });
        setProductSearch('');
        setStep(false);
    };

    // Выбор продукта из списка
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
            const payload = {
                orderId: orderId,
                description: formData.description,
                productId: formData.productId,
                quantity: formData.quantity,
                price: formData.price
            };
            
            if (selectedPrice) {
                payload.priceId = selectedPrice.id;
            }
            
            const response = await fetch('/api/order-item/create', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify(payload)
            });
            
            const data = await response.json();
            
            if (data.success) {
                onAdd(data.item);
                handleClose();
            } else {
                alert(data.error || 'Ошибка создания позиции');
            }
        } catch (err) {
            console.error(err);
            alert('Ошибка соединения');
        } finally {
            setSubmitting(false);
        }
    };

    const handleClose = () => {
        setStep(true);
        setSearchQuery('');
        setPrices([]);
        setSelectedPrice(null);
        setFormData({
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

    if (!show) return null;

    // Шаг 1: выбор из прайса
    if (step) {
        return (
            <div className="modal show d-block" style={{ backgroundColor: 'rgba(0,0,0,0.5)' }} tabIndex="-1">
                <div className="modal-dialog modal-md">
                    <div className="modal-content rounded-4">
                        <div className="modal-header border-0 pt-4 px-4">
                            <h5 className="modal-title fw-semibold">Выбор позиции из прайса</h5>
                            <button type="button" className="btn-close" onClick={handleClose}></button>
                        </div>
                        <div className="modal-body px-4">
                            <div className="mb-3">
                                <div className="position-relative">
                                    <i className="bi bi-search position-absolute top-50 start-0 translate-middle-y ms-3 text-muted"></i>
                                    <input
                                        type="text"
                                        className="form-control ps-5 py-2"
                                        style={{ borderRadius: '50px' }}
                                        placeholder="Поиск по прайсу..."
                                        value={searchQuery}
                                        onChange={(e) => setSearchQuery(e.target.value)}
                                        autoFocus
                                    />
                                </div>
                            </div>
                            
                            {loadingPrices && (
                                <div className="text-center py-3">
                                    <div className="spinner-border spinner-border-sm text-primary"></div>
                                </div>
                            )}
                            
                            <div className="list-group" style={{ maxHeight: '400px', overflowY: 'auto' }}>
                                <button
                                    type="button"
                                    className="list-group-item list-group-item-action border-0 py-3"
                                    onClick={handleSkipPrice}
                                >
                                    <div className="d-flex align-items-center">
                                        <i className="bi bi-plus-circle fs-4 me-3 text-secondary"></i>
                                        <div>
                                            <div className="fw-semibold">Не использовать прайс</div>
                                            <div className="small text-muted">Заполнить всё вручную</div>
                                        </div>
                                    </div>
                                </button>
                                
                                <div className="dropdown-divider my-1"></div>
                                
                                {prices.map(price => (
                                    <button
                                        key={price.id}
                                        type="button"
                                        className="list-group-item list-group-item-action border-0 py-3"
                                        onClick={() => handleSelectPrice(price)}
                                    >
                                        <div className="d-flex justify-content-between align-items-center w-100">
                                            <div>
                                                <div className="fw-semibold">{price.description}</div>
                                                <div className="small text-muted">{price.product.description}</div>
                                            </div>
                                            <div className="fw-semibold text-primary">{price.price} ₽</div>
                                        </div>
                                    </button>
                                ))}
                                
                                {searchQuery.length >= 2 && prices.length === 0 && !loadingPrices && (
                                    <div className="text-center py-4 text-muted">
                                        <i className="bi bi-inbox fs-1 d-block mb-2"></i>
                                        <small>Ничего не найдено</small>
                                    </div>
                                )}
                            </div>
                        </div>
                        <div className="modal-footer border-0 px-4 pb-4">
                            <button type="button" className="btn btn-light" onClick={handleClose}>Отмена</button>
                        </div>
                    </div>
                </div>
            </div>
        );
    }

    // Шаг 2: форма редактирования позиции
    return (
        <div className="modal show d-block" style={{ backgroundColor: 'rgba(0,0,0,0.5)' }} tabIndex="-1">
            <div className="modal-dialog modal-md">
                <div className="modal-content rounded-4">
                    <div className="modal-header border-0 pt-4 px-4">
                        <h5 className="modal-title fw-semibold">
                            {selectedPrice ? 'Редактирование позиции' : 'Новая позиция'}
                        </h5>
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
                                                        className="dropdown-item py-2"
                                                        onClick={() => handleSelectProduct(product)}
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
                        <button type="button" className="btn btn-light" onClick={() => setStep(true)}>
                            Назад
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
                                'Добавить позицию'
                            )}
                        </button>
                    </div>
                </div>
            </div>
        </div>
    );
}