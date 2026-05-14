export const settings = {
    title: 'Configuración',
    subtitle: 'Administra los ajustes de tu cuenta y privacidad',
    sections: {
        account: {
            label: 'Cuenta',
            desc: 'Información personal y seguridad'
        },
        privacy: {
            label: 'Privacidad',
            desc: 'Control de actividad'
        },
        language: {
            label: 'Idioma',
            desc: 'Selecciona tu idioma'
        },
        cookies: {
            label: 'Cookies y Analíticas',
            desc: 'Preferencias de seguimiento'
        },
        notifications: {
            label: 'Notificaciones',
            desc: 'Alertas y avisos'
        },
        tutors: {
            label: 'Tutores Legales',
            desc: 'Vínculos familiares'
        }
    },
    account: {
        title: 'Cuenta',
        email_label: 'Email',
        status_label: 'Estado',
        status_active: 'Activa'
    },
    tutors: {
        title: 'Tutores Legales',
        dni_placeholder: 'Introduce el DNI del tutor',
        add_button: 'Buscar y Añadir',
        no_tutors: 'No tienes tutores vinculados.',
        tutor_name: 'Nombre del tutor',
        remove_confirm: '¿Estás seguro de eliminar este tutor?',
        search_error: 'No se encontró ningún tutor con ese DNI o no es un tutor válido.',
        add_success: 'Tutor añadido correctamente.',
        remove_modal: {
            title: '¿Eliminar tutor?',
            description: 'Esta acción desvinculará a este tutor de tu cuenta de forma permanente.',
            confirm: 'Eliminar',
            cancel: 'Cancelar'
        }
    },
    privacy: {
        title: 'Privacidad',
        coming_soon: 'Próximamente: Controles de visibilidad de perfil y mensajes directos.'
    },
    language: {
        title: 'Idioma',
        spanish: 'Español',
        english: 'Inglés'
    },
    cookies: {
        title: 'Cookies y Analíticas',
        desc: 'Utilizamos Umami para recopilar datos anónimos de uso y mejorar nuestra plataforma. No utilizamos cookies de rastreo invasivas ni vendemos tus datos.',
        tracking_label: 'Seguimiento de cookies',
        tracking_on: 'Las analíticas están activadas. Gracias por ayudarnos a mejorar.',
        tracking_off: 'El seguimiento está desactivado.',
        accept_all: 'Aceptar todas',
        reject_optional: 'Rechazar opcionales'
    },
    notifications: {
        title: 'Notificaciones',
        coming_soon: 'Próximamente: Configura tus avisos por email y notificaciones push.'
    }
}
