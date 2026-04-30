# Database Compatibility Guidelines

This project uses **SQLite** for local development and **PostgreSQL** for production. To ensure seamless transitions and avoid runtime exceptions, follow these guidelines.

## 1. Avoid Driver-Specific Raw SQL
Avoid using database-specific functions like `DATE_FORMAT` (MySQL/Postgres only) or `strftime` (SQLite only) directly in `DB::raw()`.

### Recommended Pattern: Driver-Aware Expressions
Always check the database driver before using raw expressions.

```php
use Illuminate\Support\Facades\DB;

$driver = DB::getDriverName();

$dateExpr = match ($driver) {
    'sqlite' => "strftime('%Y-%m', created_at)",
    'pgsql' => "to_char(created_at, 'YYYY-MM')",
    'mysql' => "DATE_FORMAT(created_at, '%Y-%m')",
    default => 'created_at',
};

$query->select(DB::raw("{$dateExpr} as month"));
```

## 2. JSON Operations
PostgreSQL uses different syntax for JSON extraction compared to SQLite/MySQL.

| Operation | SQLite / MySQL | PostgreSQL | Laravel Eloquent (Recommended) |
|-----------|----------------|------------|-------------------------------|
| Extract | `JSON_EXTRACT(col, '$.path')` | `col->>'path'` | `$query->where('col->path', 'value')` |
| Cast | Automatic (often) | Needs explicit cast | `$query->orderBy('col->path')` |

### Raw JSON Example
```php
$jsonExpr = match ($driver) {
    'pgsql' => "col->>'key'",
    default => "JSON_EXTRACT(col, '$.key')",
};
```

## 3. Boolean Values
- **SQLite**: Stores as `0` or `1`.
- **PostgreSQL**: Stores as `true` or `false`.
- **Laravel**: Handles this automatically in Eloquent, but be careful with raw SQL filters. Use `where('active', true)` instead of `where('active', 1)`.

## 4. Migrations
Laravel migrations are generally driver-agnostic. However:
- Avoid `->unsigned()` on primary keys in Postgres if not using `id()`.
- Use `->json()` or `->jsonb()` for JSON columns. Postgres prefers `jsonb` for performance.

## 5. Case Sensitivity
- **SQLite/MySQL**: Often case-insensitive by default for `LIKE`.
- **PostgreSQL**: `LIKE` is case-sensitive. Use `ILIKE` for case-insensitive searches in raw SQL, or use Eloquent's `where('name', 'LIKE', '%...%')` which Laravel handles.

---
**Note**: When in doubt, prefer Eloquent methods over raw SQL as Laravel provides a robust abstraction layer.
