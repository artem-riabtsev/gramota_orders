import './bootstrap.js';
import './styles/app.css';

import React from 'react';
import { createRoot } from 'react-dom/client';
import OrderNew from './react/components/OrderNew';
import CustomerSearch from './react/components/CustomerSearch';

// Делаем компоненты доступными
window.React = React;
window.ReactDOM = { createRoot };
window.OrderNew = OrderNew;
window.CustomerSearch = CustomerSearch;