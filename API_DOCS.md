# HRIS Mobile App API Documentation

Welcome to the API documentation for the HRIS Mobile Attendance Application. This guide provides all the necessary information to interact with the backend services.

---

## 1. General Information

### Base URL
All API endpoints are prefixed with the following base URL:
```

[https://your-domain.com/api/v1](https://your-domain.com/api/v1)

```

### Authentication
All endpoints described in this document (except for `/login`) are protected and require authentication. The API uses **Bearer Token** authentication (powered by Laravel Sanctum).

To authenticate, the mobile application must:
1. Send user credentials (email, password) to the `/login` endpoint.
2. Receive a plain-text API token upon successful login.
3. Include this token in the `Authorization` header for all subsequent requests.

**Example Header:**
```

Authorization: Bearer \<YOUR\_API\_TOKEN\>
Accept: application/json

````

### Standard Response Format

**Successful Response (`2xx` Status Code)**
```json
{
    "status": "success",
    "code": 200,
    "message": "Descriptive success message.",
    "data": { ... }
}
````

**Error Response (`4xx` or `5xx` Status Code)**

```json
{
    "status": "error",
    "message": "Descriptive error message.",
    "errors": { 
        "field_name": [
            "The field_name is required."
        ]
    }
}
```

-----

## 2\. Authentication

*Note: The login/logout controllers are not provided in the snippet, but this is the standard expected implementation.*

### Login

Authenticates a user and returns an API token.

  * **Endpoint:** `POST /login`
  * **Method:** `POST`
  * **Authorization:** None

**Request Body:**

```json
{
    "email": "employee@example.com",
    "password": "password123",
    "device_name": "John's iPhone 15"
}
```

**Success Response (`200 OK`)**
*The token is returned as a plain-text string in the response body.*

```
eyJhbGciOiJIUzI1NiIsInR5cCI6IkpXVCJ9...
```

### Logout

Invalidates the current user's API token.

  * **Endpoint:** `POST /logout`
  * **Method:** `POST`
  * **Authorization:** `Bearer <token>`

**Success Response (`204 No Content`)**
*An empty response indicating the token has been successfully invalidated.*

-----

## 3\. Attendance Management

Endpoints for handling daily attendance records (clock-in, clock-out).

### Clock In

Creates a new attendance record for the day. This should be used for the user's first action of the day (clocking in).

  * **Endpoint:** `POST /attendance`
  * **Method:** `POST`
  * **Authorization:** `Bearer <token>`

**Request Body:**
| Field | Type | Required | Description |
| :--- | :--- | :--- | :--- |
| `employee_id` | Integer | Yes | The ID of the authenticated user. |
| `date` | String | Yes | The date of the attendance record (`YYYY-MM-DD`). |
| `clock_in_time` | String | Yes | The time of clock-in (`HH:MM:SS`). |
| `location_type_id` | Integer | Yes | ID representing the work arrangement. |
| `gps_coordinates` | String | Yes | Latitude and longitude (`"lat,lng"`). |
| `status_id` | Integer | Yes | ID representing the attendance status. |
| `notes` | String | No | Optional notes from the employee. |

**Example Request:**

```json
{
    "employee_id": 12,
    "date": "2025-08-27",
    "clock_in_time": "08:59:15",
    "location_type_id": 1,
    "gps_coordinates": "-6.2297, 106.8859",
    "status_id": 1,
    "notes": "Starting my day."
}
```

**Success Response (`201 Created`)**

```json
{
    "status": "success",
    "code": 201,
    "message": "Attendance record created successfully.",
    "data": {
        "id": "152"
    }
}
```

### Clock Out

Updates an existing attendance record with a clock-out time.

  * **Endpoint:** `PUT /attendance/{id}`
  * **Method:** `PUT`
  * **Authorization:** `Bearer <token>`

**URL Parameters:**
| Parameter | Type | Description |
| :--- | :--- | :--- |
| `id` | Integer | The ID of the attendance record to update. |

**Request Body:**
| Field | Type | Required | Description |
| :--- | :--- | :--- | :--- |
| `clock_out_time`| String | Yes | The time of clock-out (`HH:MM:SS`). |

**Example Request:**

```json
{
    "clock_out_time": "17:30:05"
}
```

**Success Response (`200 OK`)**

```json
{
    "status": "success",
    "code": 200,
    "message": "Attendance record ID 152 updated successfully.",
    "data": {
        "id": "152",
        "date": "2025-08-27",
        "clockInTime": "08:59:15",
        "clockOutTime": "17:30:05",
        "status": "Present"
    }
}
```

### Get Attendance History

Retrieves a paginated list of the user's attendance records.

  * **Endpoint:** `GET /attendance`
  * **Method:** `GET`
  * **Authorization:** `Bearer <token>`

**Query Parameters:**
| Parameter | Type | Description |
| :--- | :--- | :--- |
| `page` | Integer | The page number for pagination (e.g., `?page=2`). |
| `date` | String | Filter records for a specific date (`YYYY-MM-DD`). |
| `start_date` | String | Filter records from this start date (`YYYY-MM-DD`). |
| `end_date` | String | Filter records up to this end date (`YYYY-MM-DD`). |

**Example URL:** `GET /api/v1/attendance?start_date=2025-08-01&end_date=2025-08-31`

**Success Response (`200 OK`)**

```json
{
    "status": "success",
    "code": 200,
    "message": "Attendance records retrieved successfully.",
    "data": {
        "current_page": 1,
        "data": [
            {
                "id": "152",
                "date": "2025-08-27"
            },
            {
                "id": "151",
                "date": "2025-08-26"
            }
        ],
        "total": 2
    }
}
```

-----

## 4\. Attendance Correction Request

### Submit Correction Request

  * **Endpoint:** `POST /attendance-requests`
  * **Method:** `POST`
  * **Authorization:** `Bearer <token>`

**Request Body:**
| Field | Type | Required | Description |
| :--- | :--- | :--- | :--- |
| `attendance_id` | Integer | Yes | The ID of the record to be corrected. |
| `employee_reason` | String | Yes | The reason for the correction. |
| `requested_clock_in_time` | String | Conditional| New clock-in time (`HH:MM`). |
| `requested_clock_out_time`| String | Conditional| New clock-out time (`HH:MM`). |
| `requested_location_type_id` | Integer | Conditional| New work arrangement ID. |

**Success Response (`201 Created`)**

```json
{
    "message": "Correction request submitted successfully."
}
```

-----

## 5\. User Profile Management

### Get User Profile

  * **Endpoint:** `GET /profile`
  * **Method:** `GET`
  * **Authorization:** `Bearer <token>`

**Success Response (`200 OK`)**

```json
{
    "status": "success",
    "code": 200,
    "message": "Profile retrieved successfully",
    "data": {
        "user": {
            "id": 12,
            "name": "John Doe",
            "email": "employee@example.com"
        }
    }
}
```

-----

## 6\. Notifications

### Get Notifications

Retrieves the latest 50 notifications for the authenticated user.

  * **Endpoint:** `GET /notifications`
  * **Method:** `GET`
  * **Authorization:** `Bearer <token>`

**Success Response (`200 OK`)**

```json
{
    "data": [
        {
            "id": "c9a419e0-0b6b-4f4d-8b0a-7c98e1f2e3d4",
            "data": {
                "message": "Your attendance correction for 2025-08-26 has been approved."
            }
        }
    ]
}
```

### Mark Notification as Read

  * **Endpoint:** `POST /notifications/{notificationId}/read`
  * **Method:** `POST`
  * **Authorization:** `Bearer <token>`

**URL Parameters:**
| Parameter | Type | Description |
| :--- | :--- | :--- |
| `notificationId` | UUID | The ID of the notification to mark as read. |

**Success Response (`204 No Content`)**
*An empty response indicating success.*

```
```
