# 🦈 TELAMONET

**TelamoNet** es una aplicación web que simula una **red social educativa**, diseñada para conectar **centros educativos** y **estudiantes**, facilitando la difusión de eventos académicos y el apoyo entre alumnos mediante un sistema de preguntas y respuestas.

El proyecto nace como una plataforma orientada al ámbito educativo, con un enfoque inicial en **Lanzarote**, y tiene como objetivo servir tanto como herramienta funcional como entorno de aprendizaje para el desarrollo de aplicaciones web modernas.

<p align="center">
  <img src="src/assets/logo/logoTelamon.png" alt="TelamoNet_logo" width="135" height="135"/>
</p>

---

## 🎯 Objetivo del proyecto

El objetivo principal de **TelamoNet** es crear un entorno digital donde:

- Los **centros educativos** puedan promocionar eventos, actividades y contenidos relevantes.
- Los **alumnos** puedan informarse, participar y resolver dudas académicas a través de la comunidad.
- Se fomente la colaboración entre estudiantes mediante un **sistema de preguntas, respuestas y reputación**.

Además, el proyecto sirve como práctica realista de desarrollo **fullstack**, simulando una aplicación con lógica de negocio, roles de usuario y escalabilidad.

---

## 👥 Tipos de usuarios

La plataforma cuenta con dos roles principales:

### 🏫 Centro Educativo
- Creación y gestión de eventos educativos.
- Publicación de información relevante para los alumnos.
- Visualización del impacto y participación en sus eventos.

### 🎓 Alumno
- Acceso a eventos publicados por los centros.
- Creación de preguntas académicas clasificadas por materias.
- Respuesta a preguntas de otros alumnos.
- Obtención de puntos de **reputación** por aportar respuestas útiles.

---

## 📅 Sistema de eventos

Los centros educativos pueden crear eventos con información estructurada, incluyendo:

- Título del evento
- Descripción del evento
- Fecha de inicio y finalización  
- Ubicación mediante enlace a Google Maps  

Esto permite centralizar y organizar actividades educativas como charlas, talleres, jornadas o cursos.

---

## ❓ Apartado de preguntas y reputación

TelamoNet incorpora un sistema de **preguntas y respuestas** orientado al apoyo académico:

- Los alumnos pueden publicar preguntas asociadas a una o varias materias mediante etiquetas.
- Las preguntas son públicas y visibles para toda la comunidad estudiantil.
- Los usuarios pueden responder y ayudar a otros alumnos.
- El autor de la pregunta puede otorgar **reputación** a las mejores respuestas.
- Los usuarios con mayor reputación ganan visibilidad, destacando sus respuestas en futuras preguntas.
- Verificación de preguntas y respuestas mediante APIs de Inteligencia Artificial

Este sistema incentiva la participación activa y la calidad del contenido.

---

## 💬 Chat grupal/privado

Telamonet dispone de funciones para realizar una charla grupal o privada:

- Mensajes en tiempo real mediante WebSocket
- Realización de kahoots mediante APIs de Inteligencia Artificial para la generación de dicho Kahoot
- Función de videollamada

## 🚀 Tecnologías utilizadas

### 🖥️ Frontend
- **Vue 3** — Framework principal de la interfaz.
- **Vite** — Entorno de desarrollo rápido.
- **Vue Router** — Sistema de rutas para las vistas.

### 🗄️ Backend / Base de Datos
- **Laravel**
  - Base de datos: MySQL
  - Autenticación de usuarios
  - Gestión de roles
  - Storage para imágenes

- **Docker** — Entorno de desarrollo y despliegue.

---

## 🧩 Propósito educativo

El proyecto **TelamoNet** no pretende competir con plataformas reales, sino servir como un proyecto educativo para profundizar en:

- Diseño y normalización de bases de datos  
- Arquitectura backend y patrón MVC  
- Autenticación y control de roles  
- Lógica de negocio escalable  
- Buenas prácticas en Vue 3 y Laravel  
- Desarrollo de aplicaciones con enfoque real de mercado  

---

## 📌 Estado del proyecto
Finalizado 🏗️  

---

## ✨ Autores
**Daniel Bucaloiu Morales, Jason Camila Sotto y Antonio Morera Marrero**  
Proyecto educativo orientado al aprendizaje y mejora continua como desarrolladores fullstack.
