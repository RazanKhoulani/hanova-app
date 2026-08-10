# Medical E-commerce & Clinical Management API Documentation

## Base URL: `/api/v1` (Current: `/api`)

### Unified Response Format
All responses return JSON. Lists are paginated.
Multilingual fields are returned as objects: `{"ar": "...", "en": "..."}`.

---

## 🔐 Authentication

### Register
`POST /auth/register`
- Payload: `name`, `phone`, `password`, `password_confirmation`
- Response: `201 Created` with simulated OTP in `otp_simulated`.

### Login
`POST /auth/login`
- Payload: `phone`, `password`
- Response: `200 OK` with `access_token` and `UserResource`.

### Password Reset (Mock)
`POST /auth/forgot-password` (Implemented in logic)
- Payload: `phone`
- Response: `200 OK` with simulated reset OTP.

---

## 🛍️ Products Module

### List Products
`GET /products`
- Returns paginated `ProductResource`.

### Product Details
`GET /products/{id}`

---

## 🏥 Clinical & Patient Management

### Patient Profiles
`GET /patients` - List my patients.
`POST /patients` - Create new profile with `image_before`, `image_after`, `medical_file`.

### Appointments
`GET /appointments` - List appointments.
`POST /appointments` - Book clinic or online session.

### Consultations
`GET /consultations` - List consultations.
`POST /consultations` - Request Chat, Bot, or Pre-booked session.

---

## 💬 Chat & Notifications

### Conversations
`GET /chat/conversations`
`GET /chat/conversations/{id}/messages`
`POST /chat/conversations/{id}/messages` - Send text, images, or files.

### Notifications
`GET /notifications`
`PUT /notifications/{id}/read`
