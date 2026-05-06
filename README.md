# 🎮 Rakion v258 - Comunidad GameDev

![License](https://img.shields.io/badge/license-MIT-green)
![Status](https://img.shields.io/badge/status-active-brightgreen)
![Community](https://img.shields.io/badge/GameDev-Server-blueviolet)

¡Bienvenidos al repositorio oficial de nuestra comunidad! Este es un proyecto **Open Source** integral que abarca desde la lógica del núcleo hasta la interfaz de usuario. Nuestro objetivo es colaborar, aprender y construir juntos el mejor ecosistema para Rakion.

---

## 🌐 Conexión al Entorno (Radmin VPN)

Para poder testear el servidor y colaborar en tiempo real👌, asegúrate de estar conectado a nuestra red de Radmin VPN:

* **Red:** `RakionDev258 user`
* **Contraseña:** `123456`

---

## 🚀 Estructura del Proyecto

El repositorio está organizado de forma modular para separar las herramientas de desarrollo de los archivos del juego:

| Carpeta | Componente | Tecnología | Descripción |
| :--- | :--- | :--- | :--- |
| `📂 /client` | **Cliente** | Serious Engine | Archivos base del juego, arte y recursos. |
| `📂 /server` | **Server Files** | C++ | Lógica de red, base de datos y validaciones. |
| `📂 /web` | **Plataforma Web** | PHP 7 / HTML | Registro, Ranking y Panel de administración. |
| `📂 /patch` | **Client Patch** | Binarios | Parches para conexión IP y Bypass de GameGuard. |
| `📂 /tools` | **Herramientas** | Varios | Editores de .mrs, .xfs y utilitarios de desarrollo. |

---

## 🔧 Instalación de Parches y Acceso

Para conectar con éxito al servidor de desarrollo, sigue estos pasos ubicados en la carpeta `📂 /patch`:

1.  **Bypass GameGuard:** Copia los archivos de la carpeta patch en la raíz de tu cliente para anular la protección original.
2.  **IP Config:** Edita el archivo de configuración con la IP proporcionada en Radmin VPN para direccionar el tráfico al servidor activo.

---

## 🛠️ Requisitos Previos

* **Servidor Web:** PHP 7.0+ (Soporte mysqli habilitado).
* **Base de Datos:** MySQL / MariaDB (Nombre de la DB: `rakion`).
* **Red:** Cliente Radmin VPN instalado.

---

## 🤝 Cómo Contribuir

1. Haz un **Fork** del proyecto.
2. Crea una rama para tu mejora: `git checkout -b feature/NuevaMejora`.
3. Realiza tus cambios y haz un **Commit**: `git commit -m 'Añadida nueva funcionalidad'`.
4. Sube tus cambios: `git push origin feature/NuevaMejora`.
5. Abre un **Pull Request**.

---

## 📜 Licencia

Este proyecto se distribuye bajo la licencia **MIT**. Eres libre de usarlo, modificarlo y distribuirlo siempre que mantengas los créditos originales de la comunidad.

---
Desarrollado con ❤️ por la comunidad de **GameDev Server**
