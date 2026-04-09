import './bootstrap.js';
import './styles/app.css';

import React from 'react';
import { createRoot } from 'react-dom/client';
import LiveSearchTable from './react/components/LiveSearchTable';
import OrdersTable from './react/components/OrdersTable';
import CustomersTable from './react/components/CustomersTable';
import ProductsTable from './react/components/ProductsTable';
import PricesTable from './react/components/PricesTable';
import PaymentsTable from './react/components/PaymentsTable';
import OrderNew from './react/components/OrderNew';

window.React = React;
window.ReactDOM = { createRoot };
window.LiveSearchTable = LiveSearchTable;
window.OrdersTable = OrdersTable;
window.CustomersTable = CustomersTable;
window.ProductsTable = ProductsTable;
window.PricesTable = PricesTable;
window.PaymentsTable = PaymentsTable;
window.OrderNew = OrderNew;