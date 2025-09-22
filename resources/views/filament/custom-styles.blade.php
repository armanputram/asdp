{{-- resources/views/filament/custom-styles.blade.php --}}

<style>
    /* Custom Filament Table Styling untuk Card Layout */

    .fi-ta-content {
        background: transparent !important;
    }

    .fi-ta-table {
        background: transparent !important;
    }

    /* Card layout untuk operasional data */
    .operasional-grid {
        display: grid;
        grid-template-columns: repeat(auto-fill, minmax(350px, 1fr));
        gap: 20px;
        padding: 20px 0;
    }

    .operasional-record-card {
        background: white;
        border-radius: 16px;
        padding: 24px;
        box-shadow: 0 4px 20px rgba(0, 0, 0, 0.08);
        border: 1px solid #e2e8f0;
        transition: all 0.3s ease;
        position: relative;
        overflow: hidden;
    }

    .operasional-record-card:hover {
        transform: translateY(-4px);
        box-shadow: 0 12px 30px rgba(0, 0, 0, 0.15);
        border-color: #3b82f6;
    }

    .operasional-record-card::before {
        content: '';
        position: absolute;
        top: 0;
        left: 0;
        right: 0;
        height: 4px;
        background: linear-gradient(90deg, #3b82f6, #8b5cf6, #ec4899);
    }

    .record-header {
        display: flex;
        align-items: center;
        justify-content: space-between;
        margin-bottom: 20px;
        padding-bottom: 16px;
        border-bottom: 2px solid #f1f5f9;
    }

    .layanan-badge {
        background: linear-gradient(135deg, #3b82f6, #1d4ed8);
        color: white;
        padding: 8px 16px;
        border-radius: 20px;
        font-size: 0.875rem;
        font-weight: 600;
        text-transform: uppercase;
        letter-spacing: 0.5px;
    }

    .record-actions {
        display: flex;
        gap: 8px;
    }

    .action-btn {
        padding: 8px 12px;
        border-radius: 8px;
        border: none;
        cursor: pointer;
        font-size: 0.8rem;
        font-weight: 500;
        transition: all 0.2s ease;
    }

    .btn-edit { background: #3b82f6; color: white; }
    .btn-delete { background: #ef4444; color: white; }
    .btn-pdf { background: #059669; color: white; }

    .action-btn:hover {
        opacity: 0.8;
        transform: scale(1.05);
    }

    .record-info {
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 16px;
    }

    .info-item {
        display: flex;
        align-items: center;
        gap: 10px;
        color: #475569;
        font-size: 0.9rem;
    }

    .info-icon {
        width: 24px;
        height: 24px;
        background: #f1f5f9;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        color: #3b82f6;
        font-size: 0.8rem;
    }

    .info-label {
        font-weight: 500;
        color: #64748b;
        min-width: 60px;
    }

    .info-value {
        font-weight: 600;
        color: #1e293b;
    }

    /* Responsive design */
    @media (max-width: 1024px) {
        .operasional-grid {
            grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
            gap: 16px;
        }
    }

    @media (max-width: 768px) {
        .operasional-grid {
            grid-template-columns: 1fr;
            gap: 16px;
        }

        .record-info {
            grid-template-columns: 1fr;
            gap: 12px;
        }

        .record-header {
            flex-direction: column;
            align-items: stretch;
            gap: 12px;
        }

        .record-actions {
            justify-content: center;
        }
    }

    /* Custom scrollbar */
    .operasional-grid::-webkit-scrollbar {
        width: 8px;
    }

    .operasional-grid::-webkit-scrollbar-track {
        background: #f1f5f9;
        border-radius: 4px;
    }

    .operasional-grid::-webkit-scrollbar-thumb {
        background: #cbd5e1;
        border-radius: 4px;
    }

    .operasional-grid::-webkit-scrollbar-thumb:hover {
        background: #94a3b8;
    }

    /* Animation classes */
    .fade-in {
        animation: fadeIn 0.5s ease-in-out;
    }

    @keyframes fadeIn {
        from {
            opacity: 0;
            transform: translateY(20px);
        }
        to {
            opacity: 1;
            transform: translateY(0);
        }
    }

    /* Loading state */
    .loading-skeleton {
        background: linear-gradient(90deg, #f0f0f0 25%, #e0e0e0 50%, #f0f0f0 75%);
        background-size: 200% 100%;
        animation: loading 1.5s infinite;
        border-radius: 8px;
        height: 20px;
        margin-bottom: 10px;
    }

    @keyframes loading {
        0% { background-position: 200% 0; }
        100% { background-position: -200% 0; }
    }

    /* Enhanced Filament panel styling */
    .fi-section {
        border: none !important;
        box-shadow: none !important;
        background: transparent !important;
    }

    .fi-section-content {
        padding: 0 !important;
    }

    /* Hide default Filament table when using card view */
    .hide-default-table .fi-ta {
        display: none;
    }

    /* Custom filter styling */
    .fi-ta-filters {
        background: white;
        border-radius: 12px;
        padding: 16px;
        margin-bottom: 20px;
        box-shadow: 0 2px 10px rgba(0, 0, 0, 0.05);
    }
</style>
