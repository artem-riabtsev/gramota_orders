// assets/react/components/EditItemModal.jsx
import React, { useState, useEffect } from 'react';
import ManualFormStep from './AddItemModal/ManualFormStep';
import { useProductSearch } from './AddItemModal/hooks/useProductSearch';

export default function EditItemModal({ show, onClose, onUpdate, item }) {
    const [formData, setFormData] = useState({
        description: '',
        productId: '',
        productName: '',
        quantity: 1,
        price: 0
    });
    const [submitting, setSubmitting] = useState(false);

    const { products, loading: productLoading, showDropdown, setShowDropdown } = useProductSearch();

    useEffect(() => {
        if (show && item) {
            setFormData({
                description: item.description,
                productId: item.product.id,
                productName: item.product.description,
                quantity: item.quantity,
                price: item.price
            });
        }
    }, [show, item]);

    const handleSubmit = async (data) => {
        if (!data.productId) {
            alert('Выберите продукт');
            return;
        }
        setSubmitting(true);
        try {
            const response = await fetch(`/api/order-item/${item.id}`, {
                method: 'PUT',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({
                    description: data.description,
                    productId: data.productId,
                    quantity: data.quantity,
                    price: data.price
                })
            });
            const result = await response.json();
            if (result.success) {
                onUpdate();
                handleClose();
            } else {
                alert(result.error || 'Ошибка обновления позиции');
            }
        } catch (err) {
            alert('Ошибка соединения');
        } finally {
            setSubmitting(false);
        }
    };

    const handleClose = () => {
        setFormData({ description: '', productId: '', productName: '', quantity: 1, price: 0 });
        onClose();
    };

    if (!show || !item) return null;

    return (
        <ManualFormStep 
            selectedPrice={null}
            formData={formData}
            setFormData={setFormData}
            products={products}
            loadingProducts={productLoading}
            showProductDropdown={showDropdown}
            setShowProductDropdown={setShowDropdown}
            onSubmit={handleSubmit}
            onBack={handleClose}
            onClose={handleClose}
            isSubmitting={submitting}
        />
    );
}