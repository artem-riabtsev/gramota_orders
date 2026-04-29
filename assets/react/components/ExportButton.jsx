import React, { useState } from 'react';

export default function ExportButton({ apiUrl, filters, searchQuery, getHeaders, getRows }) {
    const [exporting, setExporting] = useState(false);

    const exportToCSV = async () => {
        setExporting(true);
        try {
            const params = new URLSearchParams();
            if (searchQuery) params.set('q', searchQuery);
            params.set('limit', 10000);
            
            if (filters) {
                Object.keys(filters).forEach(key => {
                    const value = filters[key];
                    if (value !== null && value !== '' && value !== undefined) {
                        params.set(key, value);
                    }
                });
            }
            
            const response = await fetch(`${apiUrl}?${params.toString()}`);
            const result = await response.json();
            
            if (!result.data || result.data.length === 0) {
                alert('Нет данных для экспорта');
                setExporting(false);
                return;
            }
            
            // Если переданы кастомные функции — используем их
            let headers, rows;
            if (getHeaders && getRows) {
                headers = getHeaders();
                rows = getRows(result.data);
            } else {
                // Стандартный вариант для отчета по позициям
                headers = ['Описание', 'Продукт', 'Сумма', 'Номер заказа', 'Дата заказа', 'Клиент', 'Статус'];
                rows = result.data.map(item => [
                    item.description,
                    item.product?.description || '',
                    item.lineTotal,
                    item.orderId,
                    item.orderDate,
                    item.customerName || '',
                    item.orderStatus.label
                ]);
            }
            
            const csvContent = [headers, ...rows]
                .map(row => row.map(cell => `"${String(cell).replace(/"/g, '""')}"`).join(';'))
                .join('\n');
            
            const blob = new Blob(["\uFEFF" + csvContent], { type: 'text/csv;charset=utf-8;' });
            const link = document.createElement('a');
            const url = URL.createObjectURL(blob);
            link.href = url;
            link.setAttribute('download', `export_${new Date().toISOString().slice(0,19)}.csv`);
            document.body.appendChild(link);
            link.click();
            document.body.removeChild(link);
            URL.revokeObjectURL(url);
        } catch (err) {
            console.error(err);
            alert('Ошибка экспорта');
        } finally {
            setExporting(false);
        }
    };

    return (
        <button 
            onClick={exportToCSV}
            className="btn btn-outline-success shadow-sm"
            style={{ borderRadius: '60px', padding: '0.6rem 1.2rem' }}
            disabled={exporting}
        >
            <i className="bi bi-file-earmark-spreadsheet me-2"></i>
            {exporting ? 'Экспорт...' : 'Экспорт в CSV'}
        </button>
    );
}