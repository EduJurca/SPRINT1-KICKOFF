# 💬 Chatbot con IA - Groq (100% GRATUITO)

## 📋 Descripción

Chatbot integrado en VoltiaCar que utiliza **Groq API** (completamente gratuita) con el modelo **Llama 3.1 8B Instant** para asistir a los usuarios con preguntas sobre:

- Vehículos disponibles
- Proceso de reservas
- Información sobre pagos
- Uso general de la aplicación

## ✨ Ventajas de Groq

- ✅ **100% GRATUITO** (sin necesidad de tarjeta de crédito)
- ✅ **ULTRA RÁPIDO** (el servicio de IA más rápido del mundo)
- ✅ Sin límites de cuota para uso personal
- ✅ Modelo Llama 3.1 de Meta (alta calidad)
- ✅ Compatible con API de OpenAI
- ✅ Sin costos ocultos ni expiración de créditos

## 🚀 Configuración Paso a Paso

### 1. Obtener API Key (Gratis - 2 minutos)

1. Ve a [Groq Console](https://console.groq.com)
2. Crea una cuenta con tu email (gratis, sin tarjeta)
3. Accede a [API Keys](https://console.groq.com/keys)
4. Clic en **"Create API Key"**
5. Dale un nombre: `VoltiaCar Chatbot`
6. Copia la key (empieza con `gsk_...`)

### 2. Configurar en el Proyecto

Edita el archivo `.env` y añade tu API key:

```bash
GROQ_API_KEY=gsk_tu_api_key_aqui
```

### 3. Reiniciar Docker

```bash
docker compose restart
```

O reconstruir:

```bash
docker compose down
docker compose up -d --build
```

## 🧪 Usar el Chatbot

### Acceso Rápido - Widget Flotante ⭐

El chatbot aparece automáticamente en **todas las páginas** de la aplicación como un botón flotante en la esquina inferior derecha:

1. **Haz clic en el botón azul** con el icono de chat 💬
2. Se abrirá una ventana de chat elegante
3. Escribe tu pregunta y presiona Enter o clic en enviar
4. ¡Obtendrás una respuesta instantánea!
5. Cierra el chat haciendo clic en la X o vuelve a hacer clic en el botón

**Características del widget:**
- 🎯 Siempre disponible en cualquier página
- 📱 Responsive (se adapta a móviles)
- ⚡ Respuestas en tiempo real
- 💾 Historial durante la sesión
- 🎨 Diseño moderno y elegante

### Acceso Directo (Opcional)

También puedes acceder a la vista completa del chat en: http://localhost:8080/chat

## 📊 Límites y Cuotas (Gratis)

- **Llama 3.1 8B**: 30 req/min, 14,400 req/día
- **Mixtral 8x7B**: 30 req/min, 14,400 req/día  
- **Gemma 7B**: 30 req/min, 14,400 req/día

Más que suficiente para uso educativo/personal. ¡No necesitas pagar!

## ⚡ Velocidad

Groq es **extremadamente rápido**:
- Respuestas en **menos de 1 segundo**
- Hasta **750 tokens/segundo** de velocidad
- Sin tiempos de carga del modelo

## 📁 Archivos del Proyecto

### Backend
- `controllers/public/ChatController.php` - Gestión de peticiones y llamadas a Groq

### Frontend
- `views/public/chat/index.php` - Vista completa del chat (opcional)
- `views/commons/chatbot-widget.php` - **Widget flotante (principal)** ⭐
- `assets/js/chat.js` - Lógica del cliente (vista completa)
- `assets/css/chat.css` - Estilos personalizados

### Configuración
- `routes/web.php` - Rutas: `/chat` y `/chat/send`
- `docker-compose.yml` - Variable de entorno `GROQ_API_KEY`
- `.env` - Configuración de la API key
- `lang/ca.php` y `lang/en.php` - Traducciones
- `views/public/layouts/footer.php` - Incluye el widget
- `views/admin/admin-footer.php` - Incluye el widget en admin

## 🔧 Modelo Utilizado

**Llama 3.1 8B Instant**
- Modelo open-source de Meta
- 8 mil millones de parámetros
- Optimizado para velocidad
- Multilingüe (catalán, español, inglés, 100+ idiomas)
- Respuestas precisas y coherentes

## 🐛 Troubleshooting

### Error: API key not configured
- Verifica que `GROQ_API_KEY` esté en `.env`
- Reinicia Docker: `docker compose restart`

### Error: 401 Unauthorized
- Verifica que la API key sea correcta
- Asegúrate de que empiece con `gsk_`

### Error: Rate limit exceeded
- Has superado 30 req/min
- Espera 1 minuto y vuelve a intentar

## 🆚 Comparación con Otras APIs

| Característica | Groq | Hugging Face | OpenAI |
|---------------|------|--------------|--------|
| Precio | **GRATIS** | Gratis (limitado) | $0.0005/msg |
| Tarjeta requerida | ❌ No | ❌ No | ✅ Sí |
| Velocidad | ⚡ Ultra rápida | 🐌 Lenta | 🚀 Rápida |
| Cuota gratuita | 14,400/día | 1,000/hora | $5 (expiran) |
| Calidad | ⭐⭐⭐⭐⭐ | ⭐⭐⭐ | ⭐⭐⭐⭐⭐ |
| API Status | ✅ Activa | ⚠️ Deprecated | ✅ Activa |

## 🎯 Modelos Disponibles en Groq

Puedes cambiar el modelo en `ChatController.php`:

```php
'model' => 'llama-3.1-8b-instant',  // Rápido y eficiente (recomendado)
'model' => 'llama-3.1-70b-versatile', // Más potente
'model' => 'mixtral-8x7b-32768',    // Contexto largo
'model' => 'gemma-7b-it',           // Google Gemma
```

Todos son **100% gratuitos**.

## 📚 Referencias

- [Groq Documentation](https://console.groq.com/docs)
- [API Playground](https://console.groq.com/playground)
- [Rate Limits](https://console.groq.com/docs/rate-limits)
- [Llama 3.1 Info](https://ai.meta.com/llama/)

## 💡 Ventajas Clave

### ¿Por qué Groq?

1. **Velocidad increíble**: Respuestas en menos de 1 segundo
2. **Totalmente gratis**: Sin tarjeta, sin cuotas, sin límites razonables
3. **Fácil de usar**: API compatible con OpenAI
4. **Alta calidad**: Modelos de última generación
5. **Fiable**: Infraestructura empresarial

## 🔐 Seguridad

- ✅ API key solo en backend (nunca expuesta al cliente)
- ✅ Validación de entrada (max 1000 caracteres)
- ✅ Timeout de 30 segundos
- ✅ Variables de entorno en Docker

## 📈 Mejoras Futuras

- [ ] Historial de conversaciones persistente
- [ ] Respuestas en streaming (Server-Sent Events)
- [ ] Moderación de contenido
- [ ] Analytics de uso
- [ ] Soporte para imágenes (con Llama 3.2 Vision)
- [ ] Memoria de contexto entre mensajes
