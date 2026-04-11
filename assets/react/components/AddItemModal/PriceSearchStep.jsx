import React from 'react';

export default function PriceSearchStep({ searchQuery, setSearchQuery, prices, loading, onSelectPrice, onSkip, onClose }) {
    return (
        <div className="modal show d-block" style={{ backgroundColor: 'rgba(0,0,0,0.5)' }} tabIndex="-1">
            <div className="modal-dialog modal-md">
                <div className="modal-content rounded-4">
                    <div className="modal-header border-0 pt-4 px-4">
                        <h5 className="modal-title fw-semibold">Выбор позиции из прайса</h5>
                        <button type="button" className="btn-close" onClick={onClose}></button>
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
                        
                        {loading && (
                            <div className="text-center py-3">
                                <div className="spinner-border spinner-border-sm text-primary"></div>
                            </div>
                        )}
                        
                        <div className="list-group" style={{ maxHeight: '400px', overflowY: 'auto' }}>
                            <button type="button" className="list-group-item list-group-item-action border-0 py-3" onClick={onSkip}>
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
                                <button key={price.id} type="button" className="list-group-item list-group-item-action border-0 py-3" onClick={() => onSelectPrice(price)}>
                                    <div className="d-flex justify-content-between align-items-center w-100">
                                        <div>
                                            <div className="fw-semibold">{price.description}</div>
                                            <div className="small text-muted">{price.product.description}</div>
                                        </div>
                                        <div className="fw-semibold text-primary">{price.price} ₽</div>
                                    </div>
                                </button>
                            ))}
                            
                            {searchQuery.length >= 2 && prices.length === 0 && !loading && (
                                <div className="text-center py-4 text-muted">
                                    <i className="bi bi-inbox fs-1 d-block mb-2"></i>
                                    <small>Ничего не найдено</small>
                                </div>
                            )}
                        </div>
                    </div>
                    <div className="modal-footer border-0 px-4 pb-4">
                        <button type="button" className="btn btn-light" onClick={onClose}>Отмена</button>
                    </div>
                </div>
            </div>
        </div>
    );
}