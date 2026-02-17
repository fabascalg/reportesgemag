# Reportes GemaG — Plugin local para Moodle

Autor: Fernando A. González  
Proyecto: Reportes GemaG  
Tipo: Plugin local Moodle  
Estado: En desarrollo activo  

---

## 📌 Descripción

Reportes GemaG es un plugin local para Moodle orientado al seguimiento automático del alumnado y a la generación de comunicaciones estructuradas tanto pedagógicas como administrativas.

El objetivo principal es disponer de un sistema que:

- Monitorice el progreso real de los alumnos en un curso.
- Envíe comunicaciones automáticas según su estado.
- Mantenga persistencia de todos los envíos.
- Evite duplicados.
- Genere informes para gestores.
- Prepare evidencias válidas para formación bonificada (España / FUNDAE).

El diseño prioriza:

- trazabilidad
- idempotencia
- bajo impacto en el sistema
- separación clara entre lógica y presentación
- escalabilidad

---

## 🎯 Objetivos funcionales

### Seguimiento del alumnado

- Lectura de curso configurado.
- Obtención de usuarios matriculados.
- Cálculo de:
  - estado (EN PROGRESO / FINALIZADO)
  - nota final
  - último acceso
- (pendiente) tiempo total dedicado.
- (pendiente) primer acceso.

---

### Comunicaciones automáticas

Tipos de correo (mailtype):

- `welcome` → bienvenida al alumno (una sola vez)
- `weekly` → seguimiento semanal
- `finished` → notificación de finalización (una sola vez)
- `manager_report` → informe de seguimiento para gestores
- `manager_bonificada` → informe administrativo ampliado (bonificada)

Todos los envíos quedan registrados en base de datos.

---

### Persistencia

Tabla propia:


Índice único:

(userid, courseid, mailtype)

Campos:

- userid
- courseid
- mailtype
- timecreated

Índice único:

(userid, courseid, mailtype)



Esto garantiza:

- no duplicación de correos
- ejecución idempotente
- trazabilidad completa

---

## 🧱 Arquitectura

El plugin está estructurado en capas:

### 1. Tasks (cron)

- weekly_report (seguimiento normal)
- futura bonificada_report (administrativo)

Las tareas solo orquestan.

---

### 2. Service (pendiente)

Toda la lógica real se moverá a una capa service para permitir:

- ejecución por cron
- ejecución manual desde dashboard

sin duplicar código.

---

### 3. Helpers

Actualmente:

- helper/mail.php

Funciones:

- has_mail_been_sent()
- log_mail()
- send_weekly_mail()

---

### 4. Dashboard (pendiente)

Se añadirá una interfaz propia:

- configuración centralizada
- disparo manual de reportes
- activación/desactivación de cron
- estado del sistema

Ruta prevista:

