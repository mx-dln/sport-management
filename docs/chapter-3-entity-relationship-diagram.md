# Chapter 3 Entity Relationship Diagram

## Source Schema

This ERD is based on `database/sports_management.sql`.

## Main Entities

```mermaid
erDiagram
    users ||--o{ teams : coaches
    users ||--o{ athletes : owns_account
    users ||--o{ form_templates : uploads
    users ||--o{ training_schedules : creates
    users ||--o{ training_schedules : coaches
    users ||--o{ attendance : marks
    users ||--o{ announcements : creates
    users ||--o{ sms_logs : sends
    users ||--o{ medical_records : records
    users ||--o{ competitions : creates
    users ||--o{ competition_participants : coaches
    users ||--o{ competition_results : updates
    users ||--o{ athlete_histories : creates

    sports ||--o{ teams : has
    sports ||--o{ athletes : chosen_by
    sports ||--o{ training_schedules : scheduled_for
    sports ||--o{ announcements : targets
    sports ||--o{ competitions : includes
    sports ||--o{ athlete_histories : categorizes

    teams ||--o{ athletes : assigned_team
    teams ||--o{ team_members : has
    athletes ||--o{ team_members : joins

    requirement_types ||--o{ athlete_documents : required_as
    athletes ||--o{ athlete_documents : submits

    teams ||--o{ training_schedules : has
    training_schedules ||--o{ attendance : records
    athletes ||--o{ attendance : has

    teams ||--o{ announcements : targets

    athletes ||--o{ medical_records : has
    athletes ||--o{ competition_participants : joins
    athletes ||--o{ competition_results : earns
    athletes ||--o{ athlete_histories : has

    competitions ||--o{ competition_participants : has
    competitions ||--o{ competition_results : has
```

## Tables and Keys

| Table | Primary key | Foreign keys |
| --- | --- | --- |
| `users` | `id` | None |
| `sports` | `id` | None |
| `teams` | `id` | `sport_id -> sports.id`, `coach_id -> users.id` |
| `athletes` | `id` | `user_id -> users.id`, `sport_id -> sports.id`, `team_id -> teams.id` |
| `team_members` | `id` | `team_id -> teams.id`, `athlete_id -> athletes.id` |
| `requirement_types` | `id` | None |
| `athlete_documents` | `id` | `athlete_id -> athletes.id`, `requirement_type_id -> requirement_types.id` |
| `form_templates` | `id` | `uploaded_by -> users.id` |
| `training_schedules` | `id` | `sport_id -> sports.id`, `team_id -> teams.id`, `coach_id -> users.id`, `created_by -> users.id` |
| `attendance` | `id` | `schedule_id -> training_schedules.id`, `athlete_id -> athletes.id`, `marked_by -> users.id` |
| `announcements` | `id` | `sport_id -> sports.id`, `team_id -> teams.id`, `created_by -> users.id` |
| `sms_logs` | `id` | `sent_by -> users.id` |
| `medical_records` | `id` | `athlete_id -> athletes.id`, `recorded_by -> users.id` |
| `competitions` | `id` | `sport_id -> sports.id`, `created_by -> users.id` |
| `competition_participants` | `id` | `competition_id -> competitions.id`, `athlete_id -> athletes.id`, `coach_id -> users.id` |
| `competition_results` | `id` | `competition_id -> competitions.id`, `athlete_id -> athletes.id`, `updated_by -> users.id` |
| `athlete_histories` | `id` | `athlete_id -> athletes.id`, `sport_id -> sports.id`, `created_by -> users.id` |
