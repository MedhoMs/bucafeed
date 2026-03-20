{{-- 
    Centralized Color Constants
    Modify these CSS variables to change the theme colors across the admin panel.
--}}
<style>
    :root {
        /* --- COLORES PARA CAMBIAR --- */
        
        /* Color principal (Botones, bordes activos, etc) */
        --admin-primary: #4fd1c5; 
        
        /* Brillo suave del color principal */
        --admin-primary-glow: rgba(79, 209, 197, 0.4);
        
        /* Fondo muy sutil del color principal */
        --admin-primary-soft: rgba(79, 209, 197, 0.1);
        
        /* --- FONDOS --- */
        
        /* Fondo base de la página */
        --admin-bg-main: #0a141d;
        
        /* Gradiente superior della página (Inicio) */
        --admin-bg-gradient-start: #1a3a3a;
        
        /* Gradiente superior della página (Medio) */
        --admin-bg-gradient-via: #10202e;
        
        /* Fondo de las tarjetas/tablas */
        --admin-bg-card: rgba(15, 25, 34, 0.4);
        
        /* Fondo de los inputs y dropdowns */
        --admin-bg-input: rgba(15, 25, 34, 0.6);
        
        /* --- BORDES Y TEXTOS --- */
        
        /* Borde general de tablas y botones */
        --admin-border: rgba(255, 255, 255, 0.1);
        
        /* Texto principal (Blanco) */
        --admin-text-main: #ffffff;
        
        /* Texto secundario/desactivado */
        --admin-text-muted: rgba(255, 255, 255, 0.4);

        /* --- ACENTOS COMPARTIDOS (Formal) --- */
        
        /* Acento 1: Teal (Coordina con el fondo) */
        --admin-accent-1: #4fd1c5;
        --admin-accent-1-bg: rgba(79, 209, 197, 0.15);
        --admin-accent-1-border: rgba(79, 209, 197, 0.3);
        
        /* Acento 2: Azul Slate (Sexto tono para contraste suave) */
        --admin-accent-2: #94a3b8;
        --admin-accent-2-bg: rgba(148, 163, 184, 0.15);
        --admin-accent-2-border: rgba(148, 163, 184, 0.3);
        
        /* Acento Neutro: Blanco/Gris (Sutil) */
        --admin-accent-neutral: rgba(255, 255, 255, 0.7);
        --admin-accent-neutral-bg: rgba(255, 255, 255, 0.08);
        --admin-accent-neutral-border: rgba(255, 255, 255, 0.15);

        /* --- MAPEO DE ETIQUETAS (BADGES) --- */
        
        /* Estas etiquetas ahora comparten los acentos para ser más formales */
        
        --admin-badge-purple-bg: var(--admin-accent-1-bg);
        --admin-badge-purple-text: var(--admin-accent-1);
        --admin-badge-purple-border: var(--admin-accent-1-border);
        
        --admin-badge-emerald-bg: var(--admin-accent-2-bg);
        --admin-badge-emerald-text: var(--admin-accent-2);
        --admin-badge-emerald-border: var(--admin-accent-2-border);
        
        --admin-badge-blue-bg: var(--admin-accent-2-bg);
        --admin-badge-blue-text: var(--admin-accent-2);
        --admin-badge-blue-border: var(--admin-accent-2-border);
        
        --admin-badge-red-bg: var(--admin-accent-neutral-bg);
        --admin-badge-red-text: var(--admin-accent-neutral);
        --admin-badge-red-border: var(--admin-accent-neutral-border);
        
        --admin-badge-yellow-bg: var(--admin-accent-1-bg);
        --admin-badge-yellow-text: var(--admin-accent-1);
        --admin-badge-yellow-border: var(--admin-accent-1-border);
        
        --admin-badge-white-bg: var(--admin-accent-neutral-bg);
        --admin-badge-white-text: var(--admin-accent-neutral);
        --admin-badge-white-border: var(--admin-accent-neutral-border);
    }

    /* Helper classes using these variables */
    .bg-admin-card { background-color: var(--admin-bg-card); }
    .border-admin { border-color: var(--admin-border); }
    .text-admin-main { color: var(--admin-text-main); }
    .text-admin-muted { color: var(--admin-text-muted); }
    .ring-admin-primary:focus { --tw-ring-color: var(--admin-primary-focus); }
</style>
