import React, { useEffect } from 'react';
import { useForm } from 'react-hook-form';

export default function ManualFormStep({ selectedPrice, formData, setFormData, products, loadingProducts, showProductDropdown, setShowProductDropdown, onSubmit, onBack, onClose, isSubmitting }) {
    const { register, handleSubmit, watch, setValue, reset, formState: { errors } } = useForm({
        defaultValues: formData
    });

    useEffect(() => {
        reset(formData);
    }, [formData, reset]);

    const watchQuantity = watch('quantity');
    const watchPrice = watch('price');
    const total = (watchQuantity || 0) * (watchPrice || 0);

    const handleSelectProduct = (product) => {
        setValue('productId', product.id);
        setValue('productName', product.description);
        setFormData(prev => ({ ...prev, productId: product.id, productName: product.description }));
        setShowProductDropdown(false);
    };

    return (
        <div className="modal show d-block" style={{ backgroundColor: 'rgba(0,0,0,0.5)' }} tabIndex="-1">
            <div className="modal-dialog modal-md">
                <div className="modal-content rounded-4">
                    <div className="modal-header border-0 pt-4 px-4">
                        <h5 className="modal-title fw-semibold">
                            {selectedPrice ? 'Редактирование позиции' : 'Новая позиция'}
                        </h5>
                        <button type="button" className="btn-close" onClick={onClose}></button>
                    </div>
                    <div className="modal-body px-4">
                        <form onSubmit={handleSubmit(onSubmit)}>
                            <div className="mb-3">
                                <label className="form-label small fw-semibold">Наименование</label>
                                <input type="text" className="form-control" {...register('description')} placeholder="Введите наименование" />
                            </div>
                            
                            <div className="mb-3">
                                <label className="form-label small fw-semibold">Продукт</label>
                                {formData.productId ? (
                                    <div className="d-flex justify-content-between align-items-center p-2 bg-light rounded-3">
                                        <span>{formData.productName}</span>
                                        <button type="button" className="btn btn-sm btn-outline-secondary" onClick={() => {
                                            setValue('productId', '');
                                            setValue('productName', '');
                                            setFormData(prev => ({ ...prev, productId: '', productName: '' }));
                                            setShowProductDropdown(true);
                                        }}>
                                            <i className="bi bi-pencil"></i> Изменить
                                        </button>
                                    </div>
                                ) : (
                                    <div className="position-relative">
                                        <input type="text" className="form-control" placeholder="Введите название продукта..." {...register('productSearch')} onFocus={() => setShowProductDropdown(true)} />
                                        {showProductDropdown && (
                                            <div className="position-absolute top-100 start-0 end-0 mt-1 border rounded-3 bg-white shadow-sm" style={{ zIndex: 1000, maxHeight: '250px', overflowY: 'auto' }}>
                                                {loadingProducts ? (
                                                    <div className="text-center py-3"><div className="spinner-border spinner-border-sm"></div></div>
                                                ) : (
                                                    products.map(product => (
                                                        <button key={product.id} type="button" className="dropdown-item py-2" onClick={() => handleSelectProduct(product)}>
                                                            {product.description}
                                                        </button>
                                                    ))
                                                )}
                                            </div>
                                        )}
                                    </div>
                                )}
                            </div>
                            
                            <div className="row">
                                <div className="col-md-6 mb-3">
                                    <label className="form-label small fw-semibold">Количество</label>
                                    <input type="number" className="form-control" {...register('quantity', { min: 1 })} min="1" step="1" />
                                    {errors.quantity && <small className="text-danger">Количество должно быть больше 0</small>}
                                </div>
                                <div className="col-md-6 mb-3">
                                    <label className="form-label small fw-semibold">Цена</label>
                                    <input type="number" step="0.01" className="form-control" {...register('price', { min: 0.01 })} />
                                    {errors.price && <small className="text-danger">Цена должна быть больше 0</small>}
                                </div>
                            </div>
                            
                            <div className="alert alert-light bg-light border-0 rounded-3">
                                <div className="d-flex justify-content-between">
                                    <span>Итого:</span>
                                    <span className="fw-bold fs-5">{total.toFixed(2)} ₽</span>
                                </div>
                            </div>
                            
                            <div className="modal-footer border-0 px-0 pb-0">
                                <button type="button" className="btn btn-light" onClick={onBack}>Назад</button>
                                <button type="submit" className="btn btn-primary" disabled={isSubmitting}>
                                    {isSubmitting ? 'Сохранение...' : 'Добавить позицию'}
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    );
}