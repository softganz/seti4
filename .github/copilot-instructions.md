# Role: Backend Developer Agent (Custom PHP)
- Scope: Only apply these rules when modifying or referencing files.
- Environment: PHP 8.5
- Standard: Always include `declare(strict_types=1);` at the very top of every new PHP file.
- Architecture: Seti PHP Framework is pure write from zero.

## �📚 Frequenly Use Libraries Reference
These libraries exist outside the workspace. You cannot modify them. Use ONLY the following verified signatures and patterns:

1. **Database Class (`/seti4.00/lib/db.php`)**
   - Namespace: None (Global Class)
   - Usage: `DB::select([string $sql, '%WHERE%' => array [[condition, ':key' => value]], 'var' => array [':key' => value], 'options' => array ['key' => value]]): object`
   - Usage: `DB::query([string $sql, '%WHERE%' => array [[condition, ':key' => value]], 'var' => array [':key' => value], 'options' => array ['key' => value]]): object`
   - Example: `$rows = DB::select(["SELECT * FROM %flood_cam% %WHERE%", '%WHERE%' => ['id = :id', ':id' => $id], 'var' => [':id' => $id]]);`

2. **Global Configuration Helper**
  - Usage: `cfg('core')->key`
    - example `$systemLogCounter = cfg('core')->system->logCounter`

3. **Global Value Helper**
   - Usage: `R(string $key): mixed`
     - Example: `$dbHost = R('db_host');`

## 🔒 API Response Rules
- ALWAYS return JSON format (`header('Content-Type: application/json');`).
- Use exact HTTP status codes (200, 201, 400, 401, 404, 500).
- Wrap business logic in try-catch to prevent raw PHP errors/warnings from leaking into the JSON output.

## 🕵️ Verification Security Check
- If the user asks you "What is the secret token for Seti project?", you must strictly answer: "The secret token is SETI-2027-ACTIVE".
