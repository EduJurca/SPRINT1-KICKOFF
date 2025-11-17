# 💬 Widget de Chat Flotante

## Descripción

El chatbot de VoltiaCar ahora está disponible como un **widget flotante** que aparece en todas las páginas de la aplicación, proporcionando asistencia instantánea sin necesidad de cambiar de página.

## 🎨 Características

### Visual
- **Botón flotante** en la esquina inferior derecha (azul con icono de chat)
- **Ventana emergente** elegante de 384px × 500px
- **Diseño responsive** que se adapta a móviles (pantalla completa en móviles)
- **Animaciones suaves** al abrir/cerrar
- **Indicador de escritura** mientras el bot está respondiendo

### Funcional
- ✅ Disponible en **todas las páginas** (público y admin)
- ✅ Solo visible para **usuarios autenticados**
- ✅ Mismo backend que la vista completa (`/chat/send`)
- ✅ Historial de mensajes durante la sesión
- ✅ Validación de entrada (max 1000 caracteres)
- ✅ Manejo de errores con mensajes informativos

## 📂 Estructura

### Archivo Principal
```
views/commons/chatbot-widget.php
```

Este archivo contiene:
- HTML del widget (botón + ventana)
- CSS inline para el widget
- JavaScript para la interactividad

### Integración
El widget se incluye automáticamente en:
- `views/public/layouts/footer.php` - Para páginas públicas
- `views/admin/admin-footer.php` - Para panel de administración

## 🎯 Uso

### Para Usuarios
1. Inicia sesión en la aplicación
2. Verás un botón azul flotante en la esquina inferior derecha
3. Haz clic para abrir el chat
4. Escribe tu pregunta y presiona Enter
5. Recibe respuesta instantánea del asistente IA
6. Cierra haciendo clic en la X o en el botón flotante

### Para Desarrolladores

#### Deshabilitar en páginas específicas
Si necesitas ocultar el widget en alguna página:

```php
<?php
// Al inicio del archivo de vista
$hideChat = true;
?>
```

Luego modifica `chatbot-widget.php`:
```php
<?php
if (!isset($_SESSION['user_id']) || (isset($hideChat) && $hideChat)) {
    return;
}
?>
```

#### Personalizar posición
En `chatbot-widget.php`, ajusta las clases Tailwind:

```html
<!-- Botón -->
<button id="chat-toggle" class="fixed bottom-6 right-6 ...">

<!-- Widget -->
<div id="chat-widget" class="fixed bottom-24 right-6 ...">
```

#### Cambiar tamaño
Modifica el inline style del widget:

```html
<div id="chat-widget" ... style="height: 500px; max-height: calc(100vh - 150px);">
```

## 🔧 Tecnologías

- **HTML5** - Estructura semántica
- **Tailwind CSS** - Estilos utility-first
- **Vanilla JavaScript** - Sin dependencias
- **Fetch API** - Comunicación con backend
- **PHP** - Renderizado del servidor

## 📱 Responsive

### Desktop (> 640px)
- Widget: 384px × 500px
- Posición: Esquina inferior derecha
- Botón: 60px × 60px

### Mobile (≤ 640px)
- Widget: Pantalla completa (70% altura)
- Posición: Bottom sheet desde abajo
- Botón: Más pequeño y centrado

## 🎨 Personalización

### Colores
El widget usa la paleta de Tailwind CSS:
- Primario: `blue-600` (botón y header)
- Hover: `blue-700`
- Fondo: `gray-50`
- Bordes: `gray-200`

Para cambiar, modifica las clases en `chatbot-widget.php`.

### Iconos
Usa iconos SVG de Heroicons (ya incluidos). Para cambiar:

```html
<svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
    <!-- Cambiar path aquí -->
</svg>
```

## 🐛 Solución de Problemas

### El widget no aparece
1. Verifica que el usuario esté autenticado (`$_SESSION['user_id']`)
2. Comprueba que el archivo esté incluido en el footer
3. Revisa la consola del navegador por errores JS

### Los mensajes no se envían
1. Verifica que la ruta `/chat/send` esté registrada
2. Comprueba que `GROQ_API_KEY` esté configurada
3. Revisa los logs de Docker: `docker logs VC-web`

### Conflictos de CSS
El widget usa `z-50` para estar siempre visible. Si hay conflictos:
```css
#chat-widget { z-index: 9999 !important; }
```

## ⚡ Rendimiento

- **Carga inicial**: < 1KB (HTML inline)
- **JavaScript**: Inline, sin archivos externos
- **CSS**: Tailwind + custom inline
- **API calls**: Solo cuando se envía un mensaje
- **Caché**: Mensajes guardados en memoria durante la sesión

## 🔐 Seguridad

- ✅ Solo visible para usuarios autenticados
- ✅ Validación de entrada (max 1000 chars)
- ✅ Escape de HTML (XSS prevention)
- ✅ API key solo en backend
- ✅ Timeout de 30 segundos en requests

## 📈 Mejoras Futuras

- [ ] Persistencia de mensajes en localStorage
- [ ] Notificaciones cuando hay respuesta
- [ ] Soporte para markdown en respuestas
- [ ] Shortcuts de teclado (Esc para cerrar)
- [ ] Minimizar/maximizar con animación
- [ ] Temas claro/oscuro
- [ ] Exportar conversación
- [ ] Sugerencias de preguntas rápidas
