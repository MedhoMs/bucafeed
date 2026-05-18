export const manager = {
    modals: {
        create: {
            title: "Crear Nuevo Grupo",
            msg: "Grupo creado con éxito",
            nameLabel: "Nombre Identificativo",
            namePlaceholder: "Ej: 1º DAW A o 2º Primaria B"
        },
        tutor: {
            title: "Asignar Tutor",
            msg: "Tutor asignado con éxito",
            groupInfo: "Grupo Seleccionado",
            teacherLabel: "Personal Docente"
        },
        students: {
            title: "Gestión de Alumnos",
            msg: "Alumnos actualizados con éxito",
            censusLabel: "Censo de Alumnos"
        },
        subject: {
            title: "Asignar Asignatura",
            msg: "Docencia asignada con éxito",
            teacherLabel: "Personal Docente"
        },
        event: {
            title: "Crear Nuevo Evento",
            msg: "Evento publicado con éxito",
            titleLabel: "Título del Evento",
            titlePlaceholder: "Ej: Jornada Deportiva",
            descLabel: "Descripción",
            descPlaceholder: "¿De qué trata el evento?",
            locationLabel: "Nombre de la calle",
            locationPlaceholder: "Ej: Calle de la Marina, 12",
            dateLabel: "Fecha",
            startLabel: "Hora Inicio",
            endLabel: "Hora Fin",
            imageLabel: "Imagen de portada"
        },
        editEvent: {
            title: "Editar Evento",
            msg: "Evento actualizado correctamente",
            imageLabel: "Nueva imagen (opcional)"
        },
        publication: {
            title: "Crear Nueva Publicación",
            msg: "Publicación creada con éxito",
            titleLabel: "Título de la Publicación",
            titlePlaceholder: "Ej: Logro académico",
            descLabel: "Contenido",
            descPlaceholder: "¿De qué trata la publicación?",
            imageLabel: "Imagen de portada"
        },
        editPublication: {
            title: "Editar Publicación",
            msg: "Publicación actualizada correctamente",
            imageLabel: "Nueva imagen (opcional)"
        },
        enrollUsers: {
            title: "Matricular Usuarios",
            msg: "Usuarios matriculados con éxito",
            searchPlaceholder: "Buscar por nombre o email...",
            tabs: {
                student: "Alumnos",
                teacher: "Profesores"
            }
        },
        enrollCycles: {
            title: "Vincular Ciclos Formativos",
            msg: "Ciclos vinculados con éxito",
            searchPlaceholder: "Buscar ciclo formativo..."
        },
        user: {
            title: "Crear Nuevo Usuario",
            msg: "Usuario creado con éxito",
            nameLabel: "Nombre",
            lastNameLabel: "Apellidos",
            dniLabel: "DNI/NIE",
            emailLabel: "Email",
            passLabel: "Contraseña",
            roleLabel: "Rol"
        }
    },
    labels: {
        cycle: "Ciclo Formativo",
        subject: "Asignatura / Módulo",
        schoolCycle: "Curso / Etapa",
        schoolSubject: "Asignatura / Área",
        uniCycle: "Grado / Carrera",
        uniSubject: "Asignatura / Crédito",
        noResults: "No se encontraron resultados",
        alreadyLinked: "Ya vinculado",
        enrollHint: "Selecciona los elementos que deseas matricular en tu centro"
    },
    searchEventsPlaceholder: "Buscar eventos por título o descripción...",
    searchPublicationsPlaceholder: "Buscar publicaciones por título o descripción...",
    groups: {
        title: "Gestión de Grupos",
        newGroup: "NUEVO GRUPO",
        studentsCount: "ALUMNOS",
        deleteTitle: "Eliminar Grupo",
        confirmDelete: "¿ELIMINAR?",
        yes: "SÍ",
        no: "NO",
        emptySection: "Sección vacía",
        actions: {
            newMeeting: "Nueva Charla",
            changeTutor: "Cambiar Tutor",
            addStudents: "Añadir Alumnos",
            assignSubject: "Asignar Materia"
        },
        sections: {
            studentsList: "Listado de Alumnos",
            subjectsAndTeaching: "Asignaturas y Docencia"
        }
    },
    people: {
        enrollPeople: "Matricular Personas",
        pendingVerification: "Pendientes de Verificación",
        pendingAlertText: "Estos usuarios han solicitado acceso y esperan verificación. Accede a su perfil para comprobar su identidad antes de validarlos.",
        pendingStatus: "Pendiente",
        viewProfileOf: "Ver perfil de {name}",
        teacherRole: "Profesor",
        studentRole: "Alumno",
        viewProfile: "Ver Perfil",
        verify: "Verificar",
        sections: {
            admins: "Dirección",
            teachers: "Cuerpo Docente",
            students: "Alumnado"
        }
    },
    stats: {
        groups: "Grupos",
        teachers: "Profesores",
        students: "Alumnos",
        publications: "Publicaciones"
    },
    messages: {
        serverError: "Error de servidor",
        studentVerified: "Estudiante verificado correctamente",
        teacherVerified: "Profesor verificado correctamente",
        verifyError: "Error al verificar",
        deleted: "Eliminado",
        removed: "Quitado",
        eventDeleted: "Evento eliminado",
        publicationDeleted: "Publicación eliminada",
        error: "Error",
        noTeacher: "Sin profesor"
    }
};
