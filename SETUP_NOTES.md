# EcoGuard final setup

## 1. Project folder
Place the folder as:

`htdocs/ecoGuard/`

The previous `/ecoGaurd/` spelling has been removed from the project links.

## 2. Database
Import the main EcoGuard database first, then run:

`sql/migration_2026.sql`

The migration is written to be safe to run when the Latitude/Longitude columns or the new tables already exist.

## 3. Roles
The login page contains **Register as Citizen**.

The registration page provides these account roles:

- Citizen — Role_Id 1
- Administrator — Role_Id 2 (created by an existing administrator)
- Divisional Secretary — Role_Id 3
- Local Authority — Role_Id 4
- Grama Niladhari — Role_Id 5

After login, users are routed to their own dashboard.

## 4. Truck schedule workflow

**Local Authority → publishes/edits/deletes schedule → database → Citizen + Admin**

- LA: `authorities/la_schedule.php`
- Citizen: `citizen/truck_schedule.php` and Citizen Dashboard
- Admin: `admin/truck_schedules.php`

Only the Local Authority that created a schedule can edit/delete that schedule.

## 5. Leaflet map

Citizen complaints can store Latitude/Longitude. The Citizen Dashboard displays those saved complaint locations using Leaflet.

The complaint form also supports selecting a location on a Leaflet map.

## 6. Responsive UI

The shared design system in `css/theme.css` now handles:

- Desktop/tablet/mobile layouts
- Responsive navigation
- Responsive dashboard/sidebar layout
- Responsive workflow relationship cards
- Responsive Leaflet map + truck schedule cards
- Horizontal scrolling for wide data tables
- Mobile-friendly forms and cards

## 7. Important

Leaflet and Font Awesome are loaded from their public CDNs, so those assets require an internet connection unless you later download them locally.
