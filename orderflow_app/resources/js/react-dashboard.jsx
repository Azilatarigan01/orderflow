import React from 'react';
import { createRoot } from 'react-dom/client';
import ManagementDashboard from './react-dashboard/ManagementDashboard';

const rootElement = document.getElementById('react-management-dashboard-root');

if (rootElement) {
    let initialUser = {};
    try {
        if (rootElement.dataset.user) {
            initialUser = JSON.parse(rootElement.dataset.user);
        }
    } catch (e) {
        console.warn('Could not parse initial user dataset:', e);
    }

    const root = createRoot(rootElement);
    root.render(
        <React.StrictMode>
            <ManagementDashboard initialUser={initialUser} />
        </React.StrictMode>
    );
}
