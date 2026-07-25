<style>
    /* Global SVG size enforcement to prevent giant SVG explosions */
    svg:not([width]) {
        max-width: 24px;
        max-height: 24px;
    }

    /* Custom UI/UX Enhancements for E-Helpdesk Disdik */

    /* Glassmorphism & Soft Card Shadows */
    .fi-wi-stats-overview-stat-card,
    .fi-section,
    .fi-ta-content {
        transition: all 0.25s cubic-bezier(0.4, 0, 0.2, 1) !important;
        border-radius: 0.875rem !important;
    }

    .fi-wi-stats-overview-stat-card:hover {
        transform: translateY(-2px);
        box-shadow: 0 12px 24px -6px rgba(0, 0, 0, 0.08), 0 4px 12px -4px rgba(0, 0, 0, 0.04) !important;
    }

    /* Welcome Banner Styling */
    .disdik-banner {
        position: relative;
        overflow: hidden;
        border-radius: 1rem;
        background: linear-gradient(135deg, #0f172a 0%, #1e3a8a 50%, #0369a1 100%);
        padding: 1.75rem 2rem;
        color: #ffffff;
        box-shadow: 0 10px 25px -5px rgba(15, 23, 42, 0.3);
        border: 1px solid rgba(255, 255, 255, 0.15);
        margin-bottom: 1.75rem;
    }

    .disdik-banner-content {
        position: relative;
        z-index: 10;
        display: flex;
        flex-wrap: wrap;
        align-items: center;
        justify-content: space-between;
        gap: 1.5rem;
    }

    .disdik-banner-info {
        max-width: 42rem;
    }

    .disdik-role-badge {
        display: inline-flex;
        align-items: center;
        gap: 0.5rem;
        padding: 0.25rem 0.75rem;
        border-radius: 9999px;
        font-size: 0.75rem;
        font-weight: 600;
        background: rgba(255, 255, 255, 0.15);
        backdrop-filter: blur(8px);
        border: 1px solid rgba(255, 255, 255, 0.2);
        color: #e0f2fe;
        margin-bottom: 0.75rem;
    }

    .disdik-banner-title {
        font-size: 1.75rem;
        font-weight: 800;
        color: #ffffff;
        margin: 0 0 0.5rem 0;
        letter-spacing: -0.02em;
        line-height: 1.2;
    }

    .disdik-banner-desc {
        font-size: 0.875rem;
        color: #cbd5e1;
        margin: 0;
        line-height: 1.6;
    }

    .disdik-banner-actions {
        display: flex;
        flex-wrap: wrap;
        align-items: center;
        gap: 0.75rem;
    }

    .disdik-btn {
        display: inline-flex;
        align-items: center;
        gap: 0.5rem;
        padding: 0.625rem 1.25rem;
        border-radius: 0.75rem;
        font-size: 0.875rem;
        font-weight: 600;
        text-decoration: none;
        transition: all 0.2s ease;
        cursor: pointer;
    }

    .disdik-btn svg {
        width: 18px;
        height: 18px;
        flex-shrink: 0;
    }

    .disdik-btn-primary {
        background-color: #2563eb;
        color: #ffffff !important;
        border: 1px solid rgba(147, 197, 253, 0.3);
        box-shadow: 0 4px 14px rgba(37, 99, 235, 0.4);
    }
    .disdik-btn-primary:hover {
        background-color: #1d4ed8;
        transform: translateY(-1px);
    }

    .disdik-btn-glass {
        background: rgba(255, 255, 255, 0.12);
        color: #ffffff !important;
        border: 1px solid rgba(255, 255, 255, 0.2);
        backdrop-filter: blur(8px);
    }
    .disdik-btn-glass:hover {
        background: rgba(255, 255, 255, 0.22);
        transform: translateY(-1px);
    }

    .disdik-btn-dark {
        background: rgba(15, 23, 42, 0.6);
        color: #f1f5f9 !important;
        border: 1px solid rgba(255, 255, 255, 0.15);
        backdrop-filter: blur(8px);
    }
    .disdik-btn-dark:hover {
        background: rgba(15, 23, 42, 0.85);
        transform: translateY(-1px);
    }

    /* KANBAN BOARD SYSTEM STYLES */
    .kb-top-bar {
        display: flex;
        flex-wrap: wrap;
        align-items: center;
        justify-content: space-between;
        gap: 1rem;
        padding-bottom: 1.25rem;
        margin-bottom: 1.5rem;
        border-bottom: 1px solid #e2e8f0;
    }
    .dark .kb-top-bar {
        border-bottom-color: #334155;
    }

    .kb-title-group .kb-subtitle {
        font-size: 0.75rem;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: 0.05em;
        color: #64748b;
        margin-bottom: 0.25rem;
    }
    .kb-title-group .kb-main-title {
        font-size: 1.75rem;
        font-weight: 800;
        color: #0f172a;
        margin: 0;
        letter-spacing: -0.02em;
    }
    .dark .kb-title-group .kb-main-title {
        color: #f8fafc;
    }

    .kb-controls {
        display: flex;
        flex-wrap: wrap;
        align-items: center;
        gap: 0.75rem;
    }

    .kb-search-wrapper {
        position: relative;
        min-width: 240px;
    }
    .kb-search-input {
        width: 100%;
        padding: 0.5rem 1rem 0.5rem 2.25rem;
        font-size: 0.875rem;
        border-radius: 0.75rem;
        border: 1px solid #cbd5e1;
        background-color: #ffffff;
        color: #0f172a;
        box-shadow: 0 1px 2px rgba(0,0,0,0.05);
    }
    .dark .kb-search-input {
        background-color: #0f172a;
        border-color: #334155;
        color: #f8fafc;
    }
    .kb-search-icon {
        position: absolute;
        left: 0.75rem;
        top: 50%;
        transform: translateY(-50%);
        width: 16px;
        height: 16px;
        color: #94a3b8;
    }

    .kb-select {
        padding: 0.5rem 0.875rem;
        font-size: 0.875rem;
        font-weight: 600;
        border-radius: 0.75rem;
        border: 1px solid #cbd5e1;
        background-color: #ffffff;
        color: #334155;
        cursor: pointer;
    }
    .dark .kb-select {
        background-color: #0f172a;
        border-color: #334155;
        color: #cbd5e1;
    }

    .kb-toggle-group {
        display: inline-flex;
        padding: 0.25rem;
        border-radius: 0.75rem;
        background-color: #e2e8f0;
    }
    .dark .kb-toggle-group {
        background-color: #1e293b;
    }
    .kb-toggle-btn {
        padding: 0.375rem 0.875rem;
        font-size: 0.75rem;
        font-weight: 700;
        border-radius: 0.5rem;
        border: none;
        background: transparent;
        color: #64748b;
        cursor: pointer;
        transition: all 0.15s ease;
    }
    .kb-toggle-btn.active {
        background-color: #ffffff;
        color: #2563eb;
        box-shadow: 0 1px 3px rgba(0,0,0,0.1);
    }
    .dark .kb-toggle-btn.active {
        background-color: #334155;
        color: #ffffff;
    }

    /* Kanban Grid Columns */
    .kb-grid {
        display: grid;
        grid-template-columns: repeat(1, minmax(0, 1fr));
        gap: 1.25rem;
    }
    @media (min-width: 768px) {
        .kb-grid {
            grid-template-columns: repeat(3, minmax(0, 1fr));
        }
    }

    .kb-col {
        display: flex;
        flex-direction: column;
        background-color: #f8fafc;
        border: 1px solid #e2e8f0;
        border-radius: 1rem;
        padding: 1.25rem;
        min-height: 550px;
    }
    .dark .kb-col {
        background-color: #0f172a;
        border-color: #1e293b;
    }

    .kb-col-header {
        display: flex;
        align-items: center;
        justify-content: space-between;
        margin-bottom: 1.25rem;
        padding-bottom: 0.625rem;
        border-bottom: 2px solid #e2e8f0;
    }
    .dark .kb-col-header {
        border-bottom-color: #334155;
    }

    .kb-col-title {
        font-size: 1rem;
        font-weight: 800;
        color: #0f172a;
        display: flex;
        align-items: center;
        gap: 0.5rem;
    }
    .dark .kb-col-title {
        color: #f8fafc;
    }

    .kb-badge-pill {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        min-width: 1.5rem;
        height: 1.5rem;
        padding: 0 0.375rem;
        border-radius: 9999px;
        background-color: #cbd5e1;
        color: #1e293b;
        font-size: 0.75rem;
        font-weight: 700;
    }
    .dark .kb-badge-pill {
        background-color: #334155;
        color: #f8fafc;
    }

    .kb-col-body {
        flex: 1;
        display: flex;
        flex-direction: column;
        gap: 1rem;
    }

    /* Card Styling */
    .kb-card {
        background-color: #ffffff;
        border: 1px solid #e2e8f0;
        border-radius: 0.875rem;
        padding: 1.125rem;
        box-shadow: 0 2px 5px rgba(0,0,0,0.03);
        transition: all 0.2s cubic-bezier(0.4, 0, 0.2, 1);
        display: flex;
        flex-direction: column;
        gap: 0.625rem;
        margin-bottom: 0.875rem;
    }
    .dark .kb-card {
        background-color: #1e293b;
        border-color: #334155;
    }
    .kb-card:hover {
        transform: translateY(-2px);
        box-shadow: 0 10px 20px -5px rgba(0,0,0,0.08);
        border-color: #bfdbfe;
    }

    .kb-card-top {
        display: flex;
        align-items: center;
        justify-content: space-between;
    }

    .kb-priority-pill {
        padding: 0.2rem 0.625rem;
        border-radius: 9999px;
        font-size: 0.6875rem;
        font-weight: 800;
        letter-spacing: 0.04em;
        text-transform: uppercase;
    }
    .kb-priority-critical { background-color: #ffe4e6; color: #e11d48; }
    .kb-priority-high { background-color: #fef3c7; color: #d97706; }
    .kb-priority-medium { background-color: #dbeafe; color: #2563eb; }
    .kb-priority-low { background-color: #f1f5f9; color: #475569; }

    .dark .kb-priority-critical { background-color: #881337; color: #fda4af; }
    .dark .kb-priority-high { background-color: #78350f; color: #fde68a; }
    .dark .kb-priority-medium { background-color: #1e3a8a; color: #93c5fd; }
    .dark .kb-priority-low { background-color: #334155; color: #94a3b8; }

    .kb-card-id {
        font-size: 0.75rem;
        font-weight: 700;
        color: #94a3b8;
    }

    .kb-card-subject {
        font-size: 0.9375rem;
        font-weight: 700;
        color: #0f172a;
        text-decoration: none !important;
        line-height: 1.35;
        margin: 0;
        display: block;
    }
    .dark .kb-card-subject {
        color: #f8fafc;
    }
    .kb-card-subject:hover {
        color: #2563eb;
    }

    .kb-card-meta {
        font-size: 0.75rem;
        color: #64748b;
        display: flex;
        align-items: center;
        gap: 0.375rem;
    }
    .dark .kb-card-meta {
        color: #94a3b8;
    }

    .kb-card-footer {
        display: flex;
        align-items: center;
        justify-content: space-between;
        padding-top: 0.625rem;
        border-top: 1px solid #f1f5f9;
        margin-top: 0.25rem;
    }
    .dark .kb-card-footer {
        border-top-color: #334155;
    }

    .kb-user-info {
        display: flex;
        align-items: center;
        gap: 0.5rem;
    }
    .kb-avatar {
        width: 1.75rem;
        height: 1.75rem;
        border-radius: 9999px;
        background-color: #2563eb;
        color: #ffffff;
        font-size: 0.75rem;
        font-weight: 700;
        display: flex;
        align-items: center;
        justify-content: center;
    }
    .kb-user-name {
        font-size: 0.8125rem;
        font-weight: 600;
        color: #334155;
    }
    .dark .kb-user-name {
        color: #cbd5e1;
    }

    .kb-btn-move {
        font-size: 0.75rem;
        font-weight: 700;
        color: #2563eb;
        background: transparent;
        border: none;
        cursor: pointer;
        padding: 0;
    }
    .kb-btn-move:hover {
        text-decoration: underline;
    }

    .kb-add-dashed {
        display: flex;
        align-items: center;
        justify-content: center;
        gap: 0.5rem;
        width: 100%;
        padding: 0.875rem;
        border: 2px dashed #cbd5e1;
        border-radius: 0.875rem;
        font-size: 0.875rem;
        font-weight: 600;
        color: #64748b;
        background-color: transparent;
        text-decoration: none !important;
        transition: all 0.2s ease;
        cursor: pointer;
        margin-top: auto;
    }
    .dark .kb-add-dashed {
        border-color: #475569;
        color: #94a3b8;
    }
    .kb-add-dashed:hover {
        border-color: #2563eb;
        color: #2563eb;
        background-color: rgba(37, 99, 235, 0.04);
    }

    /* Pulsing Dot Indicators for Badges */
    .fi-badge {
        position: relative;
        font-weight: 600 !important;
        letter-spacing: 0.01em;
        border-radius: 9999px !important;
        padding-left: 0.75rem !important;
        padding-right: 0.75rem !important;
    }

    .status-dot {
        display: inline-block;
        width: 7px;
        height: 7px;
        border-radius: 50%;
        margin-right: 6px;
        animation: pulse-dot 2s cubic-bezier(0.4, 0, 0.6, 1) infinite;
    }

    .status-dot-open { background-color: #3b82f6; box-shadow: 0 0 8px rgba(59, 130, 246, 0.6); }
    .status-dot-progress { background-color: #f59e0b; box-shadow: 0 0 8px rgba(245, 158, 11, 0.6); }
    .status-dot-resolved { background-color: #10b981; box-shadow: 0 0 8px rgba(16, 185, 129, 0.6); }
    .status-dot-closed { background-color: #ef4444; box-shadow: 0 0 8px rgba(239, 68, 68, 0.6); }

    @keyframes pulse-dot {
        0%, 100% { opacity: 1; transform: scale(1); }
        50% { opacity: 0.5; transform: scale(1.2); }
    }

    /* Support Chat Timeline Styling */
    .chat-timeline-container {
        display: flex;
        flex-direction: column;
        gap: 1.25rem;
    }

    .chat-bubble {
        position: relative;
        max-width: 85%;
        padding: 1.125rem 1.375rem;
        border-radius: 1.125rem;
        box-shadow: 0 4px 12px rgba(0, 0, 0, 0.04);
        transition: transform 0.15s ease;
    }

    .chat-bubble:hover {
        transform: translateY(-1px);
    }

    .chat-bubble-left {
        align-self: flex-start;
        background-color: #ffffff;
        border: 1px solid #e5e7eb;
        border-top-left-radius: 0.25rem;
    }

    .dark .chat-bubble-left {
        background-color: #1f2937;
        border-color: #374151;
    }

    .chat-bubble-right {
        align-self: flex-end;
        background-color: #eff6ff;
        border: 1px solid #bfdbfe;
        border-top-right-radius: 0.25rem;
    }

    .dark .chat-bubble-right {
        background-color: #1e293b;
        border-color: #1e40af;
    }

    .chat-avatar {
        width: 2.5rem;
        height: 2.5rem;
        border-radius: 9999px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-weight: 700;
        font-size: 0.95rem;
        color: #ffffff;
        flex-shrink: 0;
    }

    /* Visual Workflow Progress Bar */
    .ticket-stepper {
        display: flex;
        align-items: center;
        justify-content: space-between;
        padding: 1.25rem;
        background: linear-gradient(135deg, #f8fafc 0%, #f1f5f9 100%);
        border: 1px solid #e2e8f0;
        border-radius: 0.875rem;
        margin-bottom: 1.5rem;
    }

    .dark .ticket-stepper {
        background: linear-gradient(135deg, #0f172a 0%, #1e293b 100%);
        border-color: #334155;
    }

    .stepper-step {
        display: flex;
        align-items: center;
        gap: 0.5rem;
        font-size: 0.875rem;
        font-weight: 600;
        color: #64748b;
    }

    .stepper-step.active {
        color: #2563eb;
    }

    .stepper-step.completed {
        color: #16a34a;
    }

    .stepper-circle {
        width: 2rem;
        height: 2rem;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        background-color: #cbd5e1;
        color: #ffffff;
        font-size: 0.8rem;
    }

    .stepper-step.active .stepper-circle {
        background-color: #2563eb;
        box-shadow: 0 0 12px rgba(37, 99, 235, 0.4);
    }

    .stepper-step.completed .stepper-circle {
        background-color: #16a34a;
        box-shadow: 0 0 12px rgba(22, 163, 74, 0.4);
    }

    .stepper-divider {
        flex: 1;
        height: 2px;
        background-color: #e2e8f0;
        margin: 0 1rem;
    }

    .dark .stepper-divider {
        background-color: #334155;
    }

    .stepper-divider.completed {
        background-color: #16a34a;
    }

    /* Custom Scrollbars */
    ::-webkit-scrollbar {
        width: 6px;
        height: 6px;
    }
    ::-webkit-scrollbar-track {
        background: transparent;
    }
    ::-webkit-scrollbar-thumb {
        background: #cbd5e1;
        border-radius: 9999px;
    }
    .dark ::-webkit-scrollbar-thumb {
        background: #475569;
    }
</style>
