import React, { useState } from 'react';
import PriceSearchStep from './PriceSearchStep';
import ManualFormStep from './ManualFormStep';
import { usePriceSearch } from './hooks/usePriceSearch';
import { useProductSearch } from './hooks/useProductSearch';

export default function AddItemModal({ show, onClose, onAdd, orderId }) {
    const [step, setStep] = useState(true);
    const [selectedPrice, setSelectedPrice] = useState(null);
    const [formData, setFormData] = useState({
        description: '', productId: '', productName: '', quantity: 1, price: 0
    });
    const [submitting, setSubmitting] = useState(false);

    const { searchQuery, setSearchQuery, prices, loading: priceLoading, reset: resetPrice } = usePriceSearch();
    const { search: productSearch, setSearch: setProductSearch, products, loading: productLoading, showDropdown, setShowDropdown, reset: resetProduct } = useProductSearch(!selectedPrice);

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

    const handleSkipPrice = () => {
        setSelectedPrice(null);
        setFormData({ description: '', productId: '', productName: '', quantity: 1, price: 0 });
        setProductSearch('');
        setStep(false);
    };

    const handleSubmit = async (data) => {
        if (!data.productId) { alert('Выберите продукт'); return; }
        
        setSubmitting(true);
        try {
            const payload = { orderId, description: data.description, productId: data.productId, quantity: data.quantity, price: data.price };
            if (selectedPrice) payload.priceId = selectedPrice.id;
            
            const response = await fetch('/api/order-item/create', { method: 'POST', headers: { 'Content-Type': 'application/json' }, body: JSON.stringify(payload) });
            const result = await response.json();
            
            if (result.success) {
                onAdd(result.item);
                handleClose();
            } else alert(result.error || 'Ошибка создания позиции');
        } catch (err) { alert('Ошибка соединения'); }
        finally { setSubmitting(false); }
    };

    const handleClose = () => {
        setStep(true);
        setSelectedPrice(null);
        setFormData({ description: '', productId: '', productName: '', quantity: 1, price: 0 });
        resetPrice();
        resetProduct();
        onClose();
    };

    if (!show) return null;

    if (step) {
        return <PriceSearchStep searchQuery={searchQuery} setSearchQuery={setSearchQuery} prices={prices} loading={priceLoading} onSelectPrice={handleSelectPrice} onSkip={handleSkipPrice} onClose={handleClose} />;
    }

    return <ManualFormStep selectedPrice={selectedPrice} formData={formData} setFormData={setFormData} products={products} loadingProducts={productLoading} showProductDropdown={showDropdown} setShowProductDropdown={setShowDropdown} onSubmit={handleSubmit} onBack={() => setStep(true)} onClose={handleClose} isSubmitting={submitting} />;
}